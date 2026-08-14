<?php

namespace Honda\Catalog\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Honda\Catalog\Http\Exceptions\FetchException;
use Honda\Catalog\Http\Exceptions\RobotsDisallowedException;
use Honda\Catalog\Http\RobotsTxtChecker;
use Honda\Catalog\Http\ThrottledHttpClient;
use Honda\Catalog\Http\ThrottleGate;
use PHPUnit\Framework\TestCase;

class ThrottledHttpClientTest extends TestCase
{
    private function makeClient(MockHandler $mock, ?RobotsTxtChecker $robots = null): ThrottledHttpClient
    {
        $stack = HandlerStack::create($mock);
        $client = new Client(['handler' => $stack]);

        $robots ??= $this->allowAllRobots();
        // Zero-delay throttle keeps unit tests fast; ThrottleGate itself is
        // covered by ThrottleGateTest.
        $throttle = new ThrottleGate(0, function () {});

        return new ThrottledHttpClient($client, $robots, $throttle, [
            'retry_times' => 2,
            'retry_backoff_base' => 1,
            'user_agent' => 'test-agent',
        ]);
    }

    private function allowAllRobots(): RobotsTxtChecker
    {
        return new RobotsTxtChecker(new Client, 'test-agent', enabled: false);
    }

    public function test_it_returns_the_response_on_success(): void
    {
        $mock = new MockHandler([new Response(200, [], 'ok')]);
        $client = $this->makeClient($mock);

        $response = $client->get('https://example.test/page');

        $this->assertSame('ok', (string) $response->getBody());
    }

    public function test_it_retries_on_5xx_and_eventually_succeeds(): void
    {
        $mock = new MockHandler([
            new Response(500),
            new Response(200, [], 'ok'),
        ]);
        $client = $this->makeClient($mock);

        $response = $client->get('https://example.test/page');

        $this->assertSame('ok', (string) $response->getBody());
    }

    public function test_it_retries_on_connect_exception(): void
    {
        $mock = new MockHandler([
            new ConnectException('connection refused', new Request('GET', 'https://example.test/page')),
            new Response(200, [], 'ok'),
        ]);
        $client = $this->makeClient($mock);

        $response = $client->get('https://example.test/page');

        $this->assertSame('ok', (string) $response->getBody());
    }

    public function test_it_gives_up_after_configured_retries_and_throws(): void
    {
        $mock = new MockHandler([
            new Response(500), new Response(500), new Response(500),
        ]);
        $client = $this->makeClient($mock);

        $this->expectException(FetchException::class);
        $client->get('https://example.test/page');
    }

    public function test_it_does_not_retry_on_4xx(): void
    {
        $request = new Request('GET', 'https://example.test/page');
        $mock = new MockHandler([
            RequestException::create($request, new Response(404)),
            new Response(200, [], 'should not be reached'),
        ]);
        $client = $this->makeClient($mock);

        $this->expectException(FetchException::class);
        $client->get('https://example.test/page');
    }

    public function test_it_throws_when_robots_txt_disallows_the_path(): void
    {
        $mock = new MockHandler([new Response(200, [], 'ok')]);
        $robots = $this->createMock(RobotsTxtChecker::class);
        $robots->method('assertAllowed')->willThrowException(new RobotsDisallowedException('nope'));

        $client = $this->makeClient($mock, $robots);

        $this->expectException(RobotsDisallowedException::class);
        $client->get('https://example.test/disallowed');
    }
}
