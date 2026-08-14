<?php

namespace App\Http\Controllers;

use App\Models\PreOwnedListing;
use App\Models\Special;
use App\Models\YamahaProduct;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    private const GROUP_MAP = [
        'road'       => 'Road',
        'off-road'   => 'Off Road',
        'atv-rov'    => 'ATV/ROV',
        'golf-car'   => 'Golf Car',
        'watercraft' => 'Watercraft',
    ];

    private const CATEGORY_MAP = [
        'supersport'         => 'Supersport',
        'maximum-torque'     => 'Maximum Torque',
        'sport-heritage'     => 'Sport Heritage',
        'sport-touring'      => 'Sport Touring',
        'scooter'            => 'Scooter',
        'motocross'          => 'Motocross',
        'enduro'             => 'Enduro',
        'fun'                => 'Fun',
        'adventure'          => 'Adventure',
        'agriculture'        => 'Agriculture',
        'sport-atv'          => 'Sport ATV',
        'fun-atv'            => 'Fun ATV',
        'sport-rov'          => 'Sport ROV',
        'utility-rov'        => 'Utility ROV',
        'recreational'       => 'Recreational',
        'luxury-performance' => 'Luxury Performance',
        'race-inspired'      => 'Race Inspired',
        'freestyle'          => 'Freestyle',
        'jetfish'            => 'Jetfish',
        // Golf Car categories
        'electric'           => 'Electric',
        'petrol'             => 'Petrol',
        'utility'            => null, // Golf Car utility maps to product_group=Utility
    ];

    private const GOLF_CAR_UTILITY_GROUP = 'Utility';

    public function index(): Response
    {
        $groupSlugByValue    = array_flip(self::GROUP_MAP);
        $categorySlugByValue = array_flip(array_filter(self::CATEGORY_MAP, fn($v) => $v !== null));

        // All products — build URLs via reverse-map
        $products = YamahaProduct::select('id', 'model_name', 'product_group', 'sub_category', 'synced_at')->get();

        $productUrls = [];
        foreach ($products as $product) {
            $groupSlug    = $groupSlugByValue[$product->product_group] ?? null;
            $categorySlug = $categorySlugByValue[$product->sub_category] ?? null;

            // Golf Car utility products live under golf-car/utility
            if ($product->product_group === self::GOLF_CAR_UTILITY_GROUP) {
                $groupSlug    = 'golf-car';
                $categorySlug = 'utility';
            }

            if (! $groupSlug || ! $categorySlug) {
                continue;
            }

            $productUrls[] = [
                'url'     => route('yamaha.product', [$groupSlug, $categorySlug, $product->slug]),
                'updated' => $product->synced_at?->toAtomString() ?? now()->toAtomString(),
            ];
        }

        // Static pages
        $staticPages = [
            route('yamaha.index'),
            route('yamaha.preowned'),
            route('yamaha.specials'),
            route('yamaha.insurance'),
            route('yamaha.finance'),
            route('yamaha.service'),
            route('yamaha.about'),
            route('yamaha.sell'),
        ];

        foreach (array_keys(self::GROUP_MAP) as $groupSlug) {
            $staticPages[] = route('yamaha.group', $groupSlug);
            $categories    = config('yamaha_nav.groups.' . $groupSlug . '.categories', []);
            foreach (array_keys($categories) as $categorySlug) {
                $staticPages[] = route('yamaha.category', [$groupSlug, $categorySlug]);
            }
        }

        // Pre-owned listings
        $preOwned = PreOwnedListing::where('active', true)->select('id', 'title', 'updated_at')->get();

        // Active specials
        $specials = Special::active()->select('id', 'title', 'updated_at')->get();

        $xml = view('sitemap', compact('staticPages', 'productUrls', 'preOwned', 'specials'))->render();

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
