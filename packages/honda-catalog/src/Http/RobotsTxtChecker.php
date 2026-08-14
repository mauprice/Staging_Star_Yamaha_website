<?php

namespace Honda\Catalog\Http;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Honda\Catalog\Http\Exceptions\RobotsDisallowedException;

/**
 * Minimal robots.txt parser: matches User-agent: * and the configured UA,
 * applies the longest matching Disallow/Allow prefix. Scope is deliberately
 * narrow (one known site) - not a full RFC 9309 implementation.
 */
class RobotsTxtChecker
{
    /** @var array<string, array<int, array{type: string, prefix: string}>>|null */
    private ?array $rules = null;

    public function __construct(
        private readonly ClientInterface $client,
        private readonly string $userAgent,
        private readonly bool $enabled = true,
    ) {}

    public function assertAllowed(string $url): void
    {
        if (! $this->enabled) {
            return;
        }

        $path = parse_url($url, PHP_URL_PATH) ?: '/';
        $rules = $this->rulesFor($url);

        $match = null;
        foreach ($rules as $rule) {
            // An empty Disallow value is the standard robots.txt idiom for
            // "disallow nothing" - it must not be treated as a wildcard match.
            if ($rule['type'] === 'disallow' && $rule['prefix'] === '') {
                continue;
            }

            if (str_starts_with($path, $rule['prefix'])) {
                if ($match === null || strlen($rule['prefix']) > strlen($match['prefix'])) {
                    $match = $rule;
                }
            }
        }

        if ($match !== null && $match['type'] === 'disallow') {
            throw new RobotsDisallowedException("robots.txt disallows fetching: {$url}");
        }
    }

    /**
     * @return array<int, array{type: string, prefix: string}>
     */
    private function rulesFor(string $url): array
    {
        if ($this->rules !== null) {
            return $this->rules;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME) ?: 'https';
        $host = parse_url($url, PHP_URL_HOST);
        $robotsUrl = "{$scheme}://{$host}/robots.txt";

        try {
            $response = $this->client->request('GET', $robotsUrl, ['timeout' => 10]);
            $body = (string) $response->getBody();
        } catch (GuzzleException) {
            // If robots.txt can't be fetched, fail open rather than blocking every request.
            return $this->rules = [];
        }

        return $this->rules = $this->parse($body, $this->userAgent);
    }

    /**
     * @return array<int, array{type: string, prefix: string}>
     */
    private function parse(string $body, string $userAgent): array
    {
        $groups = []; // [ [uas => [...], rules => [...]], ... ]
        $currentIndex = -1;

        foreach (preg_split('/\r\n|\r|\n/', $body) as $line) {
            $line = trim(preg_replace('/#.*$/', '', $line));
            if ($line === '' || ! str_contains($line, ':')) {
                continue;
            }

            [$field, $value] = array_map('trim', explode(':', $line, 2));
            $field = strtolower($field);

            if ($field === 'user-agent') {
                if ($currentIndex === -1 || ! empty($groups[$currentIndex]['rules'])) {
                    $groups[] = ['uas' => [], 'rules' => []];
                    $currentIndex++;
                }
                $groups[$currentIndex]['uas'][] = strtolower($value);
            } elseif (in_array($field, ['disallow', 'allow'], true) && $currentIndex !== -1) {
                $groups[$currentIndex]['rules'][] = ['type' => $field, 'prefix' => $value];
            }
        }

        $uaLower = strtolower($userAgent);
        $matched = [];
        $wildcard = [];

        foreach ($groups as $group) {
            foreach ($group['uas'] as $ua) {
                if ($ua === '*') {
                    $wildcard = array_merge($wildcard, $group['rules']);
                } elseif (str_contains($uaLower, $ua)) {
                    $matched = array_merge($matched, $group['rules']);
                }
            }
        }

        return ! empty($matched) ? $matched : $wildcard;
    }
}
