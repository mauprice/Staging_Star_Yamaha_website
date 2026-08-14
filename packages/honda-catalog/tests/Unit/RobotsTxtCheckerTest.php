<?php

namespace Honda\Catalog\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Honda\Catalog\Http\Exceptions\RobotsDisallowedException;
use Honda\Catalog\Http\RobotsTxtChecker;
use PHPUnit\Framework\TestCase;

class RobotsTxtCheckerTest extends TestCase
{
    private const ROBOTS_TXT = <<<'TXT'
        User-agent: *
        Disallow: /sitecore/
        Disallow: /temp/

        User-agent: BadBot
        Disallow: /
        TXT;

    private function makeChecker(string $body, bool $enabled = true): RobotsTxtChecker
    {
        $mock = new MockHandler([new Response(200, [], $body)]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        return new RobotsTxtChecker($client, 'HondaCatalogBot', $enabled);
    }

    public function test_it_allows_paths_not_covered_by_any_disallow_rule(): void
    {
        $checker = $this->makeChecker(self::ROBOTS_TXT);

        $checker->assertAllowed('https://example.test/models/offroad/competition/crf450r');

        $this->assertTrue(true); // reaching here without an exception is the assertion
    }

    public function test_it_disallows_a_blocked_prefix(): void
    {
        $checker = $this->makeChecker(self::ROBOTS_TXT);

        $this->expectException(RobotsDisallowedException::class);
        $checker->assertAllowed('https://example.test/sitecore/something');
    }

    public function test_disabled_checker_never_throws(): void
    {
        $checker = $this->makeChecker(self::ROBOTS_TXT, enabled: false);

        $checker->assertAllowed('https://example.test/sitecore/something');

        $this->assertTrue(true);
    }

    public function test_a_specific_user_agent_group_overrides_the_wildcard_group(): void
    {
        $robots = <<<'TXT'
            User-agent: *
            Disallow: /models/

            User-agent: HondaCatalogBot
            Disallow:
            TXT;

        $checker = $this->makeChecker($robots);

        // The specific "HondaCatalogBot" group has an empty Disallow (allow
        // everything), and per standard robots.txt semantics a matching
        // specific group replaces the wildcard group entirely for this UA.
        $checker->assertAllowed('https://example.test/models/offroad/competition/crf450r');

        $this->assertTrue(true);
    }

    public function test_unreachable_robots_txt_fails_open(): void
    {
        $mock = new MockHandler([new \GuzzleHttp\Exception\ConnectException(
            'refused',
            new \GuzzleHttp\Psr7\Request('GET', 'https://example.test/robots.txt'),
        )]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);
        $checker = new RobotsTxtChecker($client, 'HondaCatalogBot', true);

        $checker->assertAllowed('https://example.test/anything');

        $this->assertTrue(true);
    }
}
