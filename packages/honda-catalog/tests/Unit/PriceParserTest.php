<?php

namespace Honda\Catalog\Tests\Unit;

use Honda\Catalog\Parsing\Support\PriceParser;
use PHPUnit\Framework\TestCase;

class PriceParserTest extends TestCase
{
    public function test_it_extracts_cents_and_label_from_a_price_string(): void
    {
        $result = PriceParser::toCents('Ride Away $12,999');

        $this->assertSame(1299900, $result['cents']);
        $this->assertSame('Ride Away', $result['label']);
    }

    public function test_it_handles_the_number_appearing_first(): void
    {
        $result = PriceParser::toCents('$8,499 Ride Away');

        $this->assertSame(849900, $result['cents']);
        $this->assertSame('Ride Away', $result['label']);
    }

    public function test_it_returns_null_cents_for_empty_string(): void
    {
        $result = PriceParser::toCents('');

        $this->assertNull($result['cents']);
        $this->assertNull($result['label']);
    }

    public function test_it_returns_null_cents_for_null_input(): void
    {
        $result = PriceParser::toCents(null);

        $this->assertNull($result['cents']);
        $this->assertNull($result['label']);
    }

    public function test_it_never_throws_on_non_numeric_text(): void
    {
        $result = PriceParser::toCents('Contact your dealer');

        $this->assertNull($result['cents']);
        $this->assertSame('Contact your dealer', $result['label']);
    }

    public function test_it_handles_decimal_values(): void
    {
        $result = PriceParser::toCents('$1,234.50');

        $this->assertSame(123450, $result['cents']);
    }
}
