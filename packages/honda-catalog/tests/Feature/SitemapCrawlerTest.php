<?php

namespace Honda\Catalog\Tests\Feature;

use GuzzleHttp\Psr7\Response;
use Honda\Catalog\Crawling\SitemapCrawler;
use Honda\Catalog\Http\ThrottledHttpClient;
use Honda\Catalog\Tests\TestCase;

class SitemapCrawlerTest extends TestCase
{
    private function crawler(array $configOverrides = []): SitemapCrawler
    {
        $http = $this->createMock(ThrottledHttpClient::class);

        return new SitemapCrawler($http, array_merge(
            config('honda-catalog.discovery'),
            $configOverrides,
        ));
    }

    public function test_it_filters_to_only_canonical_model_urls(): void
    {
        $results = $this->crawler()->parse($this->fixture('sitemap.xml'));

        $urls = array_column($results, 'url');

        $this->assertContains('https://motorcycles.honda.com.au/models/offroad/competition/crf450r', $urls);
        $this->assertContains('https://motorcycles.honda.com.au/models/onroad/adventuretouring/africatwin', $urls);

        // Excluded: homepage, non-model pages, specifications sub-pages,
        // a too-short models path, and a too-long models path.
        $this->assertNotContains('https://motorcycles.honda.com.au/', $urls);
        $this->assertNotContains('https://motorcycles.honda.com.au/news/some-article', $urls);
        $this->assertNotContains('https://motorcycles.honda.com.au/dealers/find-a-dealer', $urls);
        $this->assertNotContains('https://motorcycles.honda.com.au/models/offroad/competition/crf450r/specifications', $urls);
        $this->assertNotContains('https://motorcycles.honda.com.au/models/offroad/', $urls);
        $this->assertNotContains('https://motorcycles.honda.com.au/models/offroad/competition/crf450r/gallery/extra', $urls);
    }

    public function test_it_derives_category_and_subcategory_from_the_path(): void
    {
        $results = $this->crawler()->parse($this->fixture('sitemap.xml'));

        $crf450r = collect($results)->first(fn ($r) => $r['url'] === 'https://motorcycles.honda.com.au/models/offroad/competition/crf450r');

        $this->assertSame('offroad', $crf450r['category']);
        $this->assertSame('competition', $crf450r['subcategory']);
    }

    public function test_category_allow_list_narrows_an_already_valid_set(): void
    {
        $results = $this->crawler(['category_allow_list' => ['onroad']])->parse($this->fixture('sitemap.xml'));

        $categories = array_unique(array_column($results, 'category'));

        $this->assertSame(['onroad'], $categories);
    }

    public function test_empty_allow_list_permits_every_category(): void
    {
        $results = $this->crawler(['category_allow_list' => []])->parse($this->fixture('sitemap.xml'));

        $categories = array_unique(array_column($results, 'category'));
        sort($categories);

        $this->assertSame(['offroad', 'onroad'], $categories);
    }

    public function test_malformed_xml_returns_an_empty_array_without_throwing(): void
    {
        $results = $this->crawler()->parse('not xml at all');

        $this->assertSame([], $results);
    }

    public function test_discover_fetches_and_parses_via_the_http_client(): void
    {
        $http = $this->createMock(ThrottledHttpClient::class);
        $http->expects($this->once())
            ->method('get')
            ->with('https://motorcycles.honda.com.au/sitemap.xml')
            ->willReturn(new Response(200, [], $this->fixture('sitemap.xml')));

        $crawler = new SitemapCrawler($http, config('honda-catalog.discovery'));
        $results = $crawler->discover('https://motorcycles.honda.com.au/sitemap.xml');

        $this->assertNotEmpty($results);
    }
}
