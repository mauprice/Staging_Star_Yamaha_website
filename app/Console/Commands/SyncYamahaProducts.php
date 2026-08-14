<?php

namespace App\Console\Commands;

use App\Models\YamahaBanner;
use App\Models\YamahaNews;
use App\Models\YamahaColor;
use App\Models\YamahaFeature;
use App\Models\YamahaImage;
use App\Models\YamahaProduct;
use App\Models\YamahaPromotion;
use App\Support\HtmlEntityDecoder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncYamahaProducts extends Command
{
    protected $signature = 'yamaha:sync {--country=AU : Country code (AU or NZ)}';

    protected $description = 'Sync all Yamaha product data from the Yamaha Motor API';

    private string $baseUrl = 'https://api.yamaha-motor.com.au/api/products';

    public function handle(): int
    {
        $country = $this->option('country');

        $this->info("Starting Yamaha product sync for {$country}...");

        Cache::put('yamaha_sync_progress', [
            'status'  => 'running',
            'phase'   => 'starting',
            'current' => 0,
            'total'   => 0,
            'started' => now()->toIso8601String(),
        ], now()->addHours(2));

        $this->syncProducts($country);
        $this->syncPromotions($country);
        $this->syncNews($country);

        Cache::put('yamaha_sync_progress', [
            'status'  => 'done',
            'phase'   => 'done',
            'current' => 0,
            'total'   => 0,
            'started' => null,
        ], now()->addMinutes(10));

        $this->info('Sync complete.');

        return self::SUCCESS;
    }

    private function syncProducts(string $country): void
    {
        $this->info('Fetching product summaries...');

        $response = Http::timeout(60)->get("{$this->baseUrl}/GetAllProductSummaries/{$country}");

        if (! $response->successful()) {
            $this->error('Failed to fetch product summaries.');
            Log::error('Yamaha sync: failed to fetch summaries', ['status' => $response->status()]);
            return;
        }

        $products = $response->json();

        if (empty($products)) {
            $this->warn('No products returned from API.');
            return;
        }

        $total = count($products);
        $this->info("Found {$total} products. Syncing details...");

        Cache::put('yamaha_sync_progress', [
            'status'  => 'running',
            'phase'   => 'products',
            'current' => 0,
            'total'   => $total,
            'started' => Cache::get('yamaha_sync_progress.started', now()->toIso8601String()),
        ], now()->addHours(2));

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($products as $i => $summary) {
            $this->syncSingleProduct($summary, $country);
            $bar->advance();

            Cache::put('yamaha_sync_progress', [
                'status'  => 'running',
                'phase'   => 'products',
                'current' => $i + 1,
                'total'   => $total,
            ], now()->addHours(2));
        }

        $bar->finish();
        $this->newLine();
    }

    private function syncSingleProduct(array $summary, string $country): void
    {
        $id = $summary['ID'] ?? null;

        if (! $id) {
            return;
        }

        try {
            // Fetch full product detail (includes specs and pricing)
            $detail = Http::timeout(30)
                ->get("{$this->baseUrl}/GetProductByID/{$id}")
                ->json();

            $price = null;
            $priceNz = null;
            $longDescription = null;
            $productSpec = null;

            if ($detail) {
                $price = isset($detail['RecommendedRetail'])
                    ? (float) preg_replace('/[^0-9.]/', '', $detail['RecommendedRetail'])
                    : null;

                $priceNz = isset($detail['RecommendedRetail_NZ'])
                    ? (float) preg_replace('/[^0-9.]/', '', $detail['RecommendedRetail_NZ'])
                    : null;

                $longDescription = $detail['LongDescription'] ?? null;
                $productSpec     = $detail['ProductSpec'] ?? null;
            }

            // Fetch brochure URL
            $brochureUrl = Http::timeout(15)
                ->get("{$this->baseUrl}/GetBrochureByProductId/{$id}")
                ->body();
            $brochureUrl = trim($brochureUrl, '"');

            // Upsert the product
            YamahaProduct::updateOrCreate(
                ['id' => $id],
                [
                    'model_name'          => $summary['ModelName'] ?? null,
                    'product_type'        => $summary['ProductType'] ?? null,
                    'year_model'          => $summary['YearModel'] ?? null,
                    'division'            => $summary['Division'] ?? null,
                    'product_group'       => $summary['ProductGroup'] ?? null,
                    'sub_category'        => $summary['SubCategory'] ?? null,
                    'primary_category'    => $summary['PrimaryCategory'] ?? null,
                    'item_description'    => $summary['ItemDescription'] ?? null,
                    'description'         => $summary['Description'] ?? null,
                    'long_description'    => $longDescription,
                    'summary_image'       => $summary['SummaryImage'] ?? null,
                    'recommended_retail'  => $price,
                    'recommended_retail_nz' => $priceNz,
                    'brochure_url'        => $brochureUrl ?: null,
                    'product_spec'        => $productSpec,
                    'synced_at'           => now(),
                ]
            );

            // Sync related data
            $this->syncBanners($id);
            $this->syncColors($id);
            $this->syncFeatures($id);
            $this->syncImages($id);

        } catch (\Exception $e) {
            Log::error("Yamaha sync: failed for product {$id}", ['error' => $e->getMessage()]);
        }
    }

    private function syncBanners(int $id): void
    {
        $banners = Http::timeout(15)
            ->get("{$this->baseUrl}/GetProductBanners/{$id}")
            ->json();

        if (! is_array($banners)) {
            return;
        }

        YamahaBanner::where('product_id', $id)->delete();

        foreach ($banners as $banner) {
            $variants = $this->parseImageOptions($banner['ImageOptions'] ?? null);

            YamahaBanner::create([
                'id'            => $banner['Id'],
                'product_id'    => $id,
                'image'         => $banner['Image'] ?? null,
                'image_mobile'  => $variants['mobile'],
                'image_tablet'  => $variants['tablet'],
                'image_options' => $banner['ImageOptions'] ?? null,
                'image_type'    => $banner['ImageType'] ?? null,
                'active'        => $banner['Active'] ?? true,
            ]);
        }
    }

    /**
     * The API returns ImageOptions as a pipe-delimited string of size variants, e.g.
     * "https://…/carousel-mobile-….ashx [--small] | https://…/carousel-tablet-….ashx [--medium] | https://…/carousel-desktop-….ashx"
     * The untagged final segment is the desktop crop, already captured separately as $banner['Image'].
     */
    private function parseImageOptions(?string $raw): array
    {
        $mobile = null;
        $tablet = null;

        if ($raw) {
            foreach (explode('|', $raw) as $segment) {
                $segment = trim($segment);

                if (preg_match('/^(\S+)\s*\[--(small|medium)\]$/', $segment, $m)) {
                    if ($m[2] === 'small') {
                        $mobile = $m[1];
                    } else {
                        $tablet = $m[1];
                    }
                }
            }
        }

        return ['mobile' => $mobile, 'tablet' => $tablet];
    }

    private function syncColors(int $id): void
    {
        $colors = Http::timeout(15)
            ->get("{$this->baseUrl}/GetProductColors/{$id}")
            ->json();

        if (! is_array($colors)) {
            return;
        }

        YamahaColor::where('product_id', $id)->delete();

        foreach ($colors as $color) {
            YamahaColor::create([
                'product_id'  => $id,
                'color_name'  => $color['ColorName'] ?? null,
                'color_code'  => $color['ColorCode'] ?? null,
                'color_image' => $color['ColorImage'] ?? null,
            ]);
        }
    }

    private function syncFeatures(int $id): void
    {
        $features = Http::timeout(15)
            ->get("{$this->baseUrl}/GetProductFeaturesByID/{$id}")
            ->json();

        if (! is_array($features)) {
            return;
        }

        YamahaFeature::where('product_id', $id)->delete();

        foreach ($features as $feature) {
            YamahaFeature::create([
                'product_id'  => $id,
                'title'       => $feature['Title'] ?? null,
                'type'        => $feature['Type'] ?? null,
                'description' => $feature['Description'] ?? null,
                'image'       => $feature['Image'] ?? null,
            ]);
        }
    }

    private function syncImages(int $id): void
    {
        $images = Http::timeout(15)
            ->get("{$this->baseUrl}/GetOverviewImages/{$id}")
            ->json();

        if (! is_array($images)) {
            return;
        }

        YamahaImage::where('product_id', $id)->delete();

        foreach ($images as $url) {
            if ($url) {
                YamahaImage::create([
                    'product_id' => $id,
                    'image_url'  => $url,
                ]);
            }
        }
    }

    private function syncPromotions(string $country): void
    {
        $this->info('Fetching promotions...');

        Cache::put('yamaha_sync_progress', [
            'status'  => 'running',
            'phase'   => 'promotions',
            'current' => 0,
            'total'   => 0,
        ], now()->addHours(2));

        $promotions = Http::timeout(30)
            ->get("{$this->baseUrl}/GetPromotions/{$country}")
            ->json();

        if (! is_array($promotions)) {
            return;
        }

        YamahaPromotion::where('country', $country)->delete();

        foreach ($promotions as $promo) {
            YamahaPromotion::create([
                'id'               => $promo['ID'],
                'head'             => HtmlEntityDecoder::decode($promo['Head'] ?? null),
                'brief'            => HtmlEntityDecoder::decode($promo['Brief'] ?? null),
                'brief_image'      => $promo['BriefImage'] ?? null,
                'full_content_url' => $promo['FullContentUrl'] ?? null,
                'content'          => HtmlEntityDecoder::decode($promo['Content'] ?? null),
                'image'            => $promo['Image'] ?? null,
                'type'             => $promo['Type'] ?? null,
                'country'          => $country,
                'active'           => $promo['Active'] ?? true,
                'sort_index'       => $promo['SortIndex'] ?? 0,
            ]);
        }

        $this->info('Promotions synced: ' . count($promotions));
    }

    private function syncNews(string $country): void
    {
        $this->info('Fetching news & events...');

        $items = Http::timeout(30)
            ->get("{$this->baseUrl}/GetNews/{$country}")
            ->json();

        if (! is_array($items)) {
            return;
        }

        YamahaNews::where('country', $country)->delete();

        foreach ($items as $item) {
            YamahaNews::create([
                'id'               => $item['ID'],
                'head'             => HtmlEntityDecoder::decode($item['Head'] ?? null),
                'brief'            => HtmlEntityDecoder::decode($item['Brief'] ?? null),
                'brief_image'      => isset($item['BriefImage']) ? trim($item['BriefImage']) : null,
                'full_content_url' => $item['FullContentUrl'] ?? null,
                'content'          => HtmlEntityDecoder::decode($item['Content'] ?? null),
                'image'            => isset($item['Image']) ? trim($item['Image']) : null,
                'image_options'    => $item['ImageOptions'] ?? null,
                'type'             => HtmlEntityDecoder::decode($item['Type'] ?? null),
                'other_types'      => $item['OtherTypes'] ?? null,
                'country'          => $country,
                'active'           => $item['Active'] ?? true,
                'sort_index'       => $item['SortIndex'] ?? 0,
            ]);
        }

        $this->info('News & events synced: ' . count($items));
    }
}
