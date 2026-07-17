from scraper.base_scraper import BaseScraper


class MarjaneScraper(BaseScraper):
    name = "Marjane"
    base_url = "https://www.marjane.ma/catalogsearch/result/?q={query}"
    wait_selector = "body"

    def parse(self, html: str) -> list:
        return self.parse_magento_grid(html, item_selector="li.product-item")
