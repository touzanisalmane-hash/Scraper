import sys
from concurrent.futures import ThreadPoolExecutor, as_completed

from rich.console import Console
from rich.table import Table
from rich.prompt import Prompt
from rich.progress import (
    Progress,
    SpinnerColumn,
    BarColumn,
    TextColumn,
    TimeElapsedColumn,
)
from rich.panel import Panel

import config
from scraper.registry import get_all_scrapers
from utils.exporter import export_all
from utils.logger import get_logger

console = Console()
logger = get_logger("main")


def get_user_input():
    console.print(Panel.fit(
        "[bold cyan]Moroccan E-Commerce Product Search[/bold cyan]\n"
        "Search across the biggest e-commerce websites in Morocco",
        border_style="cyan",
    ))
    product = Prompt.ask("[bold yellow]Enter product name[/bold yellow]")
    while not product.strip():
        console.print("[red]Product name cannot be empty[/red]")
        product = Prompt.ask("[bold yellow]Enter product name[/bold yellow]")

    brand = Prompt.ask("[bold yellow]Enter brand (optional)[/bold yellow]", default="")
    model = Prompt.ask("[bold yellow]Enter model (optional)[/bold yellow]", default="")

    return product.strip(), brand.strip(), model.strip()


def run_scrapers(product, brand, model):
    scrapers = get_all_scrapers(product, brand, model)
    all_results = []

    with Progress(
        SpinnerColumn(),
        TextColumn("[progress.description]{task.description}"),
        BarColumn(),
        TextColumn("[progress.percentage]{task.percentage:>3.0f}%"),
        TimeElapsedColumn(),
        console=console,
    ) as progress:
        task = progress.add_task("Searching Moroccan e-commerce websites...", total=len(scrapers))

        with ThreadPoolExecutor(max_workers=config.MAX_WORKERS) as executor:
            future_to_scraper = {executor.submit(scraper.run): scraper for scraper in scrapers}

            for future in as_completed(future_to_scraper):
                scraper = future_to_scraper[future]
                try:
                    results = future.result()
                    all_results.extend(results)
                except Exception as exc:
                    logger.error(f"[{scraper.name}] Unhandled exception: {exc}")
                finally:
                    progress.advance(task)

    return all_results


def sort_results(results):
    priced = [r for r in results if r.get("price") is not None]
    unpriced = [r for r in results if r.get("price") is None]
    priced.sort(key=lambda r: r["price"])
    return priced + unpriced


def format_price(value):
    if value is None:
        return "N/A"
    return f"{value:,.2f} {config.CURRENCY_SYMBOL}"


def display_results(results):
    if not results:
        console.print("[bold red]No matching products found on any website.[/bold red]")
        return

    table = Table(title="Search Results", show_lines=True, header_style="bold white on blue")
    table.add_column("Website", style="cyan", no_wrap=True)
    table.add_column("Product", style="white")
    table.add_column("Price", style="green", justify="right")
    table.add_column("Availability", style="magenta")
    table.add_column("Product Link", style="blue", overflow="fold")

    cheapest_price = None
    for r in results:
        if r.get("price") is not None:
            cheapest_price = r["price"]
            break

    for r in results:
        is_cheapest = cheapest_price is not None and r.get("price") == cheapest_price
        website = r.get("website", "")
        product_name = r.get("product_name", "")
        price = format_price(r.get("price"))
        availability = r.get("availability", "Unknown")
        url = r.get("product_url", "")

        if is_cheapest:
            table.add_row(
                f"[bold on green]{website}[/bold on green]",
                f"[bold on green]{product_name}[/bold on green]",
                f"[bold on green]{price}  BEST PRICE[/bold on green]",
                f"[bold on green]{availability}[/bold on green]",
                f"[bold on green]{url}[/bold on green]",
            )
        else:
            table.add_row(website, product_name, price, availability, url)

    console.print(table)


def main():
    try:
        product, brand, model = get_user_input()

        console.print(f"\n[bold]Searching for:[/bold] {product} {brand} {model}\n")

        raw_results = run_scrapers(product, brand, model)
        sorted_results = sort_results(raw_results)

        console.print()
        display_results(sorted_results)

        if sorted_results:
            paths = export_all(sorted_results, base_name=product.replace(" ", "_"))
            console.print("\n[bold green]Export complete:[/bold green]")
            for fmt, path in paths.items():
                console.print(f"  [cyan]{fmt.upper()}[/cyan]: {path}")
        else:
            console.print("[yellow]Nothing to export.[/yellow]")

    except KeyboardInterrupt:
        console.print("\n[red]Search interrupted by user.[/red]")
        sys.exit(1)
    except Exception as exc:
        logger.error(f"Fatal error: {exc}")
        console.print(f"[bold red]Fatal error:[/bold red] {exc}")
        sys.exit(1)


if __name__ == "__main__":
    main()
