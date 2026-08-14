<?php

namespace Honda\Catalog\Tests\Feature;

use Honda\Catalog\Enums\AssetHost;
use Honda\Catalog\Enums\AssetRole;
use Honda\Catalog\Enums\AssetStatus;
use Honda\Catalog\Models\HondaAsset;
use Honda\Catalog\Models\HondaColour;
use Honda\Catalog\Models\HondaModel;
use Honda\Catalog\Models\HondaModelFeature;
use Honda\Catalog\Models\HondaSpecification;
use Honda\Catalog\Models\HondaVariant;
use Honda\Catalog\Tests\TestCase;

class ModelRelationshipsTest extends TestCase
{
    private function makeModel(): HondaModel
    {
        return HondaModel::create([
            'slug' => 'crf450r',
            'category' => 'offroad',
            'subcategory' => 'competition',
            'name' => 'CRF450R',
            'source_url' => 'https://motorcycles.honda.com.au/models/offroad/competition/crf450r',
        ]);
    }

    public function test_model_has_many_features_variants_specs_and_colours(): void
    {
        $model = $this->makeModel();

        HondaModelFeature::create(['model_id' => $model->id, 'sort' => 0, 'heading' => 'Feature 1']);
        $variant = HondaVariant::create(['model_id' => $model->id, 'name' => 'CRF450R', 'sort' => 0]);
        HondaSpecification::create([
            'model_id' => $model->id, 'variant_id' => $variant->id,
            'section' => 'Chassis', 'category' => 'Brakes', 'label' => 'Brakes (F)', 'value' => '1x 260mm disc', 'sort' => 0,
        ]);
        HondaColour::create(['model_id' => $model->id, 'name' => 'Extreme Red', 'hex' => '#cc0000', 'sort' => 0]);

        $this->assertCount(1, $model->features);
        $this->assertCount(1, $model->variants);
        $this->assertCount(1, $model->specifications);
        $this->assertCount(1, $model->colours);
        $this->assertSame('CRF450R', $model->variants->first()->name);
        $this->assertSame($variant->id, $model->specifications->first()->variant_id);
    }

    public function test_model_belongs_to_many_assets_via_pivot_with_role_and_sort(): void
    {
        $model = $this->makeModel();
        $asset = HondaAsset::create([
            'guid' => 'guid-1',
            'source_url' => 'https://delivery.contenthub.honda.com.au/api/public/content/guid-1',
            'host' => AssetHost::ContentHub,
            'status' => AssetStatus::Remote,
        ]);

        $model->assets()->attach($asset->id, ['role' => AssetRole::Gallery->value, 'sort' => 3]);

        $pivotAsset = $model->assets()->first();

        $this->assertSame('guid-1', $pivotAsset->guid);
        $this->assertSame(AssetRole::Gallery->value, $pivotAsset->pivot->role);
        $this->assertSame(3, $pivotAsset->pivot->sort);
    }

    public function test_deleting_a_model_cascades_to_children(): void
    {
        $model = $this->makeModel();
        HondaModelFeature::create(['model_id' => $model->id, 'sort' => 0, 'heading' => 'Feature 1']);
        HondaVariant::create(['model_id' => $model->id, 'name' => 'CRF450R', 'sort' => 0]);
        HondaColour::create(['model_id' => $model->id, 'name' => 'Extreme Red', 'sort' => 0]);

        $model->delete();

        $this->assertDatabaseCount('honda_model_features', 0);
        $this->assertDatabaseCount('honda_variants', 0);
        $this->assertDatabaseCount('honda_colours', 0);
    }

    public function test_formatted_price_divides_cents_by_100_not_a_string_trim(): void
    {
        // 549900 cents -> "$5,499.00" via division, not by trimming
        // trailing zeros - those only coincidentally look the same for
        // whole-dollar amounts. A price with real cents proves the point:
        // 1234 cents is $12.34, not $12 (dropping the last two digits).
        $whole = HondaModel::create([
            'slug' => 'whole-dollar', 'category' => 'onroad', 'subcategory' => 'x',
            'name' => 'Whole', 'source_url' => 'https://example.test/whole', 'price_from' => 549900,
        ]);
        $withCents = HondaModel::create([
            'slug' => 'with-cents', 'category' => 'onroad', 'subcategory' => 'x',
            'name' => 'WithCents', 'source_url' => 'https://example.test/cents', 'price_from' => 1234,
        ]);
        $noPrice = HondaModel::create([
            'slug' => 'no-price', 'category' => 'onroad', 'subcategory' => 'x',
            'name' => 'NoPrice', 'source_url' => 'https://example.test/none',
        ]);

        $this->assertSame('$5,499.00', $whole->formatted_price);
        $this->assertSame('$12.34', $withCents->formatted_price);
        $this->assertNull($noPrice->formatted_price);
    }

    public function test_variant_formatted_price_divides_cents_by_100(): void
    {
        $model = $this->makeModel();
        $variant = HondaVariant::create(['model_id' => $model->id, 'name' => 'CRF450RWE', 'price' => 1799900, 'sort' => 0]);

        $this->assertSame('$17,999.00', $variant->formatted_price);
    }

    public function test_asset_status_and_host_are_cast_to_enums(): void
    {
        $asset = HondaAsset::create([
            'guid' => 'guid-2',
            'source_url' => 'https://motorcycles.honda.com.au/-/media/x.jpg',
            'host' => AssetHost::Sitecore,
            'status' => AssetStatus::Mirrored,
        ]);

        $fresh = HondaAsset::find($asset->id);

        $this->assertInstanceOf(AssetHost::class, $fresh->host);
        $this->assertInstanceOf(AssetStatus::class, $fresh->status);
        $this->assertSame(AssetHost::Sitecore, $fresh->host);
    }
}
