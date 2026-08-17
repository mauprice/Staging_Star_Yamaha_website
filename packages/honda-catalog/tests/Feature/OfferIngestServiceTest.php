<?php

namespace Honda\Catalog\Tests\Feature;

use GuzzleHttp\Psr7\Response;
use Honda\Catalog\Assets\AssetManager;
use Honda\Catalog\Http\ThrottledHttpClient;
use Honda\Catalog\Models\HondaModel;
use Honda\Catalog\Models\HondaOffer;
use Honda\Catalog\Parsing\OfferPageParser;
use Honda\Catalog\Services\OfferIngestService;
use Honda\Catalog\Tests\TestCase;

class OfferIngestServiceTest extends TestCase
{
    private const ENTRY_URL = 'https://motorcycles.honda.com.au/offers';

    private const RUNOUT_URL = 'https://motorcycles.honda.com.au/offers/model-runout';

    private function service(?ThrottledHttpClient $http = null, array $config = []): OfferIngestService
    {
        $http ??= $this->fixtureHttpClient();
        $config = array_merge(config('honda-catalog'), ['assets' => ['strategy' => 'cdn']], $config);

        return new OfferIngestService(
            $http,
            new OfferPageParser(config('honda-catalog.selectors.offer_page')),
            new AssetManager($http, array_merge(['disk' => 'public', 'path_prefix' => 'honda-catalog'], $config['assets'] ?? [])),
            $config,
        );
    }

    private function fixtureHttpClient(): ThrottledHttpClient
    {
        $http = $this->createMock(ThrottledHttpClient::class);
        $http->method('get')->willReturnCallback(function (string $url) {
            return match ($url) {
                self::ENTRY_URL => new Response(200, [], $this->fixture('offers-page.html')),
                self::RUNOUT_URL => new Response(200, [], $this->fixture('offer-runout-page.html')),
                default => new Response(200, [], 'fake-asset-bytes'),
            };
        });

        return $http;
    }

    public function test_it_ingests_top_level_offers_and_follows_child_listing_pages(): void
    {
        $stats = $this->service()->syncAll();

        $this->assertSame(4, $stats['synced']); // 2 top-level + 2 runout children
        $this->assertSame(0, $stats['failed']);

        $this->assertDatabaseHas('honda_offers', ['slug' => 'ready-to-level-up', 'parent_id' => null]);
        $this->assertDatabaseHas('honda_offers', ['slug' => 'honda-runout-models', 'parent_id' => null]);

        $runout = HondaOffer::where('slug', 'honda-runout-models')->first();
        $this->assertCount(2, $runout->children);
        $this->assertSame('CMX500', $runout->children[0]->title);
        $this->assertSame('NOW FROM $9,814*', $runout->children[0]->price_label);
    }

    public function test_a_cta_matching_an_existing_honda_model_resolves_the_internal_link(): void
    {
        HondaModel::create([
            'slug' => 'cmx500',
            'category' => 'onroad',
            'subcategory' => 'street',
            'name' => 'CMX500',
            'source_url' => 'https://motorcycles.honda.com.au/models/onroad/street/cmx500',
        ]);

        $this->service()->syncAll();

        $child = HondaOffer::where('slug', 'honda-runout-models-cmx500')->first();
        $this->assertSame('cmx500', $child->hondaModel->slug);
    }

    public function test_a_child_with_no_cta_link_still_ingests_with_a_null_link(): void
    {
        $this->service()->syncAll();

        $child = HondaOffer::where('slug', 'honda-runout-models-crf300-rally')->first();
        $this->assertNotNull($child);
        $this->assertNull($child->cta_url);
        $this->assertNull($child->honda_model_id);
    }

    public function test_second_run_without_force_skips_unchanged_offers(): void
    {
        $service = $this->service();
        $service->syncAll();

        $stats = $service->syncAll();

        $this->assertSame(4, $stats['skipped']);
        $this->assertSame(0, $stats['synced']);
        $this->assertDatabaseCount('honda_offers', 4);
    }

    public function test_force_reingests_even_when_content_is_unchanged(): void
    {
        $service = $this->service();
        $service->syncAll();

        $stats = $service->syncAll(force: true);

        $this->assertSame(4, $stats['synced']);
        $this->assertSame(0, $stats['skipped']);
    }

    public function test_an_offer_no_longer_present_on_the_site_is_deactivated_not_deleted(): void
    {
        $this->service()->syncAll();
        $this->assertDatabaseCount('honda_offers', 4);

        // Simulate a follow-up run where Honda has removed the finance offer.
        $http = $this->createMock(ThrottledHttpClient::class);
        $http->method('get')->willReturnCallback(function (string $url) {
            if ($url === self::ENTRY_URL) {
                $html = str_replace(
                    'id="Ready to level up?"',
                    'id="Ready to level up? Removed" style="display:none" data-removed',
                    $this->fixture('offers-page.html'),
                );

                // Remove the whole "Ready to level up?" block outright so it
                // genuinely disappears from this run's parsed blocks.
                $start = strpos($html, '<div class="block block--card" id="Ready');
                $end = strpos($html, '<div class="block block--card" id="Honda Runout Models"');

                return new Response(200, [], substr($html, 0, $start).substr($html, $end));
            }

            return match ($url) {
                self::RUNOUT_URL => new Response(200, [], $this->fixture('offer-runout-page.html')),
                default => new Response(200, [], 'fake-asset-bytes'),
            };
        });

        $stats = $this->service($http)->syncAll();

        $this->assertSame(1, $stats['deactivated']);
        $this->assertDatabaseHas('honda_offers', ['slug' => 'ready-to-level-up', 'is_active' => false]);
        $this->assertDatabaseHas('honda_offers', ['slug' => 'honda-runout-models', 'is_active' => true]);
    }

    public function test_it_returns_empty_stats_and_does_not_throw_when_the_entry_page_fetch_fails(): void
    {
        $http = $this->createMock(ThrottledHttpClient::class);
        $http->method('get')->willThrowException(new \RuntimeException('connection refused'));

        $stats = $this->service($http)->syncAll();

        $this->assertSame(['synced' => 0, 'skipped' => 0, 'deactivated' => 0, 'failed' => 0], $stats);
        $this->assertDatabaseCount('honda_offers', 0);
    }
}
