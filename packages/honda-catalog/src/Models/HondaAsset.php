<?php

namespace Honda\Catalog\Models;

use Honda\Catalog\Enums\AssetHost;
use Honda\Catalog\Enums\AssetStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HondaAsset extends Model
{
    protected $table = 'honda_assets';

    protected $fillable = [
        'guid', 'source_url', 'version_hash', 'host', 'mime', 'bytes',
        'local_path', 'storage_disk', 'checksum', 'status', 'last_fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'host' => AssetHost::class,
            'status' => AssetStatus::class,
            'bytes' => 'integer',
            'last_fetched_at' => 'datetime',
        ];
    }

    public function models()
    {
        return $this->belongsToMany(HondaModel::class, 'honda_model_asset', 'asset_id', 'model_id')
            ->withPivot(['role', 'sort'])
            ->withTimestamps();
    }

    /**
     * Returns the correct URL regardless of asset strategy, driven by this
     * row's own status (not the current global config) so a row mirrored
     * under an old config, or one that failed mid-mirror, still resolves
     * correctly.
     */
    public function url(): string
    {
        if ($this->status === AssetStatus::Mirrored && $this->local_path && $this->storage_disk) {
            return Storage::disk($this->storage_disk)->url($this->local_path);
        }

        return $this->version_hash
            ? "{$this->source_url}?v={$this->version_hash}"
            : $this->source_url;
    }
}
