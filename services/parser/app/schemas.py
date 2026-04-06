from pydantic import BaseModel


class ParsedTransaction(BaseModel):
    date: str
    amount: float
    currency: str
    type: str
    description: str
    mcc: str | None = None
    operation_type: str
    suggested_category: str | None = None
    hash: str


class ParseResponse(BaseModel):
    success: bool
    transactions: list[ParsedTransaction]
    errors: list[str]
    metadata: dict
