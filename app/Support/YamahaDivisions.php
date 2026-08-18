<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Admin-controlled visibility for the top-level Yamaha product divisions
 * (Road, Off Road, ATV/ROV, Golf Car, Watercraft). Hiding a division here
 * removes it from the nav menu, homepage category tiles and sitemap — it
 * does not block direct access to the division's own pages.
 */
class YamahaDivisions
{
    public const SLUGS = ['road', 'off-road', 'atv-rov', 'golf-car', 'watercraft'];

    public static function isVisible(string $slug): bool
    {
        return Setting::get("yamaha_division_visible_{$slug}", '1') === '1';
    }

    public static function setVisible(string $slug, bool $visible): void
    {
        Setting::set("yamaha_division_visible_{$slug}", $visible ? '1' : '0');
    }

    /**
     * Filters an array keyed by division slug (e.g. config('yamaha_nav.groups')
     * or a GROUP_MAP-style label list) down to the divisions currently visible,
     * preserving the original order.
     */
    public static function filterVisible(array $keyedBySlug): array
    {
        return array_filter(
            $keyedBySlug,
            fn (string $slug) => self::isVisible($slug),
            ARRAY_FILTER_USE_KEY,
        );
    }
}
