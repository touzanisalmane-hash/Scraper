import os
from dotenv import load_dotenv

load_dotenv()

HEADLESS = os.getenv("HEADLESS", "true").lower() == "true"
PAGE_LOAD_TIMEOUT = int(os.getenv("PAGE_LOAD_TIMEOUT", "20"))
ELEMENT_WAIT_TIMEOUT = int(os.getenv("ELEMENT_WAIT_TIMEOUT", "12"))
MAX_RETRIES = int(os.getenv("MAX_RETRIES", "3"))
RETRY_BACKOFF_SECONDS = float(os.getenv("RETRY_BACKOFF_SECONDS", "2"))
MAX_WORKERS = int(os.getenv("MAX_WORKERS", "10"))
FUZZY_THRESHOLD = int(os.getenv("FUZZY_THRESHOLD", "55"))
OUTPUT_DIR = os.getenv("OUTPUT_DIR", "output")
REQUEST_TIMEOUT = int(os.getenv("REQUEST_TIMEOUT", "15"))
LOG_LEVEL = os.getenv("LOG_LEVEL", "INFO")

SUPPORTED_SITES = [
    "jumia",
    "electroplanet",
    "marjane",
    "biougnach",
    "cosmoselectro",
    "microchoix",
    "tangerois",
    "kitea",
    "hmall",
    "virginmegastore",
]

CURRENCY_SYMBOL = "DH"

os.makedirs(OUTPUT_DIR, exist_ok=True)
