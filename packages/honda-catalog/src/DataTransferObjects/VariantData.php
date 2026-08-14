<?php

namespace Honda\Catalog\DataTransferObjects;

final readonly class VariantData
{
    public function __construct(
        public string $name,
        public ?int $priceCents,
        public int $sort,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'price_cents' => $this->priceCents,
            'sort' => $this->sort,
        ];
    }
}
