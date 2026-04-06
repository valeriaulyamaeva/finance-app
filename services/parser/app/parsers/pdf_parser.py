import hashlib
import re
from io import BytesIO

import pdfplumber

from app.categorizer import categorize
from app.parsers.csv_parser import COLUMN_ALIASES, _find_col_by_aliases
from app.schemas import ParsedTransaction

# Aliases for the fallback account-currency amount column
AMOUNT_ACCOUNT_ALIASES = [
    "Сумма в валюте счета",
    "Сумма и комиссия в валюте счета",
]


def _normalize(text: str) -> str:
    """Replace newlines with spaces and collapse whitespace."""
    return re.sub(r"\s+", " ", text).strip()


def _is_header_row(cells: list[str]) -> bool:
    """Check if a row of cells is the table header.
    Must have multiple non-empty cells AND contain date-related + amount-related keywords."""
    non_empty = [c for c in cells if c.strip()]
    if len(non_empty) < 3:
        return False

    row_text = " ".join(cells).lower()

    has_date = "дата" in row_text
    has_amount = "сумма" in row_text
    has_specific = (
        "дата операции" in row_text
        or "тип операции" in row_text
        or "наименование операции" in row_text
        or "mcc" in row_text.upper()
        or "мсс" in row_text
    )

    return has_date and has_amount and has_specific


def parse_pdf(content: bytes) -> tuple[list[ParsedTransaction], list[str], dict]:
    errors: list[str] = []
    transactions: list[ParsedTransaction] = []
    metadata: dict = {}

    try:
        pdf = pdfplumber.open(BytesIO(content))
    except Exception as e:
        errors.append(f"Не удалось открыть PDF: {str(e)}")
        return transactions, errors, metadata

    all_rows: list[list[str]] = []
    header_found = False
    header_map: dict[str, int] = {}

    for page_num, page in enumerate(pdf.pages):
        tables = page.extract_tables()
        if not tables:
            tables = page.extract_tables({
                "vertical_strategy": "text",
                "horizontal_strategy": "text",
            })

        for table in tables:
            for row in table:
                if not row:
                    continue

                # Normalize all cells: replace \n with space
                cells = [_normalize(str(c)) if c else "" for c in row]

                # Detect header row (skip if already found — handles repeated headers on new pages)
                if not header_found:
                    if _is_header_row(cells):
                        for idx, cell in enumerate(cells):
                            if cell:
                                header_map[cell] = idx
                        header_found = True
                        continue
                else:
                    # Skip repeated header rows on subsequent pages
                    if _is_header_row(cells):
                        continue

                if header_found:
                    all_rows.append(cells)

    pdf.close()

    if not header_found:
        errors.append("Не найден заголовок таблицы в PDF")
        return transactions, errors, metadata

    if "currency" not in metadata:
        metadata["currency"] = "BYN"

    # Map columns using shared aliases
    date_col = _find_col_by_aliases(header_map, COLUMN_ALIASES["date"])
    type_col = _find_col_by_aliases(header_map, COLUMN_ALIASES["type"])
    mcc_col = _find_col_by_aliases(header_map, COLUMN_ALIASES["mcc"])
    place_col = _find_col_by_aliases(header_map, COLUMN_ALIASES["place"])
    amount_col = _find_col_by_aliases(header_map, COLUMN_ALIASES["amount"])
    amount_account_col = _find_col_by_aliases(header_map, AMOUNT_ACCOUNT_ALIASES)
    status_col = _find_col_by_aliases(header_map, COLUMN_ALIASES["status"])
    direction_col = _find_col_by_aliases(header_map, COLUMN_ALIASES["direction"])

    if date_col is None or (amount_col is None and amount_account_col is None):
        errors.append(f"Не найдены обязательные колонки. Доступные: {list(header_map.keys())}")
        return transactions, errors, metadata

    for row_num, cells in enumerate(all_rows, start=1):
        try:
            raw_date = _get_col(cells, date_col)
            raw_amount = _get_col(cells, amount_col)
            raw_amount_account = _get_col(cells, amount_account_col)
            raw_type = _get_col(cells, type_col)
            raw_mcc = _get_col(cells, mcc_col)
            raw_place = _get_col(cells, place_col)
            raw_status = _get_col(cells, status_col)
            raw_direction = _get_col(cells, direction_col)

            if not raw_date:
                continue

            # Skip summary rows
            if any(kw in raw_date for kw in ["Приход", "Расход", "Доля", "Итого", "Номер карты"]):
                continue

            # Skip non-completed (if status column exists)
            status_lower = raw_status.strip().lower() if raw_status else ""
            if status_lower and status_lower not in ("исполнено", "проведено", ""):
                continue

            # Parse date
            date = _extract_date(raw_date)
            if not date:
                continue

            # Parse amount — try operation currency first, fall back to account currency
            amount_val, currency = None, None
            for amt_raw in [raw_amount, raw_amount_account]:
                if not amt_raw or not amt_raw.strip():
                    continue
                clean = amt_raw.replace(" ", "").replace("\xa0", "")
                val, cur = _parse_amount(clean)
                if val is not None and val != 0.0:
                    amount_val, currency = val, cur
                    break

            # If both are 0 or None, try the account amount even if 0
            if amount_val is None:
                for amt_raw in [raw_amount, raw_amount_account]:
                    if not amt_raw or not amt_raw.strip():
                        continue
                    clean = amt_raw.replace(" ", "").replace("\xa0", "")
                    val, cur = _parse_amount(clean)
                    if val is not None:
                        amount_val, currency = val, cur
                        break

            if amount_val is None:
                if raw_amount or raw_amount_account:
                    errors.append(f"PDF строка {row_num}: неверный формат суммы '{raw_amount}' / '{raw_amount_account}'")
                continue

            # Determine type from direction column or amount sign
            if raw_direction:
                direction = raw_direction.strip().lower()
                tx_type = "income" if "приход" in direction else "expense"
            else:
                tx_type = "income" if amount_val > 0 else "expense"
            abs_amount = abs(amount_val)

            # Skip zero amounts
            if abs_amount < 0.01:
                continue

            mcc = raw_mcc.strip() if raw_mcc and raw_mcc.strip() else None
            description = raw_place.strip() if raw_place and raw_place.strip() else raw_type.strip()
            operation_type = raw_type.strip() if raw_type else ""

            suggested_category = categorize(mcc, operation_type)

            tx_hash = hashlib.sha256(
                f"{date}|{amount_val}|{description}".encode("utf-8")
            ).hexdigest()

            transactions.append(ParsedTransaction(
                date=date,
                amount=round(abs_amount, 2),
                currency=currency or "BYN",
                type=tx_type,
                description=description,
                mcc=mcc,
                operation_type=operation_type,
                suggested_category=suggested_category,
                hash=tx_hash,
            ))

        except Exception as e:
            errors.append(f"PDF строка {row_num}: ошибка парсинга - {str(e)}")

    return transactions, errors, metadata


def _get_col(cells: list[str], idx: int | None) -> str:
    if idx is None or idx >= len(cells):
        return ""
    return cells[idx] or ""


def _extract_date(raw: str) -> str | None:
    match = re.search(r"(\d{4}-\d{2}-\d{2})", raw)
    if match:
        return match.group(1)

    match = re.search(r"(\d{2})\.(\d{2})\.(\d{4})", raw)
    if match:
        return f"{match.group(3)}-{match.group(2)}-{match.group(1)}"

    return None


def _parse_amount(raw: str) -> tuple[float | None, str | None]:
    raw = raw.strip()
    if not raw:
        return None, None

    match = re.match(r"(-?\d+[.,]\d+)\s*(?:/[\d.,]+\s*)?([A-Z]{3})?", raw)
    if match:
        amount_str = match.group(1).replace(",", ".")
        currency = match.group(2)
        return float(amount_str), currency

    match = re.match(r"(-?\d+[.,]\d+)", raw)
    if match:
        return float(match.group(1).replace(",", ".")), None

    return None, None
