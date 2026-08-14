<?php

namespace Honda\Catalog\Http;

/**
 * Enforces a minimum interval between requests. The sleep function is
 * injectable so tests can assert throttling logic without real sleeping.
 */
class ThrottleGate
{
    private ?float $lastRequestAt = null;

    /** @var callable(int): void */
    private $sleeper;

    public function __construct(
        private readonly float $requestsPerSecond = 1.5,
        ?callable $sleeper = null,
    ) {
        $this->sleeper = $sleeper ?? function (int $microseconds) {
            usleep($microseconds);
        };
    }

    public function wait(): void
    {
        if ($this->requestsPerSecond <= 0) {
            $this->lastRequestAt = microtime(true);

            return;
        }

        $minInterval = 1 / $this->requestsPerSecond;

        if ($this->lastRequestAt !== null) {
            $elapsed = microtime(true) - $this->lastRequestAt;
            $remaining = $minInterval - $elapsed;

            if ($remaining > 0) {
                ($this->sleeper)((int) ($remaining * 1_000_000));
            }
        }

        $this->lastRequestAt = microtime(true);
    }
}
