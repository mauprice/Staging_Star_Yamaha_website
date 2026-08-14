<?php

namespace Honda\Catalog\Tests\Feature;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Honda\Catalog\Assets\AssetManager;
use Honda\Catalog\Http\Exceptions\FetchException;
use Honda\Catalog\Http\ThrottledHttpClient;
use Honda\Catalog\Models\HondaModel;
use Honda\Catalog\Parsing\ModelPageParser;
use Honda\Catalog\Parsing\SpecsPageParser;
use Honda\Catalog\Pricing\MpePcmPricingClient;
use Honda\Catalog\Services\IngestService;
use Honda\Catalog\Tests\TestCase;
use Illuminate\Support\Facades\Storage;

class IngestServiceTest extends TestCase
{
    private const MODEL_URL = 'https://motorcycles.honda.com.au/models/offroad/competition/crf450r';

    private const SPECS_URL = 'https://motorcycles.honda.com.au/models/offroad/competition/crf450r/specifications';

    private const PRICING_MODEL_ID = '{097E0CC1-5601-4C4D-845C-FC56CABAF8CC}';

    private function service(?ThrottledHttpClient $http = null, array $config = []): IngestService
    {
        $http ??= $this->fixtureHttpClient();
        $config = array_merge(['assets' => ['strategy' => 'cdn']], $config);

        return new IngestService(
            $http,
            new ModelPageParser(config('honda-catalog.selectors.model_page')),
            new SpecsPageParser(config('honda-catalog.selectors.specs_page')),
            new AssetManager($http, array_merge(['disk' => 'public', 'path_prefix' => 'honda-catalog'], $config['assets'] ?? [])),
            new MpePcmPricingClient($http, array_merge(['base_url' => 'https://motorcycles.honda.com.au', 'pricing' => ['enabled' => true]], $config)),
            $config,
        );
    }

    private function fixtureHttpClient(): ThrottledHttpClient
    {
        $http = $this->createMock(ThrottledHttpClient::class);
        $http->method('get')->willReturnCallback(function (string $url) {
            return match ($url) {
                self::MODEL_URL => new Response(200, [], $this->fixture('model-page-crf450r.html')),
                self::SPECS_URL => new Response(200, [], $this->fixture('specs-page-crf450r.html')),
                default => new Response(200, [], 'fake-asset-bytes'),
            };
        });
        $http->method('post')->willReturn(new Response(200, [], json_encode([
            ['modelItemId' => self::PRICING_MODEL_ID, 'productPrice' => '14899'],
        ])));

        return $http;
    }

    public function test_it_ingests_a_model_end_to_end(): void
    {
        $model = $this->service()->ingest(self::MODEL_URL);

        $this->assertNotNull($model);
        $this->assertSame('crf450r', $model->slug);
        $this->assertSame('CRF450R', $model->name);
        $this->assertCount(2, $model->features);
        $this->assertCount(2, $model->variants);
        $this->assertCount(2, $model->colours);
        $this->assertGreaterThan(0, $model->specifications()->count());
        $this->assertNotNull($model->content_hash);
        $this->assertNotNull($model->last_scraped_at);
    }

    public function test_price_from_the_real_pricing_api_is_resolved_and_persisted(): void
    {
        $model = $this->service()->ingest(self::MODEL_URL);

        $this->assertSame(1489900, $model->price_from);
        $this->assertSame('Ride away from', $model->price_label);
    }

    public function test_pricing_api_failure_leaves_price_null_without_failing_the_model(): void
    {
        $http = $this->createMock(ThrottledHttpClient::class);
        $http->method('get')->willReturnCallback(function (string $url) {
            return match ($url) {
                self::MODEL_URL => new Response(200, [], $this->fixture('model-page-crf450r.html')),
                self::SPECS_URL => new Response(200, [], $this->fixture('specs-page-crf450r.html')),
                default => new Response(200, [], 'fake-asset-bytes'),
            };
        });
        $http->method('post')->willThrowException(new \RuntimeException('pricing api down'));

        $model = $this->service($http)->ingest(self::MODEL_URL);

        $this->assertNotNull($model);
        $this->assertNull($model->price_from);
    }

    public function test_specifications_are_linked_to_the_correct_variant(): void
    {
        $model = $this->service()->ingest(self::MODEL_URL);

        $displacementForRWE = $model->specifications()
            ->where('label', 'Displacement')
            ->whereHas('variant', fn ($q) => $q->where('name', 'CRF450RWE'))
            ->first();

        $this->assertNotNull($displacementForRWE);
        $this->assertSame('449cc', $displacementForRWE->value);
    }

    public function test_it_skips_reingest_when_content_is_unchanged(): void
    {
        $service = $this->service();
        $first = $service->ingest(self::MODEL_URL);
        $firstScrapedAt = $first->last_scraped_at;

        $this->travel(1)->seconds();
        $second = $service->ingest(self::MODEL_URL);

        $this->assertEquals($firstScrapedAt->timestamp, $second->last_scraped_at->timestamp);
        $this->assertDatabaseCount('honda_models', 1);
    }

    public function test_force_reingests_even_when_content_is_unchanged(): void
    {
        $service = $this->service();
        $first = $service->ingest(self::MODEL_URL);
        $firstScrapedAt = $first->last_scraped_at;

        $this->travel(1)->seconds();
        $second = $service->ingest(self::MODEL_URL, force: true);

        $this->assertNotEquals($firstScrapedAt->timestamp, $second->last_scraped_at->timestamp);
    }

    public function test_reingest_replaces_child_rows_rather_than_duplicating(): void
    {
        $service = $this->service();
        $service->ingest(self::MODEL_URL, force: true);
        $service->ingest(self::MODEL_URL, force: true);

        $model = HondaModel::where('slug', 'crf450r')->first();

        $this->assertCount(2, $model->features);
        $this->assertCount(2, $model->variants);
    }

    public function test_a_404_specs_page_ingests_the_model_without_specifications(): void
    {
        $http = $this->createMock(ThrottledHttpClient::class);
        $http->method('get')->willReturnCallback(function (string $url) {
            if ($url === self::SPECS_URL) {
                $request = new Request('GET', $url);
                $response = new Response(404);
                $previous = RequestException::create($request, $response);

                throw new FetchException('Failed to fetch: 404', previous: $previous);
            }

            return new Response(200, [], $this->fixture('model-page-crf450r.html'));
        });

        $model = $this->service($http)->ingest(self::MODEL_URL);

        $this->assertNotNull($model);
        $this->assertSame('crf450r', $model->slug);
        $this->assertSame(0, $model->specifications()->count());
    }

    public function test_a_non_404_specs_failure_still_aborts_the_whole_model(): void
    {
        $http = $this->createMock(ThrottledHttpClient::class);
        $http->method('get')->willReturnCallback(function (string $url) {
            if ($url === self::SPECS_URL) {
                throw new \RuntimeException('connection refused');
            }

            return new Response(200, [], $this->fixture('model-page-crf450r.html'));
        });

        $result = $this->service($http)->ingest(self::MODEL_URL);

        $this->assertNull($result);
        $this->assertDatabaseCount('honda_models', 0);
    }

    public function test_it_returns_null_and_does_not_throw_when_fetch_fails(): void
    {
        $http = $this->createMock(ThrottledHttpClient::class);
        $http->method('get')->willThrowException(new \RuntimeException('connection refused'));

        $result = $this->service($http)->ingest(self::MODEL_URL);

        $this->assertNull($result);
        $this->assertDatabaseCount('honda_models', 0);
    }

    public function test_with_assets_true_and_mirror_strategy_downloads_images(): void
    {
        Storage::fake('public');

        $model = $this->service(null, ['assets' => ['strategy' => 'mirror']])
            ->ingest(self::MODEL_URL, withAssets: true);

        $this->assertNotNull($model->ogImage);
        $this->assertSame('mirrored', $model->ogImage->status->value);
    }

    public function test_without_with_assets_flag_uses_cdn_even_if_mirror_configured(): void
    {
        $model = $this->service(null, ['assets' => ['strategy' => 'mirror']])
            ->ingest(self::MODEL_URL, withAssets: false);

        $this->assertSame('remote', $model->ogImage->status->value);
    }
}
