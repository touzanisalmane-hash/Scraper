# Moroccan E-Commerce Product Search

A modular Python application that searches for a product simultaneously across the
biggest e-commerce websites in Morocco, displays results in a sortable Rich table,
highlights the cheapest offer, and exports everything to Excel, CSV, and JSON.

## Supported websites

- Jumia Maroc
- Electroplanet
- Marjane
- Biougnach
- Cosmos Electro
- Microchoix
- Tangerois
- Kitea
- Hmall
- Virgin Megastore Morocco

## Features

- Parallel scraping with `concurrent.futures.ThreadPoolExecutor`
- Selenium with headless Chrome, auto-managed via `webdriver-manager`
- User-Agent rotation with `fake-useragent`
- HTML parsing with BeautifulSoup4
- Fuzzy title matching to filter out irrelevant products
- Automatic retry with backoff and timeout handling per site
- A website failure never stops the rest of the search
- Live Rich progress bar while scraping
- Results table sorted by lowest price, cheapest offer highlighted
- Export to `.xlsx` (styled, cheapest row highlighted), `.csv`, and `.json`
- Centralized logging via Rich logging handler
- All settings configurable through `.env`

## Project structure

```
project/
│
├── main.py
├── config.py
├── requirements.txt
├── README.md
├── .env.example
│
├── scraper/
│   ├── base_scraper.py
│   ├── registry.py
│   ├── jumia.py
│   ├── electroplanet.py
│   ├── marjane.py
│   ├── biougnach.py
│   ├── cosmoselectro.py
│   ├── microchoix.py
│   ├── tangerois.py
│   ├── kitea.py
│   ├── hmall.py
│   └── virginmegastore.py
│
├── utils/
│   ├── browser.py
│   ├── exporter.py
│   ├── logger.py
│   └── parser.py
│
└── output/
```

## Requirements

- Python 3.12
- Google Chrome installed on the machine (webdriver-manager downloads the
  matching ChromeDriver automatically)

## Installation

```bash
python -m venv venv
source venv/bin/activate      # on Windows: venv\Scripts\activate
pip install -r requirements.txt
cp .env.example .env
```

## Usage

```bash
python main.py
```

You will be prompted for:

1. **Product name** (required)
2. **Brand** (optional)
3. **Model** (optional)

The app then queries all supported websites in parallel, shows a live progress
bar, filters out irrelevant results using fuzzy title matching, and prints a
Rich table sorted by price with the cheapest offer highlighted in green.

Results are automatically exported to the `output/` folder as:

- `<product>_<timestamp>.xlsx`
- `<product>_<timestamp>.csv`
- `<product>_<timestamp>.json`

## Configuration

All runtime behavior can be tuned in `.env`:

| Variable | Description | Default |
|---|---|---|
| `HEADLESS` | Run Chrome headless | `true` |
| `PAGE_LOAD_TIMEOUT` | Selenium page load timeout (seconds) | `20` |
| `ELEMENT_WAIT_TIMEOUT` | Explicit wait for results to render (seconds) | `12` |
| `MAX_RETRIES` | Retry attempts per website | `3` |
| `RETRY_BACKOFF_SECONDS` | Backoff multiplier between retries | `2` |
| `MAX_WORKERS` | Parallel scraper threads | `10` |
| `FUZZY_THRESHOLD` | Minimum fuzzy match score (0-100) to keep a product | `55` |
| `OUTPUT_DIR` | Folder for exported files | `output` |
| `LOG_LEVEL` | Logging verbosity | `INFO` |

## Notes on reliability

Moroccan e-commerce websites frequently change their HTML structure and may
introduce anti-bot protections. The scrapers are built on top of two reusable
parsing strategies (`parse_magento_grid` and `parse_woocommerce_grid` in
`scraper/base_scraper.py`) that cover the platforms most of these sites run
on. If a specific site changes its markup or blocks automated browsers, that
scraper simply returns an empty list, is logged as a warning, and the rest of
the application keeps working normally — it will never crash the whole run.

If you need to adapt a scraper to a markup change, only the `parse()` method
of the relevant file under `scraper/` needs to be updated; nothing else in
the project depends on it.

## Extending to a new website

1. Create `scraper/newsite.py` with a class inheriting from `BaseScraper`.
2. Set `name` and `base_url` (with a `{query}` placeholder).
3. Implement `parse(self, html) -> list[dict]` returning dictionaries with the
   keys: `website`, `product_name`, `brand`, `price`, `old_price`,
   `discount`, `availability`, `product_url`, `product_image`.
4. Register the class in `scraper/registry.py`.

## Disclaimer

This tool is intended for personal price-comparison use. Always check and
respect each website's `robots.txt` and Terms of Service before scraping,
and scrape responsibly (reasonable request rates, no bypassing of explicit
anti-bot protections).
