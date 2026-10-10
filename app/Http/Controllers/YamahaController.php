<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\YamahaProduct;
use App\Models\YamahaPromotion;
use App\Support\HondaCategories;
use App\Support\YamahaCdn;
use Honda\Catalog\Models\HondaModel;
use Honda\Catalog\Models\HondaOffer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
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
        $promotions = YamahaPromotion::where('is_visible', true)
            ->where(function ($query) {
                $query->whereNull('type')->orWhere('type', 'not like', 'Outboard%');
            })
            ->orderBy('display_order')
            ->get();

        // Admin-controlled from the "Homepage Sliders" settings page — lets the
        // site administrator turn each promotional source on/off in the hero
        // slider without touching the underlying data.
        $showYamahaSlides = Setting::get('slider_show_yamaha_promotions', '1') === '1';
        $showHondaSlides  = Setting::get('slider_show_honda_offers', '1') === '1';
        $showSpecialsOffers = Setting::get('homepage_show_specials_offers', '1') === '1';

        // Yamaha promo images are wide banners cut for this slider, so they crop
        // to fill it. Honda offer images are square social-tile graphics with
        // baked-in text, so cropping them chops off the copy — those letterbox
        // instead (see 'fit' in yamaha.index).
        $slides = new Collection();

        if ($showYamahaSlides) {
            $slides = $slides->concat($promotions->whereNotNull('image')->map(fn (YamahaPromotion $promo) => (object) [
                'image' => $promo->image,
                'head'  => $promo->head,
                'brief' => $promo->brief,
                'type'  => $promo->type,
                'link'  => route('yamaha.specials'),
                'fit'   => 'cover',
            ]));
        }

        if ($showHondaSlides) {
            $hondaOffers = HondaOffer::whereNull('parent_id')
                ->where('is_active', true)
                ->where('show_in_homepage_slider', true)
                ->whereNotNull('image_asset_id')
                ->with('image')
                ->orderBy('sort')
                ->take(6)
                ->get();

            $slides = $slides->concat($hondaOffers->map(fn (HondaOffer $offer) => (object) [
                'image' => $offer->image->url(),
                'head'  => $offer->title,
                'brief' => $offer->subtitle,
                'type'  => 'Honda Offer',
                'link'  => $offer->link_url,
                'fit'   => 'contain',
            ]));
        }

        $slides = $slides->values();

        $groupPreviews = [];
        foreach (\App\Support\YamahaDivisions::filterVisible(self::GROUP_MAP) as $slug => $group) {
            $image = $this->previewImage(YamahaProduct::where('product_group', $group));
            if ($image) {
                $groupPreviews[$slug] = [
                    'label' => $group,
                    'image' => $image,
                ];
            }
        }

        $hondaCategoryPreviews = [];
        foreach (HondaCategories::visibleGroups() as $slug => $group) {
            $model = HondaModel::where('category', $slug)->with('ogImage')->first();
            if ($model) {
                $hondaCategoryPreviews[$slug] = [
                    'label' => $group['label'],
                    'image' => $model->ogImage?->url(),
                ];
            }
        }

        return view('yamaha.index', compact('promotions', 'groupPreviews', 'hondaCategoryPreviews', 'slides', 'showSpecialsOffers'));
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
                $image = $this->previewImage($q);
                if ($image) {
                    $previews[ucfirst($slug)] = $image;
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
            $image = $this->previewImage(
                YamahaProduct::where('product_group', $groupName)->where('sub_category', $subCat)
            );
            if ($image) {
                $previews[$subCat] = $image;
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

        // One slide per product, using its primary hero banner where one exists —
        // pulling every hero banner per product dragged in secondary/dead CDN shots
        // as blank/duplicate slides. Falls back to summary_image/color image (same
        // priority as the product card) because some products have no image_type=1
        // banner at all, which would otherwise leave that slide blank. Each
        // candidate is checked against YamahaCdn::isLive() first — several synced
        // rows still carry image URLs on Yamaha's decommissioned CDN, which would
        // otherwise render as a blank slide despite "having" an image.
        $sliderImages = $products
            ->map(function ($p) {
                $banner = $p->heroBanners->first();
                $bannerImage = YamahaCdn::liveOrNull($banner?->image);

                if ($bannerImage) {
                    return [
                        'mobile'  => $banner->image_mobile,
                        'tablet'  => $banner->image_tablet,
                        'desktop' => $bannerImage,
                    ];
                }

                $fallback = YamahaCdn::liveOrNull($p->summary_image)
                    ?? YamahaCdn::liveOrNull($p->colors->first()?->color_image);

                if (! $fallback) {
                    return null;
                }

                return [
                    'mobile'  => null,
                    'tablet'  => null,
                    'desktop' => $fallback,
                ];
            })
            ->filter()
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

    /**
     * Two separate gaps can leave a preview tile blank: a product with no
     * image data at all (acknowledged in SyncYamahaProducts' local-fallback-
     * image comment), or a product whose banner/summary image is still on
     * Yamaha's decommissioned www.yamaha-motor.com.au CDN (every path on
     * that host now 404s/403s, confirmed directly) rather than the live
     * Dealers-API scene7 CDN. Scans for the first product with a genuinely
     * live image instead of just taking whichever one sorts first.
     */
    private function previewImage(Builder $query): ?string
    {
        $product = $query->with('heroBanners')->get()
            ->first(fn (YamahaProduct $p) => $this->liveImage($p->heroBanners->first()?->image) ?? $this->liveImage($p->summary_image));

        return $product ? ($this->liveImage($product->heroBanners->first()?->image) ?? $this->liveImage($product->summary_image)) : null;
    }

    private function liveImage(?string $url): ?string
    {
        return YamahaCdn::liveOrNull($url);
    }
}
