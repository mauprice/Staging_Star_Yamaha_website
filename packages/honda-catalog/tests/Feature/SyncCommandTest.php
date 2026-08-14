<?php

namespace Honda\Catalog\Tests\Feature;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Honda\Catalog\Models\HondaModel;
use Honda\Catalog\Tests\TestCase;

class SyncCommandTest extends TestCase
{
    private function bindFakeClient(): void
    {
        $sitemap = $this->fixture('sitemap.xml');
        $modelHtml = $this->fixture('model-page-crf450r.html');
        $specsHtml = $this->fixture('specs-page-crf450r.html');

        $fake = new class($sitemap, $modelHtml, $specsHtml) implements \GuzzleHttp\ClientInterface
        {
            public function __construct(
                private string $sitemap,
                private string $modelHtml,
                private string $specsHtml,
            ) {}

            public function send(\Psr\Http\Message\RequestInterface $request, array $options = []): \Psr\Http\Message\ResponseInterface
            {
                return $this->request($request->getMethod(), (string) $request->getUri(), $options);
            }

            public function sendAsync(\Psr\Http\Message\RequestInterface $request, array $options = []): \GuzzleHttp\Promise\PromiseInterface
            {
                throw new \RuntimeException('not implemented in fake');
            }

            public function request(string $method, $uri = '', array $options = []): \Psr\Http\Message\ResponseInterface
            {
                $uri = (string) $uri;

                return match (true) {
                    str_ends_with($uri, 'sitemap.xml') => new Response(200, [], $this->sitemap),
                    str_ends_with($uri, '/specifications') => new Response(200, [], $this->specsHtml),
                    default => new Response(200, [], $this->modelHtml),
                };
            }

            public function requestAsync(string $method, $uri = '', array $options = []): \GuzzleHttp\Promise\PromiseInterface
            {
                throw new \RuntimeException('not implemented in fake');
            }

            public function getConfig($option = null) {}
        };

        $this->app->bind(ClientInterface::class, fn () => $fake);
    }

    public function test_sync_ingests_models_discovered_from_the_sitemap(): void
    {
        $this->bindFakeClient();

        $this->artisan('honda-catalog:sync')->assertExitCode(0);

        $this->assertDatabaseHas('honda_models', ['slug' => 'crf450r']);
    }

    public function test_sync_with_model_option_filters_to_a_single_model(): void
    {
        $this->bindFakeClient();

        $this->artisan('honda-catalog:sync', ['--model' => 'crf450r'])->assertExitCode(0);

        $this->assertDatabaseCount('honda_models', 1);
    }

    public function test_sync_with_model_option_not_found_fails(): void
    {
        $this->bindFakeClient();

        $this->artisan('honda-catalog:sync', ['--model' => 'does-not-exist'])->assertExitCode(1);
    }

    public function test_dry_run_does_not_persist_any_changes(): void
    {
        $this->bindFakeClient();

        $this->artisan('honda-catalog:sync', ['--model' => 'crf450r', '--dry-run' => true])->assertExitCode(0);

        $this->assertDatabaseCount('honda_models', 0);
    }

    public function test_second_run_without_force_skips_unchanged_models(): void
    {
        $this->bindFakeClient();

        $this->artisan('honda-catalog:sync', ['--model' => 'crf450r'])->assertExitCode(0);
        $countAfterFirst = HondaModel::count();

        $this->artisan('honda-catalog:sync', ['--model' => 'crf450r'])->assertExitCode(0);

        $this->assertSame($countAfterFirst, HondaModel::count());
        $this->assertDatabaseCount('honda_models', 1);
    }
}
