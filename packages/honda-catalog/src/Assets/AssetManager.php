<?php

namespace Honda\Catalog\Assets;

use Honda\Catalog\DataTransferObjects\AssetRef;
use Honda\Catalog\Enums\AssetStatus;
use Honda\Catalog\Http\ThrottledHttpClient;
use Honda\Catalog\Models\HondaAsset;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AssetManager
{
    public function __construct(
        private readonly ThrottledHttpClient $http,
        private readonly array $config = [],
    ) {}

    /**
     * Records an asset reference. 'cdn' strategy just stores it as a remote
     * reference; 'mirror' strategy downloads it to the configured disk.
     * Download failures degrade to status=Failed (serving the remote URL
     * via HondaAsset::url()) rather than throwing, so one bad asset never
     * fails the whole model ingest.
     */
    public function record(AssetRef $ref, string $strategy): HondaAsset
    {
        $asset = HondaAsset::firstOrNew(['guid' => $ref->guid]);
        $previousVersionHash = $asset->version_hash;
        $wasMirrored = $asset->status === AssetStatus::Mirrored;

        $asset->fill([
            'source_url' => $ref->sourceUrl,
            'version_hash' => $ref->versionHash,
            'host' => $ref->host,
        ]);

        if ($strategy !== 'mirror') {
            $asset->status = AssetStatus::Remote;
            $asset->save();

            return $asset;
        }

        if ($wasMirrored && $previousVersionHash === $ref->versionHash) {
            return $asset;
        }

        try {
            $response = $this->http->get($ref->sourceUrl);
            $bytes = (string) $response->getBody();
            $path = $this->buildPath($ref);
            $disk = $this->config['disk'] ?? 'public';

            Storage::disk($disk)->put($path, $bytes);

            $asset->fill([
                'local_path' => $path,
                'storage_disk' => $disk,
                'checksum' => hash('sha256', $bytes),
                'bytes' => strlen($bytes),
                'mime' => $response->getHeaderLine('Content-Type') ?: null,
                'status' => AssetStatus::Mirrored,
                'last_fetched_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::warning('honda-catalog: asset mirror failed, falling back to remote', [
                'guid' => $ref->guid,
                'error' => $e->getMessage(),
            ]);
            $asset->status = AssetStatus::Failed;
        }

        $asset->save();

        return $asset;
    }

    private function buildPath(AssetRef $ref): string
    {
        $prefix = trim($this->config['path_prefix'] ?? 'honda-catalog', '/');
        $path = parse_url($ref->sourceUrl, PHP_URL_PATH) ?: '';
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $ext = $ext !== '' ? $ext : 'jpg';

        return "{$prefix}/{$ref->guid}.{$ext}";
    }
}
