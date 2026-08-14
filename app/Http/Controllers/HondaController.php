<?php

namespace App\Http\Controllers;

use Honda\Catalog\Models\HondaModel;
use Illuminate\View\View;

class HondaController extends Controller
{
    private const CATEGORY_LABELS = [
        'offroad' => 'Off Road',
        'onroad' => 'On Road',
        'workrange' => 'Work Range',
    ];

    private const SUBCATEGORY_LABELS = [
        'competition' => 'Competition',
        'kidsfunbikes' => 'Kids & Fun Bikes',
        'sportsxs' => 'Sport SXS',
        'trail' => 'Trail',
        'adventuretouring' => 'Adventure Touring',
        'scooter' => 'Scooter',
        'street' => 'Street',
        'supersport' => 'Supersport',
        'touring' => 'Touring',
        'agriculturalbikes' => 'Agricultural Bikes',
        'sxs' => 'Side x Side',
    ];

    public function index(): View
    {
        $categories = HondaModel::select('category')->distinct()->pluck('category');

        $categoryPreviews = [];
        foreach ($categories as $cat) {
            $model = HondaModel::where('category', $cat)->with('ogImage')->first();

            if ($model) {
                $categoryPreviews[$cat] = [
                    'label' => self::labelForCategory($cat),
                    'image' => $model->ogImage?->url(),
                    'count' => HondaModel::where('category', $cat)->count(),
                ];
            }
        }

        return view('honda.index', compact('categoryPreviews'));
    }

    public function category(string $category): View
    {
        $subcategories = HondaModel::where('category', $category)
            ->select('subcategory')
            ->distinct()
            ->orderBy('subcategory')
            ->pluck('subcategory');

        if ($subcategories->isEmpty()) {
            abort(404);
        }

        $categoryLabel = self::labelForCategory($category);

        $previews = [];
        $subcategoryLabels = [];
        foreach ($subcategories as $sub) {
            $model = HondaModel::where('category', $category)->where('subcategory', $sub)->with('ogImage')->first();
            $previews[$sub] = $model?->ogImage?->url();
            $subcategoryLabels[$sub] = self::labelForSubcategory($sub);
        }

        return view('honda.category', compact('category', 'categoryLabel', 'subcategories', 'previews', 'subcategoryLabels'));
    }

    public function subcategory(string $category, string $subcategory): View
    {
        $models = HondaModel::where('category', $category)
            ->where('subcategory', $subcategory)
            ->with(['ogImage', 'colours.image'])
            ->orderBy('name')
            ->get();

        if ($models->isEmpty()) {
            abort(404);
        }

        $categoryLabel = self::labelForCategory($category);
        $subcategoryLabel = self::labelForSubcategory($subcategory);

        return view('honda.subcategory', compact('category', 'categoryLabel', 'subcategory', 'subcategoryLabel', 'models'));
    }

    public function product(string $category, string $subcategory, string $slug): View
    {
        $model = HondaModel::where('slug', $slug)
            ->where('category', $category)
            ->where('subcategory', $subcategory)
            ->with([
                'ogImage',
                'features.image',
                'variants',
                'colours.image',
                'specifications.variant',
                'assets',
            ])
            ->first();

        if (! $model) {
            abort(404);
        }

        $categoryLabel = self::labelForCategory($category);
        $subcategoryLabel = self::labelForSubcategory($subcategory);
        $specGroups = $this->buildSpecGroups($model);

        return view('honda.product', compact(
            'model', 'category', 'categoryLabel', 'subcategory', 'subcategoryLabel', 'specGroups',
        ));
    }

    /**
     * Shapes flat spec rows into section -> category -> label -> value(s).
     * A label collapses to a single value when every variant agrees; when
     * variants genuinely differ (e.g. displacement varies by engine option)
     * it keeps a variant name => value map so the view can show both.
     */
    private function buildSpecGroups(HondaModel $model): array
    {
        $groups = [];

        foreach ($model->specifications as $spec) {
            $variantName = $spec->variant?->name;
            $groups[$spec->section][$spec->category][$spec->label][$variantName ?? '_'] = $spec->value;
        }

        foreach ($groups as $section => $categories) {
            foreach ($categories as $category => $labels) {
                foreach ($labels as $label => $values) {
                    $unique = array_unique($values);
                    $groups[$section][$category][$label] = count($unique) === 1
                        ? reset($unique)
                        : $values;
                }
            }
        }

        return $groups;
    }

    public static function labelForCategory(string $category): string
    {
        return self::CATEGORY_LABELS[$category] ?? ucwords($category);
    }

    public static function labelForSubcategory(string $subcategory): string
    {
        return self::SUBCATEGORY_LABELS[$subcategory] ?? ucwords(str_replace('-', ' ', $subcategory));
    }
}
