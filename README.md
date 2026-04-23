# eBay Germany Flat-File Generator (PHP 8.2)

Production-grade CLI application that builds eBay Germany flat-file output **strictly from an existing XLSX template**.

## Architecture

- **Domain layer** (`src/Domain`): core entities and value objects (`Product`, `Manufacturer`, `EbayRow`, `Title`, `Price`, `Sku`, `ExtractedMetadata`).
- **Application layer** (`src/Application`): orchestration use-case (`GenerateEbayFlatService`) and command DTO.
- **Infrastructure layer** (`src/Infrastructure`): YSELL API client, XLSX/CSV exporters, template renderer, console logger.
- **Services** (`src/Service`): business extraction/transformation logic (title formatting, metadata extractors, category resolver, pricing strategy, description builder).
- **Validation** (`src/Validator`): row-level required-field validation before writing.

## Installation

```bash
composer install
cp .env.example .env
```

Adjust `.env` values as needed.

Required YSELL vars:
- `YSELL_BASE_URL` (for example: `https://4457.test1.ysell.pro/api`)
- `YSELL_BEARER_TOKEN` (if your API requires Bearer auth)

## CLI Usage

```bash
php bin/generate-ebay-flat.php \
  --template=./input/template.xlsx \
  --output=./output/ebay-result.xlsx
```

### Options

- `--template=PATH` - path to input XLSX template
- `--output=PATH` - output file path
- `--limit=N` - process only first N products
- `--product-id=ID` - process one product
- `--dry-run` - no file save, only processing/logging
- `--verbose` - debug logging
- `--format=xlsx|csv` - output format (`xlsx` default)

If `YSELL_BASE_URL` is missing, CLI exits with a clear config error instead of throwing low-level cURL exceptions.

Note: configuration loader reads `.env` values from `$_ENV`/`$_SERVER` first (and `getenv` as fallback), which is important on Windows/PHP setups where `getenv()` may not expose Dotenv-loaded variables.

### Troubleshooting YSELL connection

- If you see `YSELL API request failed ... after 3 attempts`, inspect the appended `Last error` in output.
- Typical causes:
  - wrong `YSELL_BASE_URL` (must include `/api` if your backend expects it),
  - missing/invalid `YSELL_BEARER_TOKEN`,
  - network/TLS issue in local PHP+cURL environment.

## Important Behavior

- Reads the **first row from template** as headers.
- Writes data from row 2 onward.
- Preserves template structure and order.
- Ignores fields missing in template headers.
- Skips invalid rows (logs errors, does not crash full process).
- Uses local keyword category resolver with fallback category.
- Uses heuristic price calculation strategy with configurable markup/min/default.

## Main Components

- `YsellClient`: API access with retries and backoff.
- `ProductMetadataExtractor`: orchestrates extraction from title.
- `LocalKeywordCategoryResolver`: local map-based category resolver.
- `HeuristicPriceCalculator`: configurable pricing strategy.
- `DescriptionBuilder`: renders German HTML listing via external template.
- `XlsxTemplateExporter`: strict template-based XLSX writer.

## Example

```bash
php bin/generate-ebay-flat.php \
  --template=./input/template.xlsx \
  --output=./output/ebay-result.xlsx \
  --limit=100 \
  --verbose
```
