<?php

namespace Honda\Catalog\Crawling;

use Honda\Catalog\Http\ThrottledHttpClient;

class SitemapCrawler
{
    public function __construct(
        private readonly ThrottledHttpClient $http,
        private readonly array $config = [],
    ) {}

    /**
     * Fetches and parses the sitemap, returning canonical model URLs
     * (structural pattern match first, then category allow-list).
     *
     * @return array<int, array{url: string, category: string, subcategory: string}>
     */
    public function discover(string $sitemapUrl): array
    {
        $body = (string) $this->http->get($sitemapUrl)->getBody();

        return $this->parse($body);
    }

    /**
     * @return array<int, array{url: string, category: string, subcategory: string}>
     */
    public function parse(string $xml): array
    {
        $previous = libxml_use_internal_errors(true);
        $doc = simplexml_load_string($xml);
        libxml_use_internal_errors($previous);

        if ($doc === false) {
            return [];
        }

        $results = [];

        foreach ($doc->url as $urlNode) {
            $loc = trim((string) $urlNode->loc);

            if ($loc === '') {
                continue;
            }

            $match = $this->matchModelPattern($loc);
            if ($match === null) {
                continue;
            }

            if (! $this->isAllowedCategory($match['category'])) {
                continue;
            }

            $results[] = $match;
        }

        return $results;
    }

    /**
     * @return array{url: string, category: string, subcategory: string}|null
     */
    private function matchModelPattern(string $url): ?array
    {
        $pattern = $this->config['model_url_pattern'] ?? '#^/models/([a-z0-9]+)/([a-z0-9]+)/([a-z0-9-]+)$#';
        $path = parse_url($url, PHP_URL_PATH) ?: '';

        if (! preg_match($pattern, $path, $m)) {
            return null;
        }

        return [
            'url' => $url,
            'category' => $m[1],
            'subcategory' => $m[2],
        ];
    }

    private function isAllowedCategory(string $category): bool
    {
        $allowList = $this->config['category_allow_list'] ?? [];

        if (empty($allowList)) {
            return true;
        }

        return in_array($category, $allowList, true);
    }
}
