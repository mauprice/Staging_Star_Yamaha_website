# honda-catalog

An installable Laravel package that scrapes structured model data (content, specifications, pricing, colours/variants, digital assets) from `motorcycles.honda.com.au`, normalises it into a MySQL schema, and exposes it to a host Laravel application. Built for use by any Honda-dealer Laravel site, not tied to one dealership's codebase.

## Install

```bash
composer require honda/catalog
php artisan honda-catalog:install
```

The install command publishes `config/honda-catalog.php` and the package's migrations, offers to run `php artisan migrate`, and asks which asset strategy to use. It never edits your `.env` file — copy the printed line in yourself if it differs from the default.

## Configuration

All behaviour lives in `config/honda-catalog.php`, published to your app:

- `base_url` / `sitemap_url` — the source site.
- `http.*` — user agent, requests-per-second throttle, timeout, retry count/backoff, and whether to respect `robots.txt`.
- `discovery.model_url_pattern` / `discovery.category_allow_list` — the sitemap is filtered by the structural pattern first, then narrowed by the category allow-list (leave empty to sync every category).
- `assets.strategy` — `cdn` (store the Content Hub/Sitecore URL, serve it directly) or `mirror` (download to `assets.disk`). `cdn` is the lighter default.
- `selectors.*` — every CSS selector the parsers use, so markup changes on Honda's site can be fixed here without a package release.

### A known caveat: ride-away pricing

Honda's ride-away/"Ready To Ride" pricing is hydrated **client-side** via a separate pricing API (`honda-rideawayprice-v1-prd-syd-funapp.azurewebsites.net`) — it is essentially never present in the static HTML this package fetches. `price_from` / `price_label` will commonly be `null`. This is expected, not a bug; the `selectors.model_page.price` selector is kept configurable in case a future template renders price server-side, but don't expect it to populate for most models today.

## Usage

```bash
# See what would happen without writing anything
php artisan honda-catalog:sync --dry-run

# Sync everything, mirroring assets per the configured strategy
php artisan honda-catalog:sync --with-assets

# Sync one model by slug
php artisan honda-catalog:sync --model=crf450r

# Re-ingest even if content hasn't changed
php artisan honda-catalog:sync --force

# Backfill/mirror any assets currently stored as cdn references
php artisan honda-catalog:assets:mirror
```

Without `--with-assets`, images are always recorded in `cdn` mode regardless of config, so a plain sync never downloads files.

### Scheduling

honda-catalog does **not** register a schedule for you. Add this to your own `routes/console.php`:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('honda-catalog:sync --with-assets')->daily();
```

## Data model

| Table | Purpose |
|---|---|
| `honda_models` | One row per model page, keyed by `slug`. Carries `content_hash` — a sync is skipped if this is unchanged and `--force` isn't set. |
| `honda_model_features` | Ordered feature blocks (heading/body/image) from the model page. |
| `honda_variants` | Named variants (e.g. engine/trim options) parsed from the pricing configurator. |
| `honda_specifications` | Section/category/label/value rows from the specs table, optionally linked to a `honda_variants` row when the spec differs per variant. |
| `honda_colours` | Colour swatches with hex and image. |
| `honda_assets` | Deduplicated asset records (by `guid`) shared across models — `$asset->url()` returns the right URL for either strategy. |
| `honda_model_asset` | Pivot linking models to assets with a `role` (`hero`/`gallery`/`feature`/`colour`) and `sort`. |

Assets from `delivery.contenthub.honda.com.au` keep their real Content Hub GUID. Assets from `motorcycles.honda.com.au/-/media/...` (Sitecore) don't carry an explicit GUID, so one is synthesized as a SHA-1 of the normalized path — treat `honda_assets.guid` as "our stable identifier," not literally always Honda's own GUID.

## Error handling

Markup drift or a fetch failure for a single model is logged and that model is skipped — it never fails the whole sync run. A failed asset mirror download falls back to `status = failed` and `$asset->url()` transparently serves the remote URL instead.

## Testing

The package has its own Testbench-based test suite, independent of any host app:

```bash
cd packages/honda-catalog
composer install
vendor/bin/phpunit
```

Parser tests run against real (trimmed) HTML fixtures captured from live Honda AU model pages — see `tests/fixtures/`.
