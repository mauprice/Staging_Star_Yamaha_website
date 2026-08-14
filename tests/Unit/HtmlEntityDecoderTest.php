<?php

namespace Tests\Unit;

use App\Support\HtmlEntityDecoder;
use Tests\TestCase;

class HtmlEntityDecoderTest extends TestCase
{
    public function test_decodes_named_entities(): void
    {
        $this->assertSame('Ténéré', HtmlEntityDecoder::decode('T&eacute;n&eacute;r&eacute;'));
    }

    public function test_decodes_numeric_entities(): void
    {
        $this->assertSame('Ténéré', HtmlEntityDecoder::decode('T&#233;n&#233;r&#233;'));
    }

    public function test_decodes_double_encoded_entities(): void
    {
        // &amp;#233; → &#233; → é  (two passes required)
        $this->assertSame('Ténéré', HtmlEntityDecoder::decode('T&amp;#233;n&amp;#233;r&amp;#233;'));
    }

    public function test_decodes_apostrophe_entity(): void
    {
        $this->assertSame("Industry's First", HtmlEntityDecoder::decode('Industry&#39;s First'));
    }

    public function test_decodes_double_encoded_apostrophe(): void
    {
        $this->assertSame("Industry's First", HtmlEntityDecoder::decode('Industry&amp;#39;s First'));
    }

    public function test_returns_null_for_null_input(): void
    {
        $this->assertNull(HtmlEntityDecoder::decode(null));
    }

    public function test_returns_plain_string_unchanged(): void
    {
        $this->assertSame('No entities here', HtmlEntityDecoder::decode('No entities here'));
    }

    public function test_is_idempotent_on_clean_utf8(): void
    {
        $clean = 'Ténéré Rally 2025';
        $this->assertSame($clean, HtmlEntityDecoder::decode($clean));
    }
}
