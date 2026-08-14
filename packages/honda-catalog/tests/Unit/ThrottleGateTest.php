<?php

namespace Honda\Catalog\Tests\Unit;

use Honda\Catalog\Http\ThrottleGate;
use PHPUnit\Framework\TestCase;

class ThrottleGateTest extends TestCase
{
    public function test_first_call_never_sleeps(): void
    {
        $slept = [];
        $gate = new ThrottleGate(1.0, function (int $microseconds) use (&$slept) {
            $slept[] = $microseconds;
        });

        $gate->wait();

        $this->assertSame([], $slept);
    }

    public function test_second_call_sleeps_for_roughly_the_configured_interval(): void
    {
        $slept = [];
        $gate = new ThrottleGate(2.0, function (int $microseconds) use (&$slept) {
            $slept[] = $microseconds;
        });

        $gate->wait();
        $gate->wait();

        $this->assertCount(1, $slept);
        // 2 req/sec => 500ms interval; allow generous tolerance for test execution jitter.
        $this->assertGreaterThan(0, $slept[0]);
        $this->assertLessThanOrEqual(500_000, $slept[0]);
    }

    public function test_zero_requests_per_second_disables_throttling(): void
    {
        $slept = [];
        $gate = new ThrottleGate(0, function (int $microseconds) use (&$slept) {
            $slept[] = $microseconds;
        });

        $gate->wait();
        $gate->wait();

        $this->assertSame([], $slept);
    }
}
