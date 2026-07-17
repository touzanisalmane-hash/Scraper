from scraper.base_scraper import BaseScraper


class TangeroisScraper(BaseScraper):
    name = "Tangerois"
    base_url = "https://www.tangerois.com/?s={query}&post_type=product"
    wait_selector = "body"

    def parse(self, html: str) -> list:
        return self.parse_woocommerce_grid(html, item_selector="ul.products li.product")
