<?php

namespace Honda\Catalog\Parsing;

use Honda\Catalog\DataTransferObjects\AssetRef;
use Honda\Catalog\DataTransferObjects\OfferBlockData;
use Honda\Catalog\Enums\AssetHost;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Parses "offer card" pages - both the top-level /offers page and any
 * campaign's own listing sub-page (e.g. /offers/model-runout) use the exact
 * same .block--card markup Honda already uses for model-page feature blocks,
 * so a single generic parser covers both without knowing which page it's on.
 */
class OfferPageParser
{
    public function __construct(private readonly array $selectors = []) {}

    /**
     * @return OfferBlockData[]
     */
    public function parse(string $html, string $sourceUrl): array
    {
        $crawler = new Crawler($html);
        $blockSelector = $this->selectors['block'] ?? '.block.block--card';
        $blocks = [];

        $crawler->filter($blockSelector)->each(function (Crawler $node, int $i) use (&$blocks) {
            $title = $this->firstText($node, $this->selectors['title'] ?? 'h2.field-blocktitle');

            if ($title === null) {
                return;
            }

            $blocks[] = new OfferBlockData(
                title: $title,
                subtitle: $this->firstText($node, $this->selectors['subtitle'] ?? '.field-blocksubtitle'),
                priceLabel: $this->firstText($node, $this->selectors['price'] ?? '.sale-price'),
                // Honda nests <p class="field-blockcontent"><p>...</p></p>; a
                // parser auto-closes the outer <p> on the inner one per HTML5
                // rules, leaving .field-blockcontent empty. .cmp-text is the
                // real wrapping div and isn't affected.
                bodyHtml: $this->firstHtml($node, $this->selectors['body'] ?? '.block__body-text .cmp-text, .field-blockcontent'),
                image: $this->buildAssetRef($this->firstAttr(
                    $node,
                    $this->selectors['image'] ?? '.block__image-link image, .block__image-link img',
                    'src',
                )),
                ctaUrl: $this->firstAttr($node, $this->selectors['cta'] ?? '.ctasBlock__item--cta', 'href'),
                ctaLabel: $this->firstText($node, $this->selectors['cta'] ?? '.ctasBlock__item--cta'),
                sort: $i,
            );
        });

        return $blocks;
    }

    private function buildAssetRef(?string $url): ?AssetRef
    {
        $url = trim((string) $url);

        if ($url === '') {
            return null;
        }

        if (str_starts_with($url, '//')) {
            $url = 'https:'.$url;
        }

        $parts = parse_url($url);
        $host = $parts['host'] ?? '';
        $path = $parts['path'] ?? '';

        parse_str($parts['query'] ?? '', $query);
        $versionHash = $query['v'] ?? null;

        $cleanUrl = ($parts['scheme'] ?? 'https').'://'.$host.$path;

        if (str_contains($host, 'contenthub')) {
            preg_match('#/content/([a-f0-9-]+)#i', $path, $m);
            $guid = $m[1] ?? sha1($cleanUrl);
            $assetHost = AssetHost::ContentHub;
        } else {
            $guid = sha1($cleanUrl);
            $assetHost = AssetHost::Sitecore;
        }

        return new AssetRef($guid, $cleanUrl, $versionHash, $assetHost);
    }

    private function firstText(Crawler $crawler, string $selector): ?string
    {
        $node = $crawler->filter($selector);

        if ($node->count() === 0) {
            return null;
        }

        $text = trim($node->first()->text());

        return $text !== '' ? $text : null;
    }

    private function firstHtml(Crawler $crawler, string $selector): ?string
    {
        $node = $crawler->filter($selector);

        if ($node->count() === 0) {
            return null;
        }

        $html = trim($node->first()->html());

        return $html !== '' ? $html : null;
    }

    private function firstAttr(Crawler $crawler, string $selector, string $attribute): ?string
    {
        $node = $crawler->filter($selector);

        if ($node->count() === 0) {
            return null;
        }

        return $node->first()->attr($attribute);
    }
}
