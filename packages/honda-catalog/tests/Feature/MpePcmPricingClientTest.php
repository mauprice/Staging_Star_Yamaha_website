<?php

namespace Honda\Catalog\Tests\Feature;

use GuzzleHttp\Psr7\Response;
use Honda\Catalog\Http\ThrottledHttpClient;
use Honda\Catalog\Pricing\MpePcmPricingClient;
use Honda\Catalog\Tests\TestCase;

class MpePcmPricingClientTest extends TestCase
{
    private const ITEM_ID = '{097E0CC1-5601-4C4D-845C-FC56CABAF8CC}';

    private function config(array $overrides = []): array
    {
        return array_merge([
            'base_url' => 'https://motorcycles.honda.com.au',
            'pricing' => ['enabled' => true, 'endpoint' => '/api/MpePcm/GetMpePcmProduct', 'default_label' => 'Ride away from'],
        ], $overrides);
    }

    public function test_it_returns_price_in_cents_from_a_successful_response(): void
    {
        $http = $this->createMock(ThrottledHttpClient::class);
        $http->expects($this->once())
            ->method('post')
            ->with('https://motorcycles.honda.com.au/api/MpePcm/GetMpePcmProduct', [['itemId' => self::ITEM_ID]])
            ->willReturn(new Response(200, [], json_encode([
                ['modelItemId' => self::ITEM_ID, 'productPrice' => '14899', 'baseProductPrice' => '0'],
            ])));

        $client = new MpePcmPricingClient($http, $this->config());
        $price = $client->fetchPrice(self::ITEM_ID);

        $this->assertSame(1489900, $price['cents']);
        $this->assertSame('Ride away from', $price['label']);
    }

    public function test_it_matches_the_correct_entry_when_multiple_items_are_returned(): void
    {
        $http = $this->createMock(ThrottledHttpClient::class);
        $http->method('post')->willReturn(new Response(200, [], json_encode([
            ['modelItemId' => '{other}', 'productPrice' => '999'],
            ['modelItemId' => self::ITEM_ID, 'productPrice' => '14899'],
        ])));

        $client = new MpePcmPricingClient($http, $this->config());
        $price = $client->fetchPrice(self::ITEM_ID);

        $this->assertSame(1489900, $price['cents']);
    }

    public function test_zero_or_missing_price_yields_null(): void
    {
        $http = $this->createMock(ThrottledHttpClient::class);
        $http->method('post')->willReturn(new Response(200, [], json_encode([
            ['modelItemId' => self::ITEM_ID, 'productPrice' => '0'],
        ])));

        $client = new MpePcmPricingClient($http, $this->config());
        $price = $client->fetchPrice(self::ITEM_ID);

        $this->assertNull($price['cents']);
        $this->assertNull($price['label']);
    }

    public function test_request_failure_yields_null_rather_than_throwing(): void
    {
        $http = $this->createMock(ThrottledHttpClient::class);
        $http->method('post')->willThrowException(new \RuntimeException('network error'));

        $client = new MpePcmPricingClient($http, $this->config());
        $price = $client->fetchPrice(self::ITEM_ID);

        $this->assertNull($price['cents']);
    }

    public function test_malformed_json_yields_null_rather_than_throwing(): void
    {
        $http = $this->createMock(ThrottledHttpClient::class);
        $http->method('post')->willReturn(new Response(200, [], 'not json'));

        $client = new MpePcmPricingClient($http, $this->config());
        $price = $client->fetchPrice(self::ITEM_ID);

        $this->assertNull($price['cents']);
    }

    public function test_disabled_pricing_skips_the_request_entirely(): void
    {
        $http = $this->createMock(ThrottledHttpClient::class);
        $http->expects($this->never())->method('post');

        $client = new MpePcmPricingClient($http, $this->config(['pricing' => ['enabled' => false]]));
        $price = $client->fetchPrice(self::ITEM_ID);

        $this->assertNull($price['cents']);
    }
}
