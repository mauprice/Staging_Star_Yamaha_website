<?php

namespace Honda\Catalog\Tests\Unit;

use Honda\Catalog\DataTransferObjects\AssetRef;
use Honda\Catalog\DataTransferObjects\ModelPageData;
use Honda\Catalog\DataTransferObjects\SpecsPageData;
use Honda\Catalog\Enums\AssetHost;
use Honda\Catalog\Support\ContentHash;
use PHPUnit\Framework\TestCase;

class ContentHashTest extends TestCase
{
    private function model(?AssetRef $ogImage = null): ModelPageData
    {
        return new ModelPageData(
            slug: 'crf450r',
            category: 'offroad',
            subcategory: 'competition',
            name: 'CRF450R',
            tagline: 'tagline',
            descriptionHtml: null,
            priceFromCents: null,
            priceCurrency: 'AUD',
            priceLabel: null,
            sourceUrl: 'https://example.test/models/offroad/competition/crf450r',
            ogImage: $ogImage,
            pricingModelId: null,
            features: [],
            variants: [],
            colours: [],
            galleryImages: [],
        );
    }

    private function specs(): SpecsPageData
    {
        return new SpecsPageData('crf450r', 'https://example.test/specs', [], []);
    }

    public function test_identical_content_produces_identical_hash(): void
    {
        $a = ContentHash::compute($this->model(), $this->specs());
        $b = ContentHash::compute($this->model(), $this->specs());

        $this->assertSame($a, $b);
    }

    public function test_source_url_does_not_affect_the_hash(): void
    {
        $modelA = $this->model();
        $modelB = new ModelPageData(
            slug: 'crf450r',
            category: 'offroad',
            subcategory: 'competition',
            name: 'CRF450R',
            tagline: 'tagline',
            descriptionHtml: null,
            priceFromCents: null,
            priceCurrency: 'AUD',
            priceLabel: null,
            sourceUrl: 'https://example.test/some/other/path/entirely',
            ogImage: null,
            pricingModelId: null,
            features: [],
            variants: [],
            colours: [],
            galleryImages: [],
        );

        $this->assertSame(
            ContentHash::compute($modelA, $this->specs()),
            ContentHash::compute($modelB, $this->specs()),
        );
    }

    public function test_asset_version_hash_change_changes_the_hash(): void
    {
        $refV1 = new AssetRef('guid-1', 'https://cdn.test/img.jpg', 'v1', AssetHost::ContentHub);
        $refV2 = new AssetRef('guid-1', 'https://cdn.test/img.jpg', 'v2', AssetHost::ContentHub);

        $hashV1 = ContentHash::compute($this->model($refV1), $this->specs());
        $hashV2 = ContentHash::compute($this->model($refV2), $this->specs());

        $this->assertNotSame($hashV1, $hashV2, 'a bumped asset version hash must change the content hash so image-only updates still trigger re-sync');
    }
}
