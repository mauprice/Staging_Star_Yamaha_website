<?php

namespace Honda\Catalog\DataTransferObjects;

final readonly class SpecRow
{
    public function __construct(
        public string $section,
        public string $category,
        public string $label,
        public ?string $value,
        public ?string $variantName,
        public int $sort,
    ) {}

    public function toArray(): array
    {
        return [
            'section' => $this->section,
            'category' => $this->category,
            'label' => $this->label,
            'value' => $this->value,
            'variant_name' => $this->variantName,
            'sort' => $this->sort,
        ];
    }
}
