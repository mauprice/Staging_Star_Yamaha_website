<?php

namespace Honda\Catalog\DataTransferObjects;

final readonly class ColourData
{
    public function __construct(
        public string $name,
        public ?string $hex,
        public ?AssetRef $image,
        public int $sort,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'hex' => $this->hex,
            'image' => $this->image?->toArray(),
            'sort' => $this->sort,
        ];
    }
}
