<?php

namespace Honda\Catalog\Parsing;

use Honda\Catalog\DataTransferObjects\AssetRef;
use Honda\Catalog\DataTransferObjects\ColourData;
use Honda\Catalog\DataTransferObjects\FeatureBlock;
use Honda\Catalog\DataTransferObjects\ModelPageData;
use Honda\Catalog\DataTransferObjects\VariantData;
use Honda\Catalog\Enums\AssetHost;
use Honda\Catalog\Parsing\Support\PriceParser;
use Symfony\Component\DomCrawler\Crawler;

class ModelPageParser
{
    public function __construct(private readonly array $selectors = []) {}

    public function parse(string $html, string $sourceUrl): ModelPageData
    {
        $crawler = new Crawler($html);
        [$category, $subcategory, $slug] = $this->parsePath($sourceUrl);

        $name = $this->resolveName($crawler, $slug);
        $tagline = $this->text($crawler, 'tagline');
        $description = $this->text($crawler, 'description');
        $ogImage = $this->buildAssetRef($this->attr($crawler, 'og_image', 'content'));

        $priceRaw = $this->text($crawler, 'price');
        $price = PriceParser::toCents($priceRaw);
        $pricingModelId = $this->firstAttr($crawler, $this->selectors['pricing_model_id'] ?? '[pricing-model-id]', 'pricing-model-id');

        return new ModelPageData(
            slug: $slug,
            category: $category,
            subcategory: $subcategory,
            name: $name,
            tagline: $tagline,
            descriptionHtml: $description,
            priceFromCents: $price['cents'],
            priceCurrency: 'AUD',
            priceLabel: $price['label'],
            sourceUrl: $sourceUrl,
            ogImage: $ogImage,
            pricingModelId: $pricingModelId,
            features: $this->parseFeatures($crawler),
            variants: $this->parseVariants($crawler),
            colours: $this->parseColours($crawler),
            galleryImages: $this->parseGallery($crawler),
        );
    }

    /**
     * @return FeatureBlock[]
     */
    private function parseFeatures(Crawler $crawler): array
    {
        $selector = $this->selectors['feature_block'] ?? '.block--card';
        $features = [];

        $crawler->filter($selector)->each(function (Crawler $node, int $i) use (&$features) {
            $heading = $this->firstText($node, $this->selectors['feature_heading'] ?? 'h2');

            if ($heading === null) {
                return;
            }

            $body = $this->firstText($node, $this->selectors['feature_body'] ?? 'p');
            $imageUrl = $this->firstAttr($node, $this->selectors['feature_image'] ?? 'img', 'src');

            $features[] = new FeatureBlock(
                sort: $i,
                heading: $heading,
                body: $body,
                image: $this->buildAssetRef($imageUrl),
            );
        });

        return $features;
    }

    /**
     * @return VariantData[]
     */
    private function parseVariants(Crawler $crawler): array
    {
        $selector = $this->selectors['variant_item'] ?? '.pricing__configurator-item[data-variant]';
        $variants = [];

        $colourSelector = $this->selectors['colour_swatch'] ?? '[data-color-title]';

        $crawler->filter($selector)->each(function (Crawler $node, int $i) use (&$variants, $colourSelector) {
            $name = $node->attr('data-variant');

            if (! $name) {
                return;
            }

            // data-current-subvariant-price-override is nearly always empty in
            // the static HTML (it's populated client-side); the real static
            // price lives on the variant's first colour swatch instead.
            $priceRaw = $node->attr('data-current-subvariant-price-override');

            if (! $priceRaw) {
                $firstColour = $node->filter($colourSelector);
                $priceRaw = $firstColour->count() > 0 ? $firstColour->first()->attr('data-color-priceoverride') : null;
            }

            $price = PriceParser::toCents($priceRaw);

            $variants[] = new VariantData(
                name: $name,
                priceCents: $price['cents'],
                sort: $i,
            );
        });

        return $variants;
    }

    /**
     * @return ColourData[]
     */
    private function parseColours(Crawler $crawler): array
    {
        $selector = $this->selectors['colour_swatch'] ?? '[data-color-title]';
        $colours = [];

        $crawler->filter($selector)->each(function (Crawler $node, int $i) use (&$colours) {
            $name = $node->attr('data-color-title');

            if (! $name) {
                return;
            }

            $hex = $this->extractHex($node->attr('style'));
            $imageUrl = $node->attr('data-color-img');

            $colours[] = new ColourData(
                name: $name,
                hex: $hex,
                image: $this->buildAssetRef($imageUrl),
                sort: $i,
            );
        });

        return $colours;
    }

    /**
     * @return AssetRef[]
     */
    private function parseGallery(Crawler $crawler): array
    {
        $selector = $this->selectors['gallery_item'] ?? '.griditem[data-griditemtype="griditemImage"]';
        $images = [];

        $crawler->filter($selector)->each(function (Crawler $node) use (&$images) {
            $url = $node->filter('[data-backgroundurl]')->count() > 0
                ? $node->filter('[data-backgroundurl]')->first()->attr('data-backgroundurl')
                : null;

            $ref = $this->buildAssetRef($url);

            if ($ref !== null) {
                $images[] = $ref;
            }
        });

        return $images;
    }

    private function buildAssetRef(?string $url): ?AssetRef
    {
        $url = trim((string) $url);

        if ($url === '') {
            return null;
        }

        if (str_starts_with($url, '//')) {
            $url = 'https:'.$url;
        }

        $parts = parse_url($url);
        $host = $parts['host'] ?? '';
        $path = $parts['path'] ?? '';

        parse_str($parts['query'] ?? '', $query);
        $versionHash = $query['v'] ?? null;

        $cleanUrl = ($parts['scheme'] ?? 'https').'://'.$host.$path;

        if (str_contains($host, 'contenthub')) {
            preg_match('#/content/([a-f0-9-]+)#i', $path, $m);
            $guid = $m[1] ?? sha1($cleanUrl);
            $assetHost = AssetHost::ContentHub;
        } else {
            $guid = sha1($cleanUrl);
            $assetHost = AssetHost::Sitecore;
        }

        return new AssetRef($guid, $cleanUrl, $versionHash, $assetHost);
    }

    private function extractHex(?string $style): ?string
    {
        if (! $style) {
            return null;
        }

        if (preg_match('/background-color:\s*(#[0-9a-fA-F]{3,6})/', $style, $m)) {
            return $m[1];
        }

        return null;
    }

    /**
     * Falls back through og:title / <title> before giving up and using the
     * slug - some templates (e.g. Honda's pre-launch "Expression of
     * Interest" pages) have no <h1> at all but still carry a real title via
     * meta tags.
     */
    private function resolveName(Crawler $crawler, string $slug): string
    {
        $name = $this->text($crawler, 'name');

        if ($name !== null) {
            return $name;
        }

        $name = $this->firstAttr($crawler, 'meta[property="og:title"]', 'content');

        if ($name !== null && trim($name) !== '') {
            return trim($name);
        }

        $name = $this->firstText($crawler, 'title');

        if ($name !== null && trim($name) !== '') {
            return trim($name);
        }

        return $slug;
    }

    /**
     * @return array{0: string, 1: string, 2: string} [category, subcategory, slug]
     */
    private function parsePath(string $url): array
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        $segments = array_values(array_filter(explode('/', $path)));

        // Expect: models/{category}/{subcategory}/{slug}
        return [
            $segments[1] ?? 'unknown',
            $segments[2] ?? 'unknown',
            $segments[3] ?? sha1($url),
        ];
    }

    private function text(Crawler $crawler, string $key): ?string
    {
        $selector = $this->selectors[$key] ?? null;

        if (! $selector) {
            return null;
        }

        return $this->firstText($crawler, $selector);
    }

    private function attr(Crawler $crawler, string $key, string $attribute): ?string
    {
        $selector = $this->selectors[$key] ?? null;

        if (! $selector) {
            return null;
        }

        return $this->firstAttr($crawler, $selector, $attribute);
    }

    private function firstText(Crawler $crawler, string $selector): ?string
    {
        $node = $crawler->filter($selector);

        if ($node->count() === 0) {
            return null;
        }

        $text = trim($node->first()->text());

        return $text !== '' ? $text : null;
    }

    private function firstAttr(Crawler $crawler, string $selector, string $attribute): ?string
    {
        $node = $crawler->filter($selector);

        if ($node->count() === 0) {
            return null;
        }

        return $node->first()->attr($attribute);
    }
}
