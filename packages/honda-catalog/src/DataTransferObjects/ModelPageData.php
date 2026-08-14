<?php

namespace Honda\Catalog\DataTransferObjects;

final readonly class ModelPageData
{
    /**
     * @param  FeatureBlock[]  $features
     * @param  VariantData[]  $variants
     * @param  ColourData[]  $colours
     * @param  AssetRef[]  $galleryImages
     */
    public function __construct(
        public string $slug,
        public string $category,
        public string $subcategory,
        public string $name,
        public ?string $tagline,
        public ?string $descriptionHtml,
        public ?int $priceFromCents,
        public string $priceCurrency,
        public ?string $priceLabel,
        public string $sourceUrl,
        public ?AssetRef $ogImage,
        public ?string $pricingModelId,
        public array $features,
        public array $variants,
        public array $colours,
        public array $galleryImages,
    ) {}

    public function toArray(): array
    {
        return [
            'slug' => $this->slug,
            'category' => $this->category,
            'subcategory' => $this->subcategory,
            'name' => $this->name,
            'tagline' => $this->tagline,
            'description_html' => $this->descriptionHtml,
            'price_from_cents' => $this->priceFromCents,
            'price_currency' => $this->priceCurrency,
            'price_label' => $this->priceLabel,
            'og_image' => $this->ogImage?->toArray(),
            'features' => array_map(fn (FeatureBlock $f) => $f->toArray(), $this->features),
            'variants' => array_map(fn (VariantData $v) => $v->toArray(), $this->variants),
            'colours' => array_map(fn (ColourData $c) => $c->toArray(), $this->colours),
            'gallery_images' => array_map(fn (AssetRef $a) => $a->toArray(), $this->galleryImages),
        ];
    }
}
