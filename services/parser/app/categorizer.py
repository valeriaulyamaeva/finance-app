"""
MCC-based categorization with merchant name fallback.

Sources:
- https://mcc-codes.ru/
- https://tannei.by/mcc/codes/
"""

# MCC ranges
MCC_CATEGORY_MAP: dict[str, str | None] = {
    # ─── Transport ───────────────────────────────────────────────
    "4011": "Транспорт",  # Railway
    "4111": "Транспорт",  # Local transit / ferries
    "4112": "Транспорт",  # Passenger rail
    "4121": "Транспорт",  # Taxis / limousines
    "4131": "Транспорт",  # Bus lines
    "4304": "Путешествия",  # Cargo
    "4411": "Путешествия",  # Cruise lines
    "4511": "Путешествия",  # Airlines
    "4582": "Путешествия",  # Airports
    "4722": "Путешествия",  # Travel agencies
    "4784": "Транспорт",  # Tolls / bridge fees
    "4789": "Транспорт",  # Transportation services - other
    "5511": "Авто",  # Car/truck dealers
    "5521": "Авто",  # Used car dealers
    "5531": "Авто",  # Auto supplies
    "5532": "Авто",  # Tire stores
    "5533": "Авто",  # Auto parts
    "5599": "Авто",
    "7531": "Авто",  # Auto body
    "7534": "Авто",  # Tire retreading
    "7535": "Авто",  # Paint shops
    "7538": "Авто",  # Auto service shops
    "7542": "Авто",  # Car wash
    "7549": "Авто",  # Towing services

    # ─── Fuel ────────────────────────────────────────────────────
    "5541": "Топливо",
    "5542": "Топливо",
    "5552": "Топливо",  # EV charging
    "5983": "Топливо",  # Fuel dealers (heating oil)

    # ─── Telecom / Internet ──────────────────────────────────────
    "4812": "Связь",  # Telecom equipment
    "4814": "Связь",  # Telecom services
    "4816": "Связь",  # Computer network services
    "4821": "Связь",  # Telegraph services

    # ─── Utilities / Communal ────────────────────────────────────
    "4900": "Коммунальные",
    "4931": "Коммунальные",
    "4932": "Коммунальные",

    # ─── Subscriptions / Streaming / Software ────────────────────
    "4899": "Подписки",  # Cable/satellite
    "5734": "Подписки",  # Computer software
    "5735": "Подписки",  # Record stores / digital media
    "5815": "Подписки",  # Digital goods - media
    "5816": "Подписки",  # Digital goods - games
    "5817": "Подписки",  # Digital goods - applications
    "5818": "Подписки",  # Digital goods - large purchase
    "7372": "Подписки",  # SaaS
    "7375": "Подписки",  # Information retrieval

    # ─── Groceries ───────────────────────────────────────────────
    "5300": "Продукты",  # Wholesale clubs
    "5310": "Продукты",  # Discount stores
    "5331": "Продукты",  # Variety stores
    "5399": "Продукты",
    "5411": "Продукты",
    "5412": "Продукты",
    "5422": "Продукты",  # Meat
    "5441": "Продукты",  # Confectionery
    "5451": "Продукты",
    "5462": "Продукты",  # Bakery
    "5499": "Продукты",
    "5921": "Алкоголь",

    # ─── Restaurants / Fast food ─────────────────────────────────
    "5811": "Рестораны",  # Caterers
    "5812": "Рестораны",  # Restaurants
    "5813": "Развлечения",  # Bars / nightclubs
    "5814": "Еда",  # Fast food

    # ─── Clothing & Footwear ─────────────────────────────────────
    "5611": "Одежда",
    "5621": "Одежда",
    "5631": "Одежда",
    "5641": "Одежда",
    "5651": "Одежда",
    "5661": "Одежда",  # Shoe stores
    "5681": "Одежда",  # Furriers
    "5691": "Одежда",
    "5697": "Одежда",  # Tailors
    "5698": "Одежда",
    "5699": "Одежда",
    "5931": "Одежда",  # Used merchandise / second-hand
    "5949": "Одежда",  # Sewing / fabric stores

    # ─── Health & Pharmacy ───────────────────────────────────────
    "5912": "Здоровье",  # Pharmacy
    "5975": "Здоровье",  # Hearing aids
    "5976": "Здоровье",  # Orthopedic goods
    "8011": "Здоровье",  # Doctors
    "8021": "Здоровье",  # Dentists
    "8031": "Здоровье",
    "8041": "Здоровье",  # Chiropractors
    "8042": "Здоровье",  # Optometrists
    "8043": "Здоровье",  # Optical goods / eyewear
    "8049": "Здоровье",  # Podiatrists
    "8050": "Здоровье",  # Nursing care
    "8062": "Здоровье",  # Hospitals
    "8071": "Здоровье",  # Medical labs
    "8099": "Здоровье",

    # ─── Beauty & Personal care ──────────────────────────────────
    "5977": "Красота",  # Cosmetics stores
    "7230": "Красота",  # Hair salons
    "7297": "Красота",  # Massage parlors
    "7298": "Красота",  # Health & beauty spas
    "7977": "Красота",

    # ─── Entertainment ───────────────────────────────────────────
    "7832": "Развлечения",  # Cinemas
    "7841": "Развлечения",  # Video rental
    "7911": "Развлечения",
    "7922": "Развлечения",  # Theatre / ticketing
    "7929": "Развлечения",
    "7932": "Развлечения",  # Billiards
    "7933": "Развлечения",  # Bowling
    "7941": "Развлечения",  # Sports / athletic
    "7991": "Развлечения",
    "7993": "Развлечения",
    "7994": "Развлечения",
    "7995": "Развлечения",  # Gambling
    "7996": "Развлечения",  # Amusement parks
    "7997": "Развлечения",  # Country clubs
    "7998": "Развлечения",
    "7999": "Развлечения",

    # ─── Travel / Hotels ─────────────────────────────────────────
    "3000": "Путешествия",  # Airlines (specific)
    "3001": "Путешествия",
    "3500": "Путешествия",  # Hotels (specific)
    "7011": "Путешествия",  # Hotels / motels
    "7012": "Путешествия",  # Timeshares
    "7032": "Путешествия",  # Recreational camps
    "7033": "Путешествия",
    "7512": "Путешествия",  # Car rentals
    "7513": "Путешествия",
    "7519": "Путешествия",

    # ─── Education ───────────────────────────────────────────────
    "8211": "Образование",  # Schools - K-12
    "8220": "Образование",  # Colleges
    "8241": "Образование",  # Correspondence schools
    "8244": "Образование",  # Business / secretarial schools
    "8249": "Образование",  # Vocational schools
    "8299": "Образование",

    # ─── Home / Furniture / Hardware ─────────────────────────────
    "5021": "Дом",  # Office furniture
    "5039": "Дом",  # Construction materials
    "5200": "Дом",  # Home supply
    "5211": "Дом",  # Lumber / building materials
    "5231": "Дом",  # Glass / paint / wallpaper
    "5251": "Дом",  # Hardware stores
    "5261": "Дом",  # Lawn & garden
    "5262": "Маркетплейс",  # Marketplaces
    "5311": "Дом",  # Department stores
    "5712": "Дом",  # Furniture
    "5713": "Дом",  # Floor coverings
    "5714": "Дом",  # Drapery / upholstery
    "5718": "Дом",  # Fireplace stores
    "5719": "Дом",  # Misc home furnishings
    "5722": "Электроника",  # Household appliances
    "5732": "Электроника",  # Electronics
    "5733": "Хобби",  # Music instruments
    "5942": "Хобби",  # Book stores
    "5943": "Хобби",  # Stationery
    "5945": "Хобби",  # Toys / hobby
    "5946": "Хобби",  # Camera shops
    "5947": "Хобби",  # Gift / novelty
    "5948": "Хобби",  # Leather goods
    "5970": "Хобби",  # Artist supplies
    "5971": "Хобби",  # Art dealers
    "5973": "Хобби",  # Religious goods

    # ─── Sports / Fitness ────────────────────────────────────────
    "5655": "Спорт",  # Sport / riding apparel
    "5940": "Спорт",  # Bicycle shops
    "5941": "Спорт",  # Sporting goods
    "7032": "Спорт",  # Recreational camps
    "7997": "Спорт",  # Athletic clubs
    "7991": "Спорт",  # Tourist attractions

    # ─── Pets ────────────────────────────────────────────────────
    "0742": "Питомцы",  # Veterinary
    "5995": "Питомцы",  # Pet stores

    # ─── Various / Marketplaces ──────────────────────────────────
    "5993": "Прочее",  # Cigar stores
    "5994": "Хобби",  # News dealers
    "5999": "Прочее",  # Misc retail

    # ─── Services (general) ──────────────────────────────────────
    "7210": "Прочее",  # Cleaning / laundry
    "7211": "Прочее",
    "7216": "Прочее",
    "7261": "Прочее",  # Funerals
    "7273": "Прочее",  # Dating / escort
    "7276": "Прочее",  # Tax preparation
    "7277": "Прочее",  # Counseling
    "7278": "Прочее",
    "7299": "Прочее",  # Misc services

    # ─── Charity / Donations ─────────────────────────────────────
    "8398": "Благотворительность",
    "8651": "Благотворительность",
    "8661": "Благотворительность",  # Religious

    # ─── Advertising ─────────────────────────────────────────────
    "7311": "Реклама",
    "7361": "Прочее",  # Employment agencies
    "7392": "Прочее",  # Consulting

    # ─── P2P / Transfers (handled by direction) ──────────────────
    "6010": "P2P",  # Cash advance
    "6011": "P2P",  # ATM
    "6012": "P2P",
    "6051": "P2P",
    "6211": "P2P",
    "6536": "P2P",
    "6537": "P2P",
    "6538": "P2P",
    "6540": "P2P",
}

# Operation-type heuristics (substring match on operation_type)
OPERATION_TYPE_CATEGORY: dict[str, str] = {
    "Начисление на счёт": "Зарплата",
    "Начисление на счт": "Зарплата",
    "Начисление на сч": "Зарплата",
    "Зачисление из АБС": "Зарплата",
    "Зачисление зарплаты": "Зарплата",
    "Зачисление": "Зарплата",
    "Возврат": "Возврат",
    "Кешбэк": "Кэшбек",
    "Кэшбэк": "Кэшбек",
    "Cashback": "Кэшбек",
    "Бонус": "Кэшбек",
    "Автосписание": "Автосписания",
    "Автоплатеж": "Автосписания",
}

# Merchant-name heuristics (substring match on description, case-insensitive)
MERCHANT_KEYWORDS: list[tuple[str, str]] = [
    # Fast food
    ("MCDONALD", "Еда"),
    ("MCDONALDS", "Еда"),
    ("KFC", "Еда"),
    ("BURGER KING", "Еда"),
    ("DOMINO", "Еда"),
    ("PIZZA", "Еда"),
    ("PEKARNYA", "Еда"),
    ("PRINOSIM RADOST", "Еда"),
    ("FOOD", "Еда"),
    ("DONER", "Еда"),
    ("SHAURMA", "Еда"),
    ("YANDEX.EDA", "Еда"),
    ("YANDEX EDA", "Еда"),
    ("DELIVERY CLUB", "Еда"),
    ("FOODCOURT", "Еда"),
    ("FOOD COURT", "Еда"),
    ("STARBUCKS", "Еда"),
    ("COFFEE", "Еда"),

    # Subscriptions
    ("SPOTIFY", "Подписки"),
    ("NETFLIX", "Подписки"),
    ("YOUTUBE", "Подписки"),
    ("APPLE.COM", "Подписки"),
    ("GOOGLE*", "Подписки"),
    ("ICLOUD", "Подписки"),
    ("YANDEX.PLUS", "Подписки"),
    ("YANDEX PLUS", "Подписки"),
    ("KINOPOISK", "Подписки"),
    ("CHATGPT", "Подписки"),
    ("OPENAI", "Подписки"),
    ("ANTHROPIC", "Подписки"),
    ("CLAUDE", "Подписки"),
    ("GITHUB", "Подписки"),
    ("VERCEL", "Подписки"),
    ("FIGMA", "Подписки"),
    ("NOTION", "Подписки"),
    ("DROPBOX", "Подписки"),
    ("TELEGRAM", "Подписки"),
    ("DISCORD", "Подписки"),
    ("DEEZER", "Подписки"),

    # Marketplaces
    ("WILDBERRIES", "Маркетплейс"),
    ("OZON", "Маркетплейс"),
    ("ALIEXPRESS", "Маркетплейс"),
    ("AMAZON", "Маркетплейс"),
    ("EBAY", "Маркетплейс"),
    ("21VEK", "Маркетплейс"),

    # Groceries (chain stores BY)
    ("EUROOPT", "Продукты"),
    ("SOSEDI", "Продукты"),
    ("SANTA", "Продукты"),
    ("ALMI", "Продукты"),
    ("MILA", "Продукты"),
    ("KORONA", "Продукты"),
    ("GREEN", "Продукты"),
    ("RUBLEVSKIY", "Продукты"),
    ("HIPPO", "Продукты"),
    ("MART INN", "Продукты"),
    ("ОMA", "Дом"),

    # Transport / mobility
    ("YANDEX GO", "Транспорт"),
    ("YANDEX.GO", "Транспорт"),
    ("UBER", "Транспорт"),
    ("BOLT", "Транспорт"),
    ("PASS.RW.BY", "Транспорт"),
    ("PAYBYCARD", "Транспорт"),
    ("METRO", "Транспорт"),

    # Mobile / internet
    ("MTS", "Связь"),
    ("BEELINE", "Связь"),
    ("A1 ", "Связь"),
    ("LIFE", "Связь"),
    ("BYFLY", "Связь"),

    # Pharmacy / health
    ("APTEKA", "Здоровье"),
    ("PHARMACY", "Здоровье"),
    ("DENTA", "Здоровье"),
    ("SMAYL", "Здоровье"),  # Denta Smayl

    # Beauty
    ("BEAUTY", "Красота"),
    ("SALON", "Красота"),
    ("BARBER", "Красота"),
    ("MANIKUR", "Красота"),

    # Travel
    ("BOOKING", "Путешествия"),
    ("AIRBNB", "Путешествия"),
    ("AVIASALES", "Путешествия"),
    ("HOTEL", "Путешествия"),

    # Fitness
    ("FITNESS", "Спорт"),
    ("GYM ", "Спорт"),

    # Donations / charity
    ("DONATE", "Благотворительность"),
    ("CHARITY", "Благотворительность"),
]


def categorize(mcc: str | None, operation_type: str, description: str = "", direction: str = "expense") -> str | None:
    """
    Determine category for a transaction.

    Priority:
    1. Operation type (Зарплата, Возврат, Кэшбек) — most reliable
    2. Merchant keywords (overrides MCC for chains)
    3. MCC code lookup
    4. P2P split by direction (income/expense)
    """
    op = (operation_type or "").strip()
    desc = (description or "").upper()

    # 1. Operation type
    for key, category in OPERATION_TYPE_CATEGORY.items():
        if key.lower() in op.lower():
            return category

    # 2. Merchant keywords
    for keyword, category in MERCHANT_KEYWORDS:
        if keyword in desc:
            return category

    # 3. MCC-based
    if mcc and mcc in MCC_CATEGORY_MAP:
        cat = MCC_CATEGORY_MAP[mcc]
        if cat == "P2P":
            return "Переводы (доход)" if direction == "income" else "Переводы (расход)"
        return cat

    return None
