import time
from abc import ABC, abstractmethod
from urllib.parse import quote_plus

from bs4 import BeautifulSoup
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import (
    TimeoutException,
    WebDriverException,
    NoSuchElementException,
)

import config
from utils.browser import create_driver, safe_quit
from utils.logger import get_logger
from utils.parser import is_relevant, clean_text


class BaseScraper(ABC):
    name = "BaseSite"
    base_url = ""
    wait_selector = "body"

    def __init__(self, product: str, brand: str = "", model: str = ""):
        self.product = product
        self.brand = brand or ""
        self.model = model or ""
        self.query = " ".join(filter(None, [product, brand, model]))
        self.logger = get_logger(self.name)

    def build_search_url(self) -> str:
        return self.base_url.format(query=quote_plus(self.query))

    @abstractmethod
    def parse(self, html: str) -> list:
        raise NotImplementedError

    def fetch_html(self, url: str) -> str:
        last_error = None
        for attempt in range(1, config.MAX_RETRIES + 1):
            driver = None
            try:
                driver = create_driver()
                driver.get(url)
                WebDriverWait(driver, config.ELEMENT_WAIT_TIMEOUT).until(
                    EC.presence_of_element_located((By.CSS_SELECTOR, self.wait_selector))
                )
                time.sleep(1.5)
                html = driver.page_source
                safe_quit(driver)
                return html
            except TimeoutException:
                last_error = "Timeout while waiting for page to load"
                self.logger.warning(f"[{self.name}] Attempt {attempt}/{config.MAX_RETRIES} timed out")
            except WebDriverException as exc:
                last_error = str(exc)
                self.logger.warning(f"[{self.name}] Attempt {attempt}/{config.MAX_RETRIES} WebDriver error: {exc}")
            except Exception as exc:
                last_error = str(exc)
                self.logger.warning(f"[{self.name}] Attempt {attempt}/{config.MAX_RETRIES} unexpected error: {exc}")
            finally:
                safe_quit(driver)

            if attempt < config.MAX_RETRIES:
                time.sleep(config.RETRY_BACKOFF_SECONDS * attempt)

        self.logger.error(f"[{self.name}] Failed to fetch page after {config.MAX_RETRIES} attempts: {last_error}")
        return ""

    def filter_relevant(self, items: list) -> list:
        relevant = []
        for item in items:
            title = item.get("product_name", "")
            if is_relevant(title, self.product, self.brand, self.model, config.FUZZY_THRESHOLD):
                relevant.append(item)
        return relevant

    def make_soup(self, html: str) -> BeautifulSoup:
        return BeautifulSoup(html, "html.parser")

    def safe_text(self, element, default: str = "") -> str:
        if element is None:
            return default
        return clean_text(element.get_text())

    def safe_attr(self, element, attr: str, default: str = "") -> str:
        if element is None:
            return default
        value = element.get(attr, default)
        if isinstance(value, list):
            value = value[0] if value else default
        return value

    def absolute_url(self, url: str) -> str:
        if not url:
            return ""
        if url.startswith("http"):
            return url
        if url.startswith("//"):
            return "https:" + url
        base = self.base_url.split("/search")[0].split("?")[0]
        origin = "/".join(base.split("/")[:3])
        if url.startswith("/"):
            return origin + url
        return origin + "/" + url

    def parse_magento_grid(self, html: str, item_selector: str = "li.product-item") -> list:
        soup = self.make_soup(html)
        items = []
        for card in soup.select(item_selector):
            link_el = card.select_one("a.product-item-link") or card.select_one("a.product-item-photo")
            title_el = card.select_one("a.product-item-link") or card.select_one(".product-item-name")
            image_el = card.select_one("img.product-image-photo") or card.select_one("img")
            price_el = card.select_one("span.price-wrapper span.price") or card.select_one("span.price")
            old_price_el = card.select_one(".old-price .price")
            discount_el = card.select_one(".discount-percent") or card.select_one(".label-sale")
            stock_el = card.select_one(".stock") or card.select_one(".stock-availability")

            title = self.safe_text(title_el)
            if not title:
                continue

            price_val = self._extract_price(self.safe_text(price_el))
            old_price_val = self._extract_price(self.safe_text(old_price_el))

            items.append({
                "website": self.name,
                "product_name": title,
                "brand": self.brand,
                "price": price_val,
                "old_price": old_price_val,
                "discount": self._extract_discount(self.safe_text(discount_el)) or self._compute_discount(price_val, old_price_val),
                "availability": self.safe_text(stock_el) or "Unknown",
                "product_url": self.absolute_url(self.safe_attr(link_el, "href")),
                "product_image": self.safe_attr(image_el, "src"),
            })
        return items

    def parse_woocommerce_grid(self, html: str, item_selector: str = "ul.products li.product") -> list:
        soup = self.make_soup(html)
        items = []
        for card in soup.select(item_selector):
            link_el = card.select_one("a.woocommerce-LoopProduct-link") or card.select_one("a")
            title_el = (
                card.select_one("h2.woocommerce-loop-product__title")
                or card.select_one(".woocommerce-loop-product__title")
                or card.select_one("h2")
            )
            image_el = card.select_one("img")
            current_price_el = card.select_one("span.price ins span.amount") or card.select_one("span.price span.amount")
            old_price_el = card.select_one("span.price del span.amount")
            stock_el = card.select_one(".stock")

            title = self.safe_text(title_el)
            if not title:
                continue

            current_price = self._extract_price(self.safe_text(current_price_el))
            old_price = self._extract_price(self.safe_text(old_price_el))

            items.append({
                "website": self.name,
                "product_name": title,
                "brand": self.brand,
                "price": current_price,
                "old_price": old_price,
                "discount": self._compute_discount(current_price, old_price),
                "availability": self.safe_text(stock_el) or "In Stock",
                "product_url": self.absolute_url(self.safe_attr(link_el, "href")),
                "product_image": self.safe_attr(image_el, "data-src") or self.safe_attr(image_el, "src"),
            })
        return items

    @staticmethod
    def _extract_price(text: str):
        from utils.parser import extract_price
        return extract_price(text)

    @staticmethod
    def _extract_discount(text: str):
        from utils.parser import extract_discount
        return extract_discount(text)

    @staticmethod
    def _compute_discount(current_price, old_price):
        if current_price is None or old_price is None or old_price <= 0:
            return None
        if old_price <= current_price:
            return None
        percent = round((old_price - current_price) / old_price * 100)
        return f"-{percent}%"

    def run(self) -> list:
        try:
            url = self.build_search_url()
            self.logger.info(f"[{self.name}] Searching: {url}")
            html = self.fetch_html(url)
            if not html:
                return []
            raw_items = self.parse(html)
            relevant_items = self.filter_relevant(raw_items)
            self.logger.info(f"[{self.name}] Found {len(relevant_items)} relevant product(s)")
            return relevant_items
        except Exception as exc:
            self.logger.error(f"[{self.name}] Scraper failed entirely: {exc}")
            return []
