<?php

namespace Honda\Catalog\Http;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Honda\Catalog\Http\Exceptions\FetchException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ThrottledHttpClient
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly RobotsTxtChecker $robots,
        private readonly ThrottleGate $throttle,
        private readonly array $config = [],
    ) {}

    public function get(string $url): ResponseInterface
    {
        return $this->send('GET', $url, [
            'headers' => ['User-Agent' => $this->config['user_agent'] ?? 'HondaCatalogBot/1.0'],
        ]);
    }

    /**
     * @param  array<mixed>  $json
     */
    public function post(string $url, array $json): ResponseInterface
    {
        return $this->send('POST', $url, [
            'headers' => ['User-Agent' => $this->config['user_agent'] ?? 'HondaCatalogBot/1.0'],
            'json' => $json,
        ]);
    }

    private function send(string $method, string $url, array $options): ResponseInterface
    {
        $this->robots->assertAllowed($url);
        $this->throttle->wait();

        $times = $this->config['retry_times'] ?? 3;
        $backoffBase = $this->config['retry_backoff_base'] ?? 2.0;
        $attempt = 0;
        $lastException = null;

        $options['timeout'] = $this->config['timeout'] ?? 15;

        while ($attempt <= $times) {
            try {
                return $this->client->request($method, $url, $options);
            } catch (Throwable $e) {
                $lastException = $e;

                if (! $this->isRetryable($e) || $attempt === $times) {
                    break;
                }

                usleep((int) (1_000_000 * ($backoffBase ** $attempt)));
                $attempt++;
            }
        }

        throw new FetchException("Failed to {$method} {$url}: {$lastException?->getMessage()}", previous: $lastException);
    }

    private function isRetryable(Throwable $e): bool
    {
        if ($e instanceof ConnectException) {
            return true;
        }

        if ($e instanceof RequestException) {
            $status = $e->getResponse()?->getStatusCode();

            return $status !== null && $status >= 500;
        }

        return false;
    }
}
