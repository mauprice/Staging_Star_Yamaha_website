<?php

namespace App\Support;

class HtmlEntityDecoder
{
    /**
     * Decode HTML entities until the string is stable, with an iteration cap.
     *
     * html_entity_decode() is called repeatedly because the feed may double-encode
     * (&amp;#233; → &#233; → é). An iteration cap of 5 prevents infinite loops
     * on pathological input.
     */
    public static function decode(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $limit = 5;
        do {
            $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($decoded === $value) {
                break;
            }
            $value = $decoded;
        } while (--$limit > 0);

        return $value;
    }
}
