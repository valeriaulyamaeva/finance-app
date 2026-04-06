import hashlib
import re

from app.categorizer import categorize
from app.schemas import ParsedTransaction

# Possible column name variants for each field
COLUMN_ALIASES = {
    "date": ["Дата операции", "Дата и время совершения операции"],
    "type": ["Тип операции", "Наименование операции", "Приход/ Расход", "Приход/Расход"],
    "mcc": ["MCC", "МСС"],
    "place": ["Место транзакции", "Место проведения", "Место совершения операции"],
    "amount": ["Сумма транзакции", "Сумма в валюте транзакции", "Сумма в валюте операции"],
    "status": ["Статус"],
    "direction": ["Приход/ Расход", "Приход/Расход"],
}


def parse_csv(content: bytes) -> tuple[list[ParsedTransaction], list[str], dict]:
    errors: list[str] = []
    transactions: list[ParsedTransaction] = []
    metadata: dict = {}

    try:
        text = content.decode("windows-1251")
    except UnicodeDecodeError:
        text = content.decode("utf-8")

    lines = text.replace("\r\n", "\n").strip().split("\n")

    # Find header line (contains "Дата операции")
    header_idx = None
    for i, line in enumerate(lines):
        if "Дата операции" in line and "Тип операции" in line:
            header_idx = i
            break

    if header_idx is None:
        for i, line in enumerate(lines):
            if "Дата операции" in line:
                header_idx = i
                break

    # Extract metadata from lines before header
    if header_idx is not None:
        for i in range(header_idx):
            stripped = lines[i].strip().strip(";")
            if not stripped:
                continue
            if "Валюта" in stripped and ("счёт" in stripped.lower() or "счет" in stripped.lower()):
                metadata["currency"] = stripped.split(":")[-1].strip() if ":" in stripped else "BYN"
            elif "Карт-счёт" in stripped or "Карт-счет" in stripped:
                metadata["card_info"] = stripped

        # Try to extract currency from metadata lines
        for i in range(header_idx):
            line = lines[i].strip().strip(";")
            if "Валюта счёта" in line or "Валюта счета" in line or "Валюта счет" in line:
                parts = line.split(":")
                if len(parts) > 1:
                    metadata["currency"] = parts[-1].strip()

    if header_idx is None:
        errors.append("Не найден заголовок таблицы (строка с 'Дата операции')")
        return transactions, errors, metadata

    headers = [h.strip() for h in lines[header_idx].split(";")]

    # Build column index map
    col_map = {}
    for idx, h in enumerate(headers):
        if h:
            col_map[h] = idx

    # Resolve columns using aliases
    date_col = _find_col_by_aliases(col_map, COLUMN_ALIASES["date"])
    type_col = _find_col_by_aliases(col_map, COLUMN_ALIASES["type"])
    mcc_col = _find_col_by_aliases(col_map, COLUMN_ALIASES["mcc"])
    place_col = _find_col_by_aliases(col_map, COLUMN_ALIASES["place"])
    amount_col = _find_col_by_aliases(col_map, COLUMN_ALIASES["amount"])
    status_col = _find_col_by_aliases(col_map, COLUMN_ALIASES["status"])

    if date_col is None or amount_col is None:
        errors.append(f"Не найдены обязательные колонки. Доступные: {list(col_map.keys())}")
        return transactions, errors, metadata

    default_currency = metadata.get("currency", "BYN")

    for line_num, line in enumerate(lines[header_idx + 1:], start=header_idx + 2):
        line = line.strip()
        if not line:
            continue

        # Skip summary lines at the end
        clean = line.replace(";", "").strip()
        if not clean:
            continue
        if any(kw in clean for kw in ["Приход", "Расход", "Доля", "Итого"]):
            continue

        cols = line.split(";")

        try:
            raw_date = _get_col(cols, date_col)
            raw_amount = _get_col(cols, amount_col)
            raw_type = _get_col(cols, type_col)
            raw_mcc = _get_col(cols, mcc_col)
            raw_place = _get_col(cols, place_col)
            raw_status = _get_col(cols, status_col)

            if not raw_date.strip() or not raw_amount.strip():
                continue

            # Skip non-completed transactions
            status = raw_status.strip().lower()
            if status and status not in ("исполнено", "проведено"):
                continue

            # Parse date: "2026-03-30 17:45:04" -> "2026-03-30"
            date = raw_date.strip().split(" ")[0]
            if not re.match(r"\d{4}-\d{2}-\d{2}", date):
                # Try DD.MM.YYYY format
                match = re.match(r"(\d{2})\.(\d{2})\.(\d{4})", date)
                if match:
                    date = f"{match.group(3)}-{match.group(2)}-{match.group(1)}"
                else:
                    errors.append(f"Строка {line_num}: неверный формат даты '{raw_date}'")
                    continue

            # Parse amount
            amount_val, currency = _parse_amount(raw_amount)
            if amount_val is None:
                errors.append(f"Строка {line_num}: неверный формат суммы '{raw_amount}'")
                continue

            tx_type = "income" if amount_val > 0 else "expense"
            abs_amount = abs(amount_val)

            mcc = raw_mcc.strip() if raw_mcc and raw_mcc.strip() else None
            description = raw_place.strip() if raw_place and raw_place.strip() else raw_type.strip()
            operation_type = raw_type.strip()

            suggested_category = categorize(mcc, operation_type)

            tx_hash = hashlib.sha256(
                f"{date}|{amount_val}|{description}".encode("utf-8")
            ).hexdigest()

            transactions.append(ParsedTransaction(
                date=date,
                amount=round(abs_amount, 2),
                currency=currency or default_currency,
                type=tx_type,
                description=description,
                mcc=mcc,
                operation_type=operation_type,
                suggested_category=suggested_category,
                hash=tx_hash,
            ))

        except Exception as e:
            errors.append(f"Строка {line_num}: ошибка парсинга - {str(e)}")

    return transactions, errors, metadata


def _find_col_by_aliases(col_map: dict, aliases: list[str]) -> int | None:
    """Find column index by trying exact match first, then substring match."""
    for alias in aliases:
        # Exact match
        if alias in col_map:
            return col_map[alias]
    for alias in aliases:
        # Substring match (column name contains alias or alias contains column name)
        for key, idx in col_map.items():
            if alias.lower() in key.lower() or key.lower() in alias.lower():
                return idx
    return None


def _get_col(cols: list[str], idx: int | None) -> str:
    if idx is None or idx >= len(cols):
        return ""
    return cols[idx] or ""


def _parse_amount(raw: str) -> tuple[float | None, str | None]:
    raw = raw.strip()
    if not raw:
        return None, None

    # Match patterns like "-14.50 BYN", "423.57 BYN", "-14.50/0.00 BYN"
    match = re.match(r"(-?\d+[.,]\d+)\s*(?:/[\d.,]+\s*)?([A-Z]{3})?", raw)
    if match:
        amount_str = match.group(1).replace(",", ".")
        currency = match.group(2)
        return float(amount_str), currency

    # Try just a number
    match = re.match(r"(-?\d+[.,]\d+)", raw)
    if match:
        return float(match.group(1).replace(",", ".")), None

    return None, None
