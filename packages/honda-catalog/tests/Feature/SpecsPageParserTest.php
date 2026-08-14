<?php

namespace Honda\Catalog\Tests\Feature;

use Honda\Catalog\Parsing\SpecsPageParser;
use Honda\Catalog\Tests\TestCase;

class SpecsPageParserTest extends TestCase
{
    private function parser(): SpecsPageParser
    {
        return new SpecsPageParser(config('honda-catalog.selectors.specs_page'));
    }

    public function test_it_resolves_variant_columns_from_the_variant_titles_row(): void
    {
        $data = $this->parser()->parse(
            $this->fixture('specs-page-crf450r.html'),
            'https://motorcycles.honda.com.au/models/offroad/competition/crf450r/specifications',
        );

        $this->assertCount(2, $data->variantColumns);
        $this->assertSame('CRF450RWE', $data->variantColumns[0]->name);
        $this->assertSame('CRF450R', $data->variantColumns[1]->name);
    }

    public function test_it_tracks_section_and_category_state_while_walking_rows(): void
    {
        $data = $this->parser()->parse(
            $this->fixture('specs-page-crf450r.html'),
            'https://motorcycles.honda.com.au/models/offroad/competition/crf450r/specifications',
        );

        $brakesF = collect($data->rows)->first(fn ($r) => $r->label === 'Brakes (F)');

        $this->assertNotNull($brakesF);
        $this->assertSame('Chassis', $brakesF->section);
        $this->assertSame('Brakes', $brakesF->category);
        $this->assertSame('1x 260mm disc', $brakesF->value);
    }

    public function test_it_does_not_emit_rows_for_category_placeholder_rows(): void
    {
        $data = $this->parser()->parse(
            $this->fixture('specs-page-crf450r.html'),
            'https://motorcycles.honda.com.au/models/offroad/competition/crf450r/specifications',
        );

        $labels = array_map(fn ($r) => $r->label, $data->rows);

        $this->assertNotContains('Brakes', $labels);
        $this->assertNotContains('Engine Type', $labels);
    }

    public function test_multi_variant_rows_resolve_variant_name_via_data_col(): void
    {
        $data = $this->parser()->parse(
            $this->fixture('specs-page-crf450r.html'),
            'https://motorcycles.honda.com.au/models/offroad/competition/crf450r/specifications',
        );

        $displacementRows = collect($data->rows)->filter(fn ($r) => $r->label === 'Displacement')->values();

        $this->assertCount(2, $displacementRows);
        $this->assertSame('CRF450RWE', $displacementRows[0]->variantName);
        $this->assertSame('CRF450R', $displacementRows[1]->variantName);
    }

    public function test_empty_value_cells_become_null_not_empty_string(): void
    {
        $data = $this->parser()->parse(
            $this->fixture('specs-page-crf450r.html'),
            'https://motorcycles.honda.com.au/models/offroad/competition/crf450r/specifications',
        );

        $launchControl = collect($data->rows)
            ->filter(fn ($r) => $r->label === 'Launch Control' && $r->variantName === 'CRF450R')
            ->first();

        $this->assertNotNull($launchControl);
        $this->assertNull($launchControl->value);
    }

    public function test_missing_table_returns_empty_data_without_throwing(): void
    {
        $data = $this->parser()->parse('<html><body>No specs here</body></html>', 'https://example.test/specs');

        $this->assertSame([], $data->variantColumns);
        $this->assertSame([], $data->rows);
    }
}
