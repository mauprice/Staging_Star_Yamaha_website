<?php

namespace Honda\Catalog\Tests\Feature;

use Honda\Catalog\Parsing\OfferPageParser;
use Honda\Catalog\Tests\TestCase;

class OfferPageParserTest extends TestCase
{
    private function parser(): OfferPageParser
    {
        return new OfferPageParser(config('honda-catalog.selectors.offer_page'));
    }

    public function test_it_parses_top_level_offer_cards_in_order(): void
    {
        $blocks = $this->parser()->parse(
            $this->fixture('offers-page.html'),
            'https://motorcycles.honda.com.au/offers',
        );

        $this->assertCount(2, $blocks);

        $this->assertSame('Ready to level up?', $blocks[0]->title);
        $this->assertStringContainsString('1% p.a comparison rate finance', $blocks[0]->subtitle);
        $this->assertNull($blocks[0]->priceLabel);
        $this->assertStringContainsString('CRF250R', $blocks[0]->bodyHtml);
        $this->assertNotNull($blocks[0]->image);
        $this->assertSame('/models/offroad/competition', $blocks[0]->ctaUrl);
        $this->assertSame('View the range', $blocks[0]->ctaLabel);
        $this->assertSame(0, $blocks[0]->sort);

        $this->assertSame('Honda Runout Models', $blocks[1]->title);
        $this->assertSame('/offers/model-runout', $blocks[1]->ctaUrl);
        $this->assertSame(1, $blocks[1]->sort);
    }

    public function test_it_parses_a_price_label_when_present(): void
    {
        $blocks = $this->parser()->parse(
            $this->fixture('offer-runout-page.html'),
            'https://motorcycles.honda.com.au/offers/model-runout',
        );

        $this->assertSame('CMX500', $blocks[0]->title);
        $this->assertSame('25 YM', $blocks[0]->subtitle);
        $this->assertSame('NOW FROM $9,814*', $blocks[0]->priceLabel);
        $this->assertSame('/models/onroad/street/cmx500', $blocks[0]->ctaUrl);
    }

    public function test_a_card_with_no_cta_link_degrades_to_a_null_cta_rather_than_throwing(): void
    {
        // Real Honda runout listings include discontinued models that have
        // no live showroom page to link to - the CTA row is present but
        // empty in the static markup.
        $blocks = $this->parser()->parse(
            $this->fixture('offer-runout-page.html'),
            'https://motorcycles.honda.com.au/offers/model-runout',
        );

        $this->assertSame('CRF300 Rally', $blocks[1]->title);
        $this->assertSame('NOW FROM $8,926*', $blocks[1]->priceLabel);
        $this->assertNull($blocks[1]->ctaUrl);
        $this->assertNull($blocks[1]->ctaLabel);
    }
}
