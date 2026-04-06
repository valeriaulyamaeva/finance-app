from fastapi import FastAPI, UploadFile, File

from app.parsers.csv_parser import parse_csv
from app.parsers.pdf_parser import parse_pdf
from app.schemas import ParseResponse

app = FastAPI(title="Vale Statement Parser")


@app.get("/health")
async def health():
    return {"status": "ok"}


@app.post("/parse/csv", response_model=ParseResponse)
async def handle_csv(file: UploadFile = File(...)):
    content = await file.read()
    transactions, errors, metadata = parse_csv(content)

    return ParseResponse(
        success=len(transactions) > 0,
        transactions=transactions,
        errors=errors,
        metadata=metadata,
    )


@app.post("/debug/pdf")
async def debug_pdf(file: UploadFile = File(...)):
    import pdfplumber
    from io import BytesIO
    content = await file.read()
    pdf = pdfplumber.open(BytesIO(content))
    result = []
    for page_num, page in enumerate(pdf.pages[:2]):
        tables = page.extract_tables()
        for t_idx, table in enumerate(tables):
            for r_idx, row in enumerate(table[:5]):
                result.append({
                    "page": page_num,
                    "table": t_idx,
                    "row": r_idx,
                    "cells": [str(c)[:80] if c else "" for c in row],
                })
        if not tables:
            text = page.extract_text() or ""
            result.append({"page": page_num, "no_tables": True, "text": text[:500]})
    pdf.close()
    return {"rows": result}


@app.post("/debug/csv")
async def debug_csv(file: UploadFile = File(...)):
    content = await file.read()
    try:
        text = content.decode("windows-1251")
    except UnicodeDecodeError:
        text = content.decode("utf-8")

    lines = text.replace("\r\n", "\n").strip().split("\n")
    result = []
    for i, line in enumerate(lines[:20]):
        result.append({"line": i, "content": line[:300]})
    return {"lines": result, "total": len(lines)}


@app.post("/parse/pdf", response_model=ParseResponse)
async def handle_pdf(file: UploadFile = File(...)):
    content = await file.read()
    transactions, errors, metadata = parse_pdf(content)

    return ParseResponse(
        success=len(transactions) > 0,
        transactions=transactions,
        errors=errors,
        metadata=metadata,
    )
