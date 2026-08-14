<?php

namespace App\Http\Controllers;

use App\Models\YamahaProduct;
use App\Models\YamahaPromotion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class YamahaController extends Controller
{
    // Maps URL slug → API product_group value
    // Note: Generator excluded — not currently available in the Yamaha AU API feed
    private const GROUP_MAP = [
        'road'       => 'Road',
        'off-road'   => 'Off Road',
        'atv-rov'    => 'ATV/ROV',
        'golf-car'   => 'Golf Car',
        'watercraft' => 'Watercraft',
    ];

    // Golf Car is split across two product_groups in the API (Golf Car + Utility)
    // so we query the whole division and treat product_group as the category.
    // Maps category slug → product_group value for Golf Car division
    private const GOLF_CAR_CATEGORIES = [
        'electric' => ['product_group' => 'Golf Car', 'sub_category' => 'Electric'],
        'petrol'   => ['product_group' => 'Golf Car', 'sub_category' => 'Petrol'],
        'utility'  => ['product_group' => 'Utility',  'sub_category' => null],
    ];

    // Maps URL slug → exact sub_category value stored in DB
    private const CATEGORY_MAP = [
        // Road
        'supersport'         => 'Supersport',
        'maximum-torque'     => 'Maximum Torque',
        'sport-heritage'     => 'Sport Heritage',
        'sport-touring'      => 'Sport Touring',
        'scooter'            => 'Scooter',
        // Off Road
        'motocross'          => 'Motocross',
        'enduro'             => 'Enduro',
        'fun'                => 'Fun',
        'adventure'          => 'Adventure',
        'agriculture'        => 'Agriculture',
        // ATV/ROV
        'sport-atv'          => 'Sport ATV',
        'fun-atv'            => 'Fun ATV',
        'sport-rov'          => 'Sport ROV',
        'utility-rov'        => 'Utility ROV',
        // Watercraft
        'recreational'       => 'Recreational',
        'luxury-performance' => 'Luxury Performance',
        'race-inspired'      => 'Race Inspired',
        'freestyle'          => 'Freestyle',
        'jetfish'            => 'Jetfish',
    ];

    public function index(): View
    {
        $promotions = YamahaPromotion::where('active', true)
            ->where('type', '!=', 'Outboard')
            ->orderBy('sort_index')
            ->get();

        $groupPreviews = [];
        foreach (self::GROUP_MAP as $slug => $group) {
            $product = YamahaProduct::where('product_group', $group)
                ->with('heroBanners')
                ->first();
            if ($product) {
                $groupPreviews[$slug] = [
                    'label' => $group,
                    'image' => $product->heroBanners->first()?->image ?? $product->summary_image,
                ];
            }
        }

        return view('yamaha.index', compact('promotions', 'groupPreviews'));
    }

    public function group(string $group): View
    {
        $groupName = self::GROUP_MAP[$group] ?? null;

        if (! $groupName) {
            abort(404);
        }

        // Golf Cars span two product_groups — build categories manually
        if ($group === 'golf-car') {
            $subCategories = collect(array_keys(self::GOLF_CAR_CATEGORIES))
                ->map(fn ($slug) => ucfirst($slug));

            $previews = [];
            foreach (self::GOLF_CAR_CATEGORIES as $slug => $filter) {
                $q = YamahaProduct::where('division', 'Golf Car')
                    ->where('product_group', $filter['product_group']);
                if ($filter['sub_category']) {
                    $q->where('sub_category', $filter['sub_category']);
                }
                $product = $q->with('heroBanners')->first();
                if ($product) {
                    $previews[ucfirst($slug)] = $product->heroBanners->first()?->image ?? $product->summary_image;
                }
            }

            // Use slugs as keys so the view can build correct URLs
            $subCategories = collect(array_keys(self::GOLF_CAR_CATEGORIES))
                ->mapWithKeys(fn ($slug) => [$slug => ucfirst($slug)]);

            return view('yamaha.group', [
                'group'         => $group,
                'groupName'     => 'Golf Car',
                'subCategories' => $subCategories,
                'previews'      => $previews,
                'useSlugKeys'   => true,
            ]);
        }

        $subCategories = YamahaProduct::where('product_group', $groupName)
            ->select('sub_category')
            ->whereNotNull('sub_category')
            ->distinct()
            ->orderBy('sub_category')
            ->pluck('sub_category');

        $previews = [];
        foreach ($subCategories as $subCat) {
            $product = YamahaProduct::where('product_group', $groupName)
                ->where('sub_category', $subCat)
                ->with('heroBanners')
                ->first();
            if ($product) {
                $previews[$subCat] = $product->heroBanners->first()?->image ?? $product->summary_image;
            }
        }

        return view('yamaha.group', compact('group', 'groupName', 'subCategories', 'previews'));
    }

    public function category(string $group, string $category): View
    {
        $groupName = self::GROUP_MAP[$group] ?? null;

        if (! $groupName) {
            abort(404);
        }

        // Golf Car uses a special per-category filter
        if ($group === 'golf-car') {
            $filter = self::GOLF_CAR_CATEGORIES[$category] ?? null;
            if (! $filter) {
                abort(404);
            }

            $query = YamahaProduct::where('division', 'Golf Car')
                ->where('product_group', $filter['product_group']);

            if ($filter['sub_category']) {
                $query->where('sub_category', $filter['sub_category']);
            }

            $subCategoryName = ucfirst($category);

        } else {
            $subCategoryName = $this->slugToCategory($category);

            $query = YamahaProduct::where('product_group', $groupName)
                ->where('sub_category', $subCategoryName);
        }

        $products = $query->with(['heroBanners', 'colors'])->orderBy('model_name')->get();

        if ($products->isEmpty()) {
            abort(404);
        }

        // Collect all hero banners across every product in this category for the slider,
        // keeping the mobile/tablet/desktop crop variants together so the view can
        // render a responsive <picture> instead of forcing the desktop crop everywhere.
        $sliderImages = $products
            ->flatMap(fn ($p) => $p->heroBanners)
            ->filter(fn ($banner) => $banner->image)
            ->map(fn ($banner) => [
                'mobile'  => $banner->image_mobile,
                'tablet'  => $banner->image_tablet,
                'desktop' => $banner->image,
            ])
            ->values();

        return view('yamaha.category', compact('group', 'groupName', 'category', 'subCategoryName', 'products', 'sliderImages'));
    }

    public function product(string $group, string $category, string $slug): View
    {
        $groupName = self::GROUP_MAP[$group] ?? null;

        if (! $groupName) {
            abort(404);
        }

        if ($group === 'golf-car') {
            $filter = self::GOLF_CAR_CATEGORIES[$category] ?? null;
            if (! $filter) {
                abort(404);
            }

            $query = YamahaProduct::where('division', 'Golf Car')
                ->where('product_group', $filter['product_group']);

            if ($filter['sub_category']) {
                $query->where('sub_category', $filter['sub_category']);
            }

            $subCategoryName = ucfirst($category);

        } else {
            $subCategoryName = $this->slugToCategory($category);

            $query = YamahaProduct::where('product_group', $groupName)
                ->where('sub_category', $subCategoryName);
        }

        $product = $query->with(['banners', 'colors', 'features', 'images'])
            ->get()
            ->first(fn ($p) => $p->slug === $slug);

        if (! $product) {
            abort(404);
        }

        return view('yamaha.product', compact('product', 'group', 'groupName', 'category', 'subCategoryName'));
    }

    private function slugToCategory(string $slug): string
    {
        return self::CATEGORY_MAP[$slug] ?? ucwords(str_replace('-', ' ', $slug));
    }
}
