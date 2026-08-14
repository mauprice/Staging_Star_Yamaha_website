<?php

namespace Honda\Catalog\Services;

use GuzzleHttp\Exception\RequestException;
use Honda\Catalog\Assets\AssetManager;
use Honda\Catalog\DataTransferObjects\ModelPageData;
use Honda\Catalog\DataTransferObjects\SpecsPageData;
use Honda\Catalog\Enums\AssetRole;
use Honda\Catalog\Http\ThrottledHttpClient;
use Honda\Catalog\Models\HondaModel;
use Honda\Catalog\Parsing\ModelPageParser;
use Honda\Catalog\Parsing\SpecsPageParser;
use Honda\Catalog\Pricing\MpePcmPricingClient;
use Honda\Catalog\Support\ContentHash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class IngestService
{
    public function __construct(
        private readonly ThrottledHttpClient $http,
        private readonly ModelPageParser $modelParser,
        private readonly SpecsPageParser $specsParser,
        private readonly AssetManager $assets,
        private readonly MpePcmPricingClient $pricing,
        private readonly array $config = [],
    ) {}

    /**
     * Fetches, parses, and upserts a single model. Returns null (logging
     * the failure) rather than throwing, so one bad model doesn't fail the
     * whole sitemap run.
     */
    public function ingest(string $modelUrl, bool $withAssets = false, bool $force = false): ?HondaModel
    {
        try {
            $modelHtml = (string) $this->http->get($modelUrl)->getBody();
            $modelData = $this->modelParser->parse($modelHtml, $modelUrl);
            $modelData = $this->resolvePrice($modelData);
        } catch (Throwable $e) {
            Log::error('honda-catalog: ingest failed for model page', [
                'url' => $modelUrl,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        $specsUrl = rtrim($modelUrl, '/').'/specifications';
        $specsData = $this->fetchSpecs($specsUrl, $modelData->slug);

        if ($specsData === null) {
            return null;
        }

        $contentHash = ContentHash::compute($modelData, $specsData);
        $existing = HondaModel::where('slug', $modelData->slug)->first();

        if (! $force && $existing && $existing->content_hash === $contentHash) {
            return $existing;
        }

        $strategy = $withAssets ? ($this->config['assets']['strategy'] ?? 'cdn') : 'cdn';

        return DB::transaction(function () use ($modelData, $specsData, $contentHash, $strategy) {
            $ogAsset = $modelData->ogImage ? $this->assets->record($modelData->ogImage, $strategy) : null;

            $model = HondaModel::updateOrCreate(
                ['slug' => $modelData->slug],
                [
                    'category' => $modelData->category,
                    'subcategory' => $modelData->subcategory,
                    'name' => $modelData->name,
                    'tagline' => $modelData->tagline,
                    'description' => $modelData->descriptionHtml,
                    'price_from' => $modelData->priceFromCents,
                    'price_currency' => $modelData->priceCurrency,
                    'price_label' => $modelData->priceLabel,
                    'source_url' => $modelData->sourceUrl,
                    'og_image_asset_id' => $ogAsset?->id,
                    'last_scraped_at' => now(),
                    'content_hash' => $contentHash,
                ],
            );

            $variantMap = $this->syncVariants($model, $modelData->variants);
            $this->syncFeatures($model, $modelData->features, $strategy);
            $this->syncColours($model, $modelData->colours, $strategy);
            $this->syncSpecifications($model, $specsData->rows, $variantMap);
            $this->syncGallery($model, $modelData->galleryImages, $strategy);

            return $model;
        });
    }

    /**
     * A 404 on the specifications sub-page is treated as "no specs yet" and
     * ingests the model anyway, rather than a fatal error - Honda publishes
     * pre-launch "Expression of Interest" model pages that have real hero
     * content but no specifications page until closer to release. Any other
     * failure still aborts the whole model, per ingest()'s normal handling.
     */
    private function fetchSpecs(string $specsUrl, string $slug): ?SpecsPageData
    {
        try {
            $specsHtml = (string) $this->http->get($specsUrl)->getBody();

            return $this->specsParser->parse($specsHtml, $specsUrl);
        } catch (Throwable $e) {
            if ($this->isNotFound($e)) {
                Log::warning('honda-catalog: specifications page not found, ingesting model without specs', [
                    'slug' => $slug,
                    'url' => $specsUrl,
                ]);

                return new SpecsPageData($slug, $specsUrl, [], []);
            }

            Log::error('honda-catalog: ingest failed for specifications page', [
                'url' => $specsUrl,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function isNotFound(Throwable $e): bool
    {
        $previous = $e->getPrevious();

        return $previous instanceof RequestException
            && $previous->getResponse()?->getStatusCode() === 404;
    }

    /**
     * Overrides the (almost always null) statically-scraped price with a
     * real value from the pricing API, when a pricing-model-id was found and
     * the API returns usable data. Returns a new DTO rather than mutating,
     * since ModelPageData is immutable - this also means the resolved price
     * flows into content_hash, so a price-only change still triggers re-sync.
     */
    private function resolvePrice(ModelPageData $modelData): ModelPageData
    {
        if ($modelData->pricingModelId === null) {
            return $modelData;
        }

        $price = $this->pricing->fetchPrice($modelData->pricingModelId);

        if ($price['cents'] === null) {
            return $modelData;
        }

        return new ModelPageData(
            slug: $modelData->slug,
            category: $modelData->category,
            subcategory: $modelData->subcategory,
            name: $modelData->name,
            tagline: $modelData->tagline,
            descriptionHtml: $modelData->descriptionHtml,
            priceFromCents: $price['cents'],
            priceCurrency: $modelData->priceCurrency,
            priceLabel: $price['label'] ?? $modelData->priceLabel,
            sourceUrl: $modelData->sourceUrl,
            ogImage: $modelData->ogImage,
            pricingModelId: $modelData->pricingModelId,
            features: $modelData->features,
            variants: $modelData->variants,
            colours: $modelData->colours,
            galleryImages: $modelData->galleryImages,
        );
    }

    /**
     * @return array<string, int> variant name => variant id
     */
    private function syncVariants(HondaModel $model, array $variants): array
    {
        $model->variants()->delete();
        $map = [];

        foreach ($variants as $variant) {
            $map[$variant->name] = $model->variants()->create([
                'name' => $variant->name,
                'price' => $variant->priceCents,
                'sort' => $variant->sort,
            ])->id;
        }

        return $map;
    }

    private function syncFeatures(HondaModel $model, array $features, string $strategy): void
    {
        $model->features()->delete();

        foreach ($features as $feature) {
            $image = $feature->image ? $this->assets->record($feature->image, $strategy) : null;

            $model->features()->create([
                'sort' => $feature->sort,
                'heading' => $feature->heading,
                'body' => $feature->body,
                'image_asset_id' => $image?->id,
            ]);
        }
    }

    private function syncColours(HondaModel $model, array $colours, string $strategy): void
    {
        $model->colours()->delete();

        foreach ($colours as $colour) {
            $image = $colour->image ? $this->assets->record($colour->image, $strategy) : null;

            $model->colours()->create([
                'name' => $colour->name,
                'hex' => $colour->hex,
                'image_asset_id' => $image?->id,
                'sort' => $colour->sort,
            ]);
        }
    }

    /**
     * @param  array<string, int>  $variantMap
     */
    private function syncSpecifications(HondaModel $model, array $rows, array $variantMap): void
    {
        $model->specifications()->delete();

        foreach ($rows as $row) {
            $variantId = null;

            if ($row->variantName !== null) {
                $variantId = $variantMap[$row->variantName] ?? null;

                if ($variantId === null) {
                    Log::warning('honda-catalog: spec row references an unknown variant, storing without linkage', [
                        'model' => $model->slug,
                        'variant' => $row->variantName,
                        'label' => $row->label,
                    ]);
                }
            }

            $model->specifications()->create([
                'variant_id' => $variantId,
                'section' => $row->section,
                'category' => $row->category,
                'label' => $row->label,
                'value' => $row->value,
                'sort' => $row->sort,
            ]);
        }
    }

    private function syncGallery(HondaModel $model, array $galleryImages, string $strategy): void
    {
        $model->assets()->wherePivot('role', AssetRole::Gallery->value)->detach();

        foreach ($galleryImages as $i => $ref) {
            $asset = $this->assets->record($ref, $strategy);

            $model->assets()->syncWithoutDetaching([
                $asset->id => ['role' => AssetRole::Gallery->value, 'sort' => $i],
            ]);
        }
    }
}
