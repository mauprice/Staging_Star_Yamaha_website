<?php

namespace Honda\Catalog\Tests\Feature;

use Honda\Catalog\Enums\AssetHost;
use Honda\Catalog\Parsing\ModelPageParser;
use Honda\Catalog\Tests\TestCase;

class ModelPageParserTest extends TestCase
{
    private function parser(): ModelPageParser
    {
        return new ModelPageParser(config('honda-catalog.selectors.model_page'));
    }

    public function test_it_parses_name_tagline_and_og_image(): void
    {
        $data = $this->parser()->parse(
            $this->fixture('model-page-crf450r.html'),
            'https://motorcycles.honda.com.au/models/offroad/competition/crf450r',
        );

        $this->assertSame('crf450r', $data->slug);
        $this->assertSame('offroad', $data->category);
        $this->assertSame('competition', $data->subcategory);
        $this->assertSame('CRF450R', $data->name);
        $this->assertSame('Get 1% p.a comparison rate finance*', $data->tagline);
        $this->assertNotNull($data->ogImage);
        $this->assertSame('41e391dda38740e5bc616e1292a76e5b', $data->ogImage->guid);
        $this->assertSame('a9767838', $data->ogImage->versionHash);
        $this->assertSame(AssetHost::ContentHub, $data->ogImage->host);
    }

    public function test_price_is_null_when_ride_away_pricing_is_js_hydrated(): void
    {
        // Honda's real model pages hydrate ride-away pricing client-side via
        // a separate pricing API; the static HTML the scraper fetches never
        // contains .showroom-product-price text. This must degrade to a
        // null price, not throw.
        $data = $this->parser()->parse(
            $this->fixture('model-page-crf450r.html'),
            'https://motorcycles.honda.com.au/models/offroad/competition/crf450r',
        );

        $this->assertNull($data->priceFromCents);
    }

    public function test_it_parses_multiple_feature_blocks_in_order(): void
    {
        $data = $this->parser()->parse(
            $this->fixture('model-page-crf450r.html'),
            'https://motorcycles.honda.com.au/models/offroad/competition/crf450r',
        );

        $this->assertCount(2, $data->features);
        $this->assertSame('Hinson clutch basket & cover', $data->features[0]->heading);
        $this->assertStringContainsString('Hinson clutch basket', $data->features[0]->body);
        $this->assertNotNull($data->features[0]->image);
        $this->assertSame('Yoshimura Exhaust', $data->features[1]->heading);
    }

    public function test_it_parses_multiple_variants_by_data_variant_attribute(): void
    {
        $data = $this->parser()->parse(
            $this->fixture('model-page-crf450r.html'),
            'https://motorcycles.honda.com.au/models/offroad/competition/crf450r',
        );

        $this->assertCount(2, $data->variants);
        $names = array_map(fn ($v) => $v->name, $data->variants);
        $this->assertSame(['CRF450R', 'CRF450RWE'], $names);
    }

    public function test_variant_price_falls_back_to_its_first_colour_swatch_override(): void
    {
        // data-current-subvariant-price-override is empty in real markup;
        // the real static price lives on the nested colour swatch instead.
        $data = $this->parser()->parse(
            $this->fixture('model-page-crf450r.html'),
            'https://motorcycles.honda.com.au/models/offroad/competition/crf450r',
        );

        $this->assertSame(1369900, $data->variants[0]->priceCents);
        $this->assertSame(1549900, $data->variants[1]->priceCents);
    }

    public function test_it_parses_the_pricing_model_id_used_by_the_real_pricing_api(): void
    {
        $data = $this->parser()->parse(
            $this->fixture('model-page-crf450r.html'),
            'https://motorcycles.honda.com.au/models/offroad/competition/crf450r',
        );

        $this->assertSame('{097E0CC1-5601-4C4D-845C-FC56CABAF8CC}', $data->pricingModelId);
    }

    public function test_it_parses_colours_with_hex_and_image(): void
    {
        $data = $this->parser()->parse(
            $this->fixture('model-page-crf450r.html'),
            'https://motorcycles.honda.com.au/models/offroad/competition/crf450r',
        );

        $this->assertCount(2, $data->colours);
        $this->assertSame('Extreme Red', $data->colours[0]->name);
        $this->assertSame('#cc0000', $data->colours[0]->hex);
        $this->assertNotNull($data->colours[0]->image);
    }

    public function test_it_parses_gallery_images_from_both_asset_hosts(): void
    {
        $data = $this->parser()->parse(
            $this->fixture('model-page-crf450r.html'),
            'https://motorcycles.honda.com.au/models/offroad/competition/crf450r',
        );

        $this->assertCount(2, $data->galleryImages);
        $this->assertSame(AssetHost::ContentHub, $data->galleryImages[0]->host);
        $this->assertSame(AssetHost::Sitecore, $data->galleryImages[1]->host);
        // Sitecore media paths have no real GUID - must synthesize a stable one.
        $this->assertNotEmpty($data->galleryImages[1]->guid);
    }

    public function test_it_degrades_gracefully_on_markup_drift(): void
    {
        $data = $this->parser()->parse(
            $this->fixture('model-page-markup-drift.html'),
            'https://motorcycles.honda.com.au/models/offroad/competition/crf450r',
        );

        // Missing h1 falls back to slug rather than throwing.
        $this->assertSame('crf450r', $data->name);
        $this->assertNull($data->tagline);
        $this->assertNull($data->ogImage);
        $this->assertSame([], $data->features);
        $this->assertSame([], $data->variants);
        $this->assertSame([], $data->galleryImages);
    }

    public function test_name_falls_back_to_og_title_when_h1_is_absent(): void
    {
        // Mirrors Honda's real "Expression of Interest" pre-launch template,
        // which has no <h1> at all but does carry a real title via og:title.
        $html = '<html><head><meta property="og:title" content="2027 CRF450R - Register your interest" /></head><body></body></html>';

        $data = $this->parser()->parse($html, 'https://motorcycles.honda.com.au/models/offroad/competition/crf450r-eoi');

        $this->assertSame('2027 CRF450R - Register your interest', $data->name);
    }

    public function test_name_falls_back_to_title_tag_when_h1_and_og_title_are_absent(): void
    {
        $html = '<html><head><title>2027 CRF450R - Register your interest</title></head><body></body></html>';

        $data = $this->parser()->parse($html, 'https://motorcycles.honda.com.au/models/offroad/competition/crf450r-eoi');

        $this->assertSame('2027 CRF450R - Register your interest', $data->name);
    }
}
