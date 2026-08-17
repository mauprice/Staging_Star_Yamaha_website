<?php

namespace Honda\Catalog\DataTransferObjects;

final readonly class OfferBlockData
{
    public function __construct(
        public string $title,
        public ?string $subtitle,
        public ?string $priceLabel,
        public ?string $bodyHtml,
        public ?AssetRef $image,
        public ?string $ctaUrl,
        public ?string $ctaLabel,
        public int $sort,
    ) {}

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'price_label' => $this->priceLabel,
            'body_html' => $this->bodyHtml,
            'image' => $this->image?->toArray(),
            'cta_url' => $this->ctaUrl,
            'cta_label' => $this->ctaLabel,
            'sort' => $this->sort,
        ];
    }
}
