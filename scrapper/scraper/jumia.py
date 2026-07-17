from scraper.base_scraper import BaseScraper


class JumiaScraper(BaseScraper):
    name = "Jumia Maroc"
    base_url = "https://www.jumia.ma/catalog/?q={query}"
    wait_selector = "body"

    def parse(self, html: str) -> list:
        soup = self.make_soup(html)
        items = []
        cards = soup.select("article.prd") or soup.select("div.sku")

        for card in cards:
            if card.select_one(".bdg_ad") or card.select_one(".ad-tag"):
                continue

            link_el = card.select_one("a.core") or card.select_one("a")
            title_el = card.select_one("h3.name") or card.select_one(".name")
            image_el = card.select_one("img.img") or card.select_one("img")
            price_el = card.select_one("div.prc") or card.select_one(".prc")
            old_price_el = card.select_one("div.old") or card.select_one(".old")
            discount_el = card.select_one("div.tag._dsct") or card.select_one(".bdg._dsct")
            rating_el = card.select_one(".stars")

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
                "availability": "In Stock",
                "product_url": self.absolute_url(self.safe_attr(link_el, "href")),
                "product_image": self.safe_attr(image_el, "data-src") or self.safe_attr(image_el, "src"),
            })

        return items
