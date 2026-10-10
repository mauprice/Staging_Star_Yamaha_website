<?php

namespace App\Http\Controllers;

use App\Models\Special;
use App\Models\YamahaPromotion;

class SpecialController extends Controller
{
    public function index()
    {
        $specials = Special::active()->get();
        $promotions = YamahaPromotion::where('is_visible', true)
            ->where('country', 'AU')
            ->where(function ($query) {
                $query->whereNull('type')->orWhere('type', 'not like', 'Outboard%');
            })
            ->orderBy('display_order')
            ->get();

        return view('specials.index', compact('specials', 'promotions'));
    }

    public function show(int $id, string $slug)
    {
        $special = Special::where('id', $id)->where('is_active', true)->firstOrFail();

        return view('specials.show', compact('special'));
    }
}
