<?php

namespace Honda\Parts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssemblyImage extends Model
{
    protected $table = 'honda_parts_assembly_images';
    public $timestamps = false;
    protected $primaryKey = 'assembly_image_id';
    public $incrementing = false;

    public function assembly(): BelongsTo
    {
        return $this->belongsTo(Assembly::class, 'assembly_id', 'assembly_id');
    }

    /**
     * Honda's Image table keys on (catalogue_id, image_id) — image_id alone
     * repeats across catalogues, so this can never be a normal belongsTo.
     * Callers resolving a whole assembly's images should batch via
     * Image::whereIn and match pairs in memory rather than calling this
     * once per row (see HondaPartsCatalogueController::showAssembly).
     */
    public function resolveImage(): ?Image
    {
        $catalogueId = $this->assembly?->catalogue_id;
        if (!$catalogueId) {
            return null;
        }

        return Image::where('catalogue_id', $catalogueId)
            ->where('image_id', $this->image_id)
            ->first();
    }
}
