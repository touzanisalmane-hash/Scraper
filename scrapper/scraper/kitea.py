from scraper.base_scraper import BaseScraper


class KiteaScraper(BaseScraper):
    name = "Kitea"
    base_url = "https://www.kitea.ma/catalogsearch/result/?q={query}"
    wait_selector = "body"

    def parse(self, html: str) -> list:
        return self.parse_magento_grid(html, item_selector="li.product-item")
