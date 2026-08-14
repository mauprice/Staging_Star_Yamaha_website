<?php

namespace Honda\Catalog\DataTransferObjects;

final readonly class FeatureBlock
{
    public function __construct(
        public int $sort,
        public string $heading,
        public ?string $body,
        public ?AssetRef $image,
    ) {}

    public function toArray(): array
    {
        return [
            'sort' => $this->sort,
            'heading' => $this->heading,
            'body' => $this->body,
            'image' => $this->image?->toArray(),
        ];
    }
}
