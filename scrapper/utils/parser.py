import re
from difflib import SequenceMatcher


def normalize_text(text: str) -> str:
    if not text:
        return ""
    text = text.lower().strip()
    text = re.sub(r"[^a-z0-9\u0600-\u06FF\s]", " ", text)
    text = re.sub(r"\s+", " ", text)
    return text.strip()


def similarity_ratio(a: str, b: str) -> float:
    return SequenceMatcher(None, normalize_text(a), normalize_text(b)).ratio() * 100


def fuzzy_score(title: str, product: str, brand: str = "", model: str = "") -> float:
    query_parts = [product]
    if brand:
        query_parts.append(brand)
    if model:
        query_parts.append(model)
    full_query = " ".join(query_parts)

    base_score = similarity_ratio(title, full_query)

    norm_title = normalize_text(title)
    tokens_hit = 0
    tokens_total = 0
    for part in query_parts:
        for token in normalize_text(part).split():
            if len(token) < 2:
                continue
            tokens_total += 1
            if token in norm_title:
                tokens_hit += 1

    token_score = (tokens_hit / tokens_total * 100) if tokens_total else 0

    return max(base_score, token_score)


def is_relevant(title: str, product: str, brand: str = "", model: str = "", threshold: float = 55) -> bool:
    if not title:
        return False
    return fuzzy_score(title, product, brand, model) >= threshold


def extract_price(raw_text: str):
    if not raw_text:
        return None
    cleaned = raw_text.replace("\xa0", " ").replace(",", "")
    cleaned = re.sub(r"(?<=\d)\.(?=\d{3}(\D|$))", "", cleaned)
    match = re.search(r"(\d+(?:\.\d+)?)", cleaned)
    if not match:
        return None
    try:
        return float(match.group(1))
    except ValueError:
        return None


def extract_discount(raw_text: str):
    if not raw_text:
        return None
    match = re.search(r"(\d+)\s*%", raw_text)
    if match:
        return f"-{match.group(1)}%"
    return None


def clean_text(text: str) -> str:
    if not text:
        return ""
    return re.sub(r"\s+", " ", text).strip()
