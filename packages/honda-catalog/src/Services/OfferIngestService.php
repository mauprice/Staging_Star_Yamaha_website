<?php

namespace Honda\Catalog\Services;

use Honda\Catalog\Assets\AssetManager;
use Honda\Catalog\DataTransferObjects\OfferBlockData;
use Honda\Catalog\Http\ThrottledHttpClient;
use Honda\Catalog\Models\HondaModel;
use Honda\Catalog\Models\HondaOffer;
use Honda\Catalog\Parsing\OfferPageParser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class OfferIngestService
{
    public function __construct(
        private readonly ThrottledHttpClient $http,
        private readonly OfferPageParser $parser,
        private readonly AssetManager $assets,
        private readonly array $config = [],
    ) {}

    /**
     * Crawls the offers entry page, ingests each top-level offer, follows
     * any CTA that links to another /offers/... page and ingests its cards
     * as that offer's children, then deactivates (never deletes) any
     * previously-synced offer or child not seen in this run.
     *
     * @return array{synced: int, skipped: int, deactivated: int, failed: int}
     */
    public function syncAll(bool $withAssets = false, bool $force = false): array
    {
        $stats = ['synced' => 0, 'skipped' => 0, 'deactivated' => 0, 'failed' => 0];
        $strategy = $withAssets ? ($this->config['assets']['strategy'] ?? 'cdn') : 'cdn';
        $entryPath = $this->config['offers']['entry_path'] ?? '/offers';
        $entryUrl = rtrim($this->config['base_url'] ?? '', '/').$entryPath;

        try {
            $blocks = $this->parser->parse((string) $this->http->get($entryUrl)->getBody(), $entryUrl);
        } catch (Throwable $e) {
            Log::error('honda-catalog: failed to fetch offers entry page', [
                'url' => $entryUrl,
                'error' => $e->getMessage(),
            ]);

            return $stats;
        }

        $seenSlugs = [];

        foreach ($blocks as $block) {
            $offer = $this->ingestOffer($block, null, $entryUrl, $strategy, $force, $stats);

            if ($offer === null) {
                $stats['failed']++;

                continue;
            }

            $seenSlugs[] = $offer->slug;

            if ($block->ctaUrl && preg_match($this->config['offers']['child_page_pattern'] ?? '#^/offers/#', $block->ctaUrl)) {
                $seenSlugs = array_merge($seenSlugs, $this->syncChildren($offer, $block->ctaUrl, $strategy, $force, $stats));
            }
        }

        $stats['deactivated'] = HondaOffer::whereNotIn('slug', $seenSlugs)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        return $stats;
    }

    /**
     * @return string[] slugs of the children ingested from this listing page
     */
    private function syncChildren(HondaOffer $parent, string $childPath, string $strategy, bool $force, array &$stats): array
    {
        $childUrl = str_starts_with($childPath, 'http')
            ? $childPath
            : rtrim($this->config['base_url'] ?? '', '/').'/'.ltrim($childPath, '/');

        try {
            $childBlocks = $this->parser->parse((string) $this->http->get($childUrl)->getBody(), $childUrl);
        } catch (Throwable $e) {
            Log::error('honda-catalog: failed to fetch offer child listing page', [
                'url' => $childUrl,
                'error' => $e->getMessage(),
            ]);

            return [];
        }

        $slugs = [];

        foreach ($childBlocks as $block) {
            $child = $this->ingestOffer($block, $parent, $childUrl, $strategy, $force, $stats);

            if ($child === null) {
                $stats['failed']++;

                continue;
            }

            $slugs[] = $child->slug;
        }

        return $slugs;
    }

    private function ingestOffer(
        OfferBlockData $block,
        ?HondaOffer $parent,
        string $sourceUrl,
        string $strategy,
        bool $force,
        array &$stats,
    ): ?HondaOffer {
        $slug = ($parent ? $parent->slug.'-' : '').Str::slug($block->title);
        $contentHash = hash('sha256', json_encode($block->toArray(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $existing = HondaOffer::where('slug', $slug)->first();

        if (! $force && $existing && $existing->content_hash === $contentHash && $existing->is_active) {
            $stats['skipped']++;

            return $existing;
        }

        return DB::transaction(function () use ($block, $parent, $sourceUrl, $strategy, $slug, $contentHash, &$stats) {
            $image = $block->image ? $this->assets->record($block->image, $strategy) : null;

            $offer = HondaOffer::updateOrCreate(
                ['slug' => $slug],
                [
                    'parent_id' => $parent?->id,
                    'title' => $block->title,
                    'subtitle' => $block->subtitle,
                    'price_label' => $block->priceLabel,
                    'body' => $block->bodyHtml,
                    'image_asset_id' => $image?->id,
                    'cta_url' => $block->ctaUrl,
                    'cta_label' => $block->ctaLabel,
                    'honda_model_id' => $this->resolveHondaModelId($block->ctaUrl),
                    'source_url' => $sourceUrl,
                    'sort' => $block->sort,
                    'content_hash' => $contentHash,
                    'last_scraped_at' => now(),
                    'is_active' => true,
                ],
            );

            $stats['synced']++;

            return $offer;
        });
    }

    private function resolveHondaModelId(?string $ctaUrl): ?int
    {
        if (! $ctaUrl) {
            return null;
        }

        $pattern = $this->config['discovery']['model_url_pattern'] ?? '#^/models/([a-z0-9]+)/([a-z0-9]+)/([a-z0-9-]+)$#';
        $path = parse_url($ctaUrl, PHP_URL_PATH) ?: $ctaUrl;

        if (! preg_match($pattern, $path, $m)) {
            return null;
        }

        return HondaModel::where('slug', $m[3])->value('id');
    }
}
