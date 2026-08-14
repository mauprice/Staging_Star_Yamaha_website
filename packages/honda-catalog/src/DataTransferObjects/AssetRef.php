<?php

namespace Honda\Catalog\DataTransferObjects;

use Honda\Catalog\Enums\AssetHost;

final readonly class AssetRef
{
    public function __construct(
        public string $guid,
        public string $sourceUrl,
        public ?string $versionHash,
        public AssetHost $host,
    ) {}

    public function toArray(): array
    {
        return [
            'guid' => $this->guid,
            'source_url' => $this->sourceUrl,
            'version_hash' => $this->versionHash,
            'host' => $this->host->value,
        ];
    }
}
