<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Source site
    |--------------------------------------------------------------------------
    */
    'base_url' => env('HONDA_CATALOG_BASE_URL', 'https://motorcycles.honda.com.au'),
    'sitemap_url' => env('HONDA_CATALOG_SITEMAP_URL', 'https://motorcycles.honda.com.au/sitemap.xml'),

    /*
    |--------------------------------------------------------------------------
    | HTTP behaviour
    |--------------------------------------------------------------------------
    | requests_per_second controls the minimum delay between requests made by
    | ThrottledHttpClient. Keep this conservative and honest.
    */
    'http' => [
        'user_agent' => env(
            'HONDA_CATALOG_USER_AGENT',
            'HondaCatalogBot/1.0 (+https://github.com/honda/catalog; a dealer catalog sync tool)'
        ),
        'requests_per_second' => (float) env('HONDA_CATALOG_REQUESTS_PER_SECOND', 1.5),
        'timeout' => (int) env('HONDA_CATALOG_TIMEOUT', 15),
        'retry_times' => (int) env('HONDA_CATALOG_RETRY_TIMES', 3),
        'retry_backoff_base' => (float) env('HONDA_CATALOG_RETRY_BACKOFF_BASE', 2.0),
        'respect_robots_txt' => (bool) env('HONDA_CATALOG_RESPECT_ROBOTS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Discovery
    |--------------------------------------------------------------------------
    | model_url_pattern is applied first (structural filter: is this even a
    | model URL). category_allow_list is applied second, against the category
    | segment extracted from the path, and only narrows an already-valid set.
    | Leave category_allow_list empty to sync every category found.
    */
    'discovery' => [
        'model_url_pattern' => '#^/models/([a-z0-9]+)/([a-z0-9]+)/([a-z0-9-]+)$#',
        'category_allow_list' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Offers
    |--------------------------------------------------------------------------
    | entry_path is the single starting point for offer discovery - unlike
    | models (found via the sitemap), offer campaigns are just whatever
    | .block--card entries are on this page right now. Any of those cards
    | whose CTA links to another /offers/... page (e.g. a runout campaign's
    | own listing) is followed and ingested as that offer's children, so new
    | offer sub-pages are picked up automatically without a code change.
    */
    'offers' => [
        'entry_path' => env('HONDA_CATALOG_OFFERS_ENTRY_PATH', '/offers'),
        'child_page_pattern' => '#^/offers/#',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pricing
    |--------------------------------------------------------------------------
    | Ride-away pricing is not present as text anywhere in the static HTML -
    | Honda's own frontend fetches it client-side from this same-origin JSON
    | API, keyed by the `pricing-model-id` GUID that IS present statically on
    | the model page (see selectors.model_page.pricing_model_id below). This
    | is a same-origin endpoint on motorcycles.honda.com.au itself, not a
    | third-party service. Set enabled=false to skip the extra request per
    | model and leave price_from/price_label null.
    */
    'pricing' => [
        'enabled' => (bool) env('HONDA_CATALOG_PRICING_ENABLED', true),
        'endpoint' => env('HONDA_CATALOG_PRICING_ENDPOINT', '/api/MpePcm/GetMpePcmProduct'),
        'default_label' => env('HONDA_CATALOG_PRICE_LABEL', 'Ride away from'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Asset strategy
    |--------------------------------------------------------------------------
    | 'cdn'    - store the Content Hub / Sitecore URL and serve it directly.
    | 'mirror' - download assets to the configured disk.
    */
    'assets' => [
        'strategy' => env('HONDA_CATALOG_ASSET_STRATEGY', 'cdn'), // cdn|mirror
        'disk' => env('HONDA_CATALOG_ASSET_DISK', 'public'),
        'path_prefix' => env('HONDA_CATALOG_ASSET_PATH_PREFIX', 'honda-catalog'),
    ],

    /*
    |--------------------------------------------------------------------------
    | CSS / DOM selectors
    |--------------------------------------------------------------------------
    | Kept here (not hardcoded in the parsers) so they can be updated without
    | a package release if Honda's markup changes. Verified against live
    | markup during development; see README for known caveats (notably: the
    | `price` selector below almost never matches anything directly - it's
    | kept as a fallback in case a future template renders it server-side.
    | The real price comes from the pricing API described above, keyed off
    | pricing_model_id).
    */
    'selectors' => [
        'model_page' => [
            'name' => 'h1.field-herotitle, h1',
            'tagline' => 'h3.field-herosubtitle',
            'description' => '.block__body-text .cmp-text, .field-blockcontent',
            'price' => '.showroom-product-price',
            'pricing_model_id' => '[pricing-model-id]',
            'og_image' => 'meta[property="og:image"]',
            'feature_block' => '.block--card',
            'feature_heading' => '.block__heading h2, h2.field-blocktitle',
            'feature_body' => '.block__body-text .field-blockcontent, .block__body-text p',
            'feature_image' => '.block__image-link img, .block__image-link image',
            'variant_item' => '.pricing__configurator-item[data-variant]',
            'colour_swatch' => 'button.pricing__configurator-item-color, .pricing__configurator-item-color[data-color-title]',
            'gallery_item' => '.griditem[data-griditemtype="griditemImage"]',
        ],
        'specs_page' => [
            'table' => 'table.specsTable__data',
            'variant_titles_row' => '.spec--variantTitles',
            'heading' => '.specsTable__heading',
            'category_name' => '.specsTable__category-name',
            'subcategory_name' => '.specsTable__subcategory-name',
            'subcategory_content' => '.specsTable__subcategory-content',
        ],
        'offer_page' => [
            'block' => '.block.block--card',
            'title' => 'h2.field-blocktitle',
            'subtitle' => '.field-blocksubtitle',
            'price' => '.sale-price',
            // Honda's markup nests <p class="field-blockcontent"><p>...</p></p> -
            // browsers (and DomCrawler) auto-close the outer <p> on the inner
            // one per HTML5 rules, leaving .field-blockcontent empty. .cmp-text
            // is the real wrapping div and isn't affected, same workaround as
            // model_page.description above.
            'body' => '.block__body-text .cmp-text, .field-blockcontent',
            'image' => '.block__image-link image, .block__image-link img',
            'cta' => '.ctasBlock__item--cta',
        ],
    ],

];
