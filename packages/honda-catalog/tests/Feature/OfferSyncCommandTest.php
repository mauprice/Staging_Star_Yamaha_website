<?php

namespace Honda\Catalog\Tests\Feature;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Honda\Catalog\Tests\TestCase;

class OfferSyncCommandTest extends TestCase
{
    private function bindFakeClient(): void
    {
        $offersHtml = $this->fixture('offers-page.html');
        $runoutHtml = $this->fixture('offer-runout-page.html');

        $fake = new class($offersHtml, $runoutHtml) implements ClientInterface
        {
            public function __construct(
                private string $offersHtml,
                private string $runoutHtml,
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
                    str_ends_with($uri, '/offers/model-runout') => new Response(200, [], $this->runoutHtml),
                    default => new Response(200, [], $this->offersHtml),
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

    public function test_sync_offers_ingests_top_level_offers_and_children(): void
    {
        $this->bindFakeClient();

        $this->artisan('honda-catalog:sync-offers')->assertExitCode(0);

        $this->assertDatabaseHas('honda_offers', ['slug' => 'honda-runout-models']);
        $this->assertDatabaseHas('honda_offers', ['slug' => 'honda-runout-models-cmx500']);
    }

    public function test_dry_run_does_not_persist_any_changes(): void
    {
        $this->bindFakeClient();

        $this->artisan('honda-catalog:sync-offers', ['--dry-run' => true])->assertExitCode(0);

        $this->assertDatabaseCount('honda_offers', 0);
    }

    public function test_second_run_without_force_skips_unchanged_offers(): void
    {
        $this->bindFakeClient();

        $this->artisan('honda-catalog:sync-offers')->assertExitCode(0);
        $this->artisan('honda-catalog:sync-offers')->assertExitCode(0);

        $this->assertDatabaseCount('honda_offers', 4);
    }
}
