from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager
from fake_useragent import UserAgent

import config
from utils.logger import get_logger

logger = get_logger("browser")

_ua_generator = None


def _get_user_agent() -> str:
    global _ua_generator
    try:
        if _ua_generator is None:
            _ua_generator = UserAgent()
        return _ua_generator.random
    except Exception:
        return (
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 "
            "(KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36"
        )


def create_driver(headless: bool = None) -> webdriver.Chrome:
    if headless is None:
        headless = config.HEADLESS

    options = Options()
    if headless:
        options.add_argument("--headless=new")

    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    options.add_argument("--disable-gpu")
    options.add_argument("--disable-extensions")
    options.add_argument("--disable-notifications")
    options.add_argument("--disable-infobars")
    options.add_argument("--window-size=1920,1080")
    options.add_argument("--log-level=3")
    options.add_argument(f"user-agent={_get_user_agent()}")
    options.add_experimental_option("excludeSwitches", ["enable-automation", "enable-logging"])
    options.add_experimental_option("useAutomationExtension", False)

    service = Service(ChromeDriverManager().install())
    driver = webdriver.Chrome(service=service, options=options)
    driver.set_page_load_timeout(config.PAGE_LOAD_TIMEOUT)

    try:
        driver.execute_cdp_cmd(
            "Page.addScriptToEvaluateOnNewDocument",
            {"source": "Object.defineProperty(navigator, 'webdriver', {get: () => undefined})"},
        )
    except Exception:
        pass

    return driver


def safe_quit(driver) -> None:
    try:
        if driver is not None:
            driver.quit()
    except Exception as exc:
        logger.debug(f"Error while closing driver: {exc}")
