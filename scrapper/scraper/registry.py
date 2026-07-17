from scraper.jumia import JumiaScraper
from scraper.electroplanet import ElectroplanetScraper
from scraper.marjane import MarjaneScraper
from scraper.biougnach import BiougnachScraper
from scraper.cosmoselectro import CosmosElectroScraper
from scraper.microchoix import MicrochoixScraper
from scraper.tangerois import TangeroisScraper
from scraper.kitea import KiteaScraper
from scraper.hmall import HmallScraper
from scraper.virginmegastore import VirginMegastoreScraper

SCRAPER_REGISTRY = {
    "jumia": JumiaScraper,
    "electroplanet": ElectroplanetScraper,
    "marjane": MarjaneScraper,
    "biougnach": BiougnachScraper,
    "cosmoselectro": CosmosElectroScraper,
    "microchoix": MicrochoixScraper,
    "tangerois": TangeroisScraper,
    "kitea": KiteaScraper,
    "hmall": HmallScraper,
    "virginmegastore": VirginMegastoreScraper,
}


def get_all_scrapers(product: str, brand: str = "", model: str = "") -> list:
    return [cls(product=product, brand=brand, model=model) for cls in SCRAPER_REGISTRY.values()]
