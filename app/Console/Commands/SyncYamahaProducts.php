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

    /**
     * The old api.yamaha-motor.com.au host is sunset — product endpoints return
     * stale/dead image links and GetPromotions returns an empty array. Everything
     * now comes from the Yamaha Dealers-API production gateway, which mirrors the
     * same endpoint shapes (one rename: GetProductFeaturesByID -> GetProductFeatures)
     * under camelCase keys, and serves images from Yamaha's Scene7 CDN instead of
     * their own dead one.
     */
    private function dealersApi(): \Illuminate\Http\Client\PendingRequest
    {
        $config = config('services.yamaha_dealers');

        return Http::withBasicAuth($config['username'], $config['password'])
            ->withHeaders(['X-API-Key' => $config['api_key']]);
    }

    private function dealersUrl(string $path): string
    {
        return config('services.yamaha_dealers.base_url') . '/' . $path;
    }

    public function handle(): int
    {
        $country = $this->option('country');

        $this->info("Starting Yamaha product sync for {$country}...");

        $this->setProgress([
            'status'  => 'running',
            'phase'   => 'starting',
            'current' => 0,
            'total'   => 0,
            'started' => now()->toIso8601String(),
        ]);

        try {
            $this->syncProducts($country);
            $this->syncPromotions($country);
            $this->syncNews($country);
        } catch (\Throwable $e) {
            Log::error('Yamaha sync: aborted with unhandled exception', ['error' => $e->getMessage()]);

            $this->setProgress([
                'status'  => 'failed',
                'phase'   => 'failed',
                'current' => 0,
                'total'   => 0,
                'error'   => $e->getMessage(),
            ]);

            $this->error('Sync failed: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->setProgress([
            'status'  => 'done',
            'phase'   => 'done',
            'current' => 0,
            'total'   => 0,
            'started' => null,
        ], now()->addMinutes(10));

        $this->info('Sync complete.');

        return self::SUCCESS;
    }

    /**
     * Every write is stamped with 'updated' so the admin UI can detect a sync
     * that died without ever reaching a terminal state (e.g. killed by the OS)
     * and stop showing an indefinite spinner.
     */
    private function setProgress(array $data, $ttl = null): void
    {
        Cache::put('yamaha_sync_progress', array_merge($data, [
            'updated' => now()->toIso8601String(),
        ]), $ttl ?? now()->addHours(2));
    }

    private function syncProducts(string $country): void
    {
        $this->info('Fetching product summaries...');

        $response = $this->dealersApi()->timeout(60)->get($this->dealersUrl("GetAllProductSummaries/{$country}"));

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

        $started = Cache::get('yamaha_sync_progress')['started'] ?? now()->toIso8601String();

        $this->setProgress([
            'status'  => 'running',
            'phase'   => 'products',
            'current' => 0,
            'total'   => $total,
            'started' => $started,
        ]);

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($products as $i => $summary) {
            $this->syncSingleProduct($summary, $country);
            $bar->advance();

            $this->setProgress([
                'status'  => 'running',
                'phase'   => 'products',
                'current' => $i + 1,
                'total'   => $total,
            ]);

            // Yamaha's API sits behind Imperva bot-protection; a brief pause between
            // products (each of which fires several requests) reduces the odds of
            // tripping it.
            usleep(100_000);
        }

        $bar->finish();
        $this->newLine();
    }

    private function syncSingleProduct(array $summary, string $country): void
    {
        $id = $summary['id'] ?? null;

        if (! $id) {
            return;
        }

        try {
            // Fetch full product detail (includes specs and pricing)
            $detail = $this->dealersApi()->timeout(30)
                ->get($this->dealersUrl("GetProductByID/{$id}"))
                ->json();

            $price = null;
            $priceNz = null;
            $longDescription = null;
            $productSpec = null;

            if ($detail) {
                $price = isset($detail['recommendedRetail']) ? (float) $detail['recommendedRetail'] : null;
                $priceNz = isset($detail['recommendedRetailNZ']) ? (float) $detail['recommendedRetailNZ'] : null;
                $longDescription = $detail['longDescription'] ?? null;
                $productSpec     = $detail['productSpec'] ?? null;
            }

            // Fetch brochure URL (204/empty body when a product has none)
            $brochureUrl = $this->dealersApi()->timeout(15)
                ->get($this->dealersUrl("GetBrochureByProductId/{$id}"))
                ->body();
            $brochureUrl = trim($brochureUrl, '"');

            // Upsert the product
            YamahaProduct::updateOrCreate(
                ['id' => $id],
                [
                    'model_name'          => $summary['modelName'] ?? null,
                    'product_type'        => $summary['productType'] ?? null,
                    'year_model'          => $summary['yearModel'] ?? null,
                    'division'            => $summary['division'] ?? null,
                    'product_group'       => $summary['productGroup'] ?? null,
                    'sub_category'        => $summary['subCategory'] ?? null,
                    'primary_category'    => $summary['primaryCategory'] ?? null,
                    'item_description'    => $summary['itemDescription'] ?? null,
                    'description'         => $summary['description'] ?? null,
                    'long_description'    => $longDescription,
                    'summary_image'       => isset($summary['summaryImage']) ? trim($summary['summaryImage']) : null,
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
        $response = $this->dealersApi()->timeout(15)->get($this->dealersUrl("GetProductBanners/{$id}"));

        // A successful response with no usable body means "this product genuinely
        // has none" — clear stale rows so the page omits the section instead of
        // showing a leftover broken image. Only an unsuccessful request (network
        // error, non-2xx) should leave existing data untouched.
        if (! $response->successful()) {
            return;
        }

        YamahaBanner::where('product_id', $id)->delete();

        $banners = $response->json();

        if (! is_array($banners)) {
            return;
        }

        foreach ($banners as $banner) {
            $variants = $this->parseImageOptions($banner['imageOptions'] ?? null);

            YamahaBanner::create([
                'id'            => $banner['id'],
                'product_id'    => $id,
                'image'         => $banner['image'] ?? null,
                'image_mobile'  => $variants['mobile'],
                'image_tablet'  => $variants['tablet'],
                'image_options' => $banner['imageOptions'] ?? null,
                'image_type'    => $banner['imageType'] ?? null,
                'active'        => $banner['active'] ?? true,
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
        $response = $this->dealersApi()->timeout(15)->get($this->dealersUrl("GetProductColors/{$id}"));

        if (! $response->successful()) {
            return;
        }

        YamahaColor::where('product_id', $id)->delete();

        $colors = $response->json();

        if (! is_array($colors)) {
            return;
        }

        foreach ($colors as $color) {
            YamahaColor::create([
                'product_id'  => $id,
                'color_name'  => $color['colorName'] ?? null,
                'color_code'  => $color['colorCode'] ?? null,
                'color_image' => $color['colorImage'] ?? null,
            ]);
        }
    }

    private function syncFeatures(int $id): void
    {
        // Renamed from GetProductFeaturesByID on the old (dead) API.
        $response = $this->dealersApi()->timeout(15)->get($this->dealersUrl("GetProductFeatures/{$id}"));

        if (! $response->successful()) {
            return;
        }

        YamahaFeature::where('product_id', $id)->delete();

        $features = $response->json();

        if (! is_array($features)) {
            return;
        }

        foreach ($features as $feature) {
            YamahaFeature::create([
                'product_id'  => $id,
                'title'       => $feature['title'] ?? null,
                'type'        => $feature['type'] ?? null,
                'description' => $feature['description'] ?? null,
                'image'       => $feature['image'] ?? null,
            ]);
        }
    }

    private function syncImages(int $id): void
    {
        $response = $this->dealersApi()->timeout(15)->get($this->dealersUrl("GetOverviewImages/{$id}"));

        if (! $response->successful()) {
            return;
        }

        YamahaImage::where('product_id', $id)->delete();

        $images = $response->json();

        if (! is_array($images)) {
            return;
        }

        foreach ($images as $url) {
            // The API sometimes serialises a missing image as the literal string
            // "null" rather than JSON null.
            $url = is_string($url) ? trim($url) : null;

            if ($url && strtolower($url) !== 'null') {
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

        $this->setProgress([
            'status'  => 'running',
            'phase'   => 'promotions',
            'current' => 0,
            'total'   => 0,
        ]);

        try {
            $response = $this->dealersApi()->timeout(30)->get($this->dealersUrl("GetPromotions/{$country}"));

            if (! $response->successful()) {
                $this->error('Failed to fetch promotions.');
                Log::error('Yamaha sync: failed to fetch promotions', ['status' => $response->status()]);
                return;
            }

            $promotions = $response->json();

            if (! is_array($promotions)) {
                return;
            }

            YamahaPromotion::where('country', $country)->delete();

            foreach ($promotions as $index => $promo) {
                $type = trim((string) ($promo['type'] ?? ''));

                YamahaPromotion::create([
                    'id'               => $promo['id'],
                    'head'             => HtmlEntityDecoder::decode($promo['head'] ?? null),
                    'brief'            => HtmlEntityDecoder::decode($promo['brief'] ?? null),
                    'brief_image'      => isset($promo['briefImage']) ? trim($promo['briefImage']) : null,
                    'full_content_url' => $promo['fullContentUrl'] ?? null,
                    'content'          => HtmlEntityDecoder::decode($promo['content'] ?? null),
                    'image'            => isset($promo['image']) ? trim($promo['image']) : null,
                    'type'             => $type !== '' ? $type : null,
                    // The API returns this space-padded; trust our own request param
                    // instead, or the delete-before-reinsert above silently stops
                    // matching existing rows on the next run and crashes on a
                    // duplicate primary key.
                    'country'          => $country,
                    'active'           => $promo['active'] ?? true,
                    'sort_index'       => $index,
                ]);
            }

            $this->info('Promotions synced: ' . count($promotions));
        } catch (\Exception $e) {
            Log::error('Yamaha sync: failed to sync promotions', ['error' => $e->getMessage()]);
            $this->error('Failed to sync promotions: ' . $e->getMessage());
        }
    }

    private function syncNews(string $country): void
    {
        $this->info('Fetching news & events...');

        $this->setProgress([
            'status'  => 'running',
            'phase'   => 'news',
            'current' => 0,
            'total'   => 0,
        ]);

        try {
            $response = $this->dealersApi()->timeout(30)->get($this->dealersUrl("GetNews/{$country}"));

            if (! $response->successful()) {
                $this->error('Failed to fetch news.');
                Log::error('Yamaha sync: failed to fetch news', ['status' => $response->status()]);
                return;
            }

            $items = $response->json();

            if (! is_array($items)) {
                return;
            }

            YamahaNews::where('country', $country)->delete();

            foreach ($items as $index => $item) {
                YamahaNews::create([
                    'id'               => $item['id'],
                    'head'             => HtmlEntityDecoder::decode($item['head'] ?? null),
                    'brief'            => HtmlEntityDecoder::decode($item['brief'] ?? null),
                    'brief_image'      => isset($item['briefImage']) ? trim($item['briefImage']) : null,
                    'full_content_url' => $item['fullContentUrl'] ?? null,
                    'content'          => HtmlEntityDecoder::decode($item['content'] ?? null),
                    'image'            => isset($item['image']) ? trim($item['image']) : null,
                    'image_options'    => $item['imageOptions'] ?? null,
                    'type'             => null,
                    'other_types'      => $item['otherTypes'] ?? null,
                    'country'          => $country,
                    'active'           => $item['active'] ?? true,
                    'sort_index'       => $index,
                ]);
            }

            $this->info('News & events synced: ' . count($items));
        } catch (\Exception $e) {
            Log::error('Yamaha sync: failed to sync news', ['error' => $e->getMessage()]);
            $this->error('Failed to sync news: ' . $e->getMessage());
        }
    }
}
