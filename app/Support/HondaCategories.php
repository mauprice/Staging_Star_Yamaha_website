<?php

namespace App\Support;

use App\Http\Controllers\HondaController;
use App\Models\Setting;
use Honda\Catalog\Models\HondaModel;

/**
 * Admin-controlled visibility for the top-level Honda product categories
 * (Off Road, On Road, Work Range). Hiding a category removes it from the
 * nav menu and the Honda Products landing page — it does not block direct
 * access to the category's own pages. Mirrors App\Support\YamahaDivisions.
 */
class HondaCategories
{
    public const SLUGS = ['offroad', 'onroad', 'workrange'];

    public static function isVisible(string $slug): bool
    {
        return Setting::get("honda_category_visible_{$slug}", '1') === '1';
    }

    public static function setVisible(string $slug, bool $visible): void
    {
        Setting::set("honda_category_visible_{$slug}", $visible ? '1' : '0');
    }

    /**
     * Visible categories with their subcategories, built live from whatever
     * models are actually in the catalog — Honda's taxonomy is scraped, not
     * a fixed config like Yamaha's, so a new subcategory picked up by a sync
     * (e.g. a new Work Range line) appears automatically once its parent
     * category is visible, with no extra admin step.
     *
     * @return array<string, array{label: string, subcategories: array<string, string>}>
     */
    public static function visibleGroups(): array
    {
        $rows = HondaModel::select('category', 'subcategory')->distinct()->get();

        $groups = [];
        foreach ($rows as $row) {
            if (! self::isVisible($row->category)) {
                continue;
            }

            $groups[$row->category]['label'] ??= HondaController::labelForCategory($row->category);
            $groups[$row->category]['subcategories'][$row->subcategory] = HondaController::labelForSubcategory($row->subcategory);
        }

        foreach ($groups as &$group) {
            ksort($group['subcategories']);
        }

        return $groups;
    }
}
