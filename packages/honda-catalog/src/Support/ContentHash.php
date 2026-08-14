<?php

namespace Honda\Catalog\Support;

use Honda\Catalog\DataTransferObjects\ModelPageData;
use Honda\Catalog\DataTransferObjects\SpecsPageData;

final class ContentHash
{
    /**
     * Hashes the canonicalized content of both DTOs, including asset GUID +
     * version hashes (so an image-only upstream change still triggers a
     * re-sync) but excluding the raw source URL, which is constant per slug
     * and carries no signal about whether anything changed.
     */
    public static function compute(ModelPageData $model, SpecsPageData $specs): string
    {
        $modelArray = $model->toArray();
        unset($modelArray['slug']);

        $payload = [
            'model' => $modelArray,
            'specs' => $specs->toArray(),
        ];

        self::ksortRecursive($payload);

        return hash('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private static function ksortRecursive(array &$array): void
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                self::ksortRecursive($value);
            }
        }

        if (! array_is_list($array)) {
            ksort($array);
        }
    }
}
