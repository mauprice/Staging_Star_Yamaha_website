<?php

namespace Honda\Catalog\DataTransferObjects;

final readonly class SpecsPageData
{
    /**
     * @param  VariantData[]  $variantColumns
     * @param  SpecRow[]  $rows
     */
    public function __construct(
        public string $slug,
        public string $sourceUrl,
        public array $variantColumns,
        public array $rows,
    ) {}

    public function toArray(): array
    {
        return [
            'variant_columns' => array_map(fn (VariantData $v) => $v->toArray(), $this->variantColumns),
            'rows' => array_map(fn (SpecRow $r) => $r->toArray(), $this->rows),
        ];
    }
}
