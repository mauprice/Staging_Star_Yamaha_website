<?php

namespace App\Http\Controllers;

use App\Models\Special;
use App\Models\YamahaPromotion;

class SpecialController extends Controller
{
    public function index()
    {
        $specials = Special::active()->get();
        $promotions = YamahaPromotion::where('active', true)
            ->where('country', 'AU')
            ->where('type', '!=', 'Outboard')
            ->orderBy('sort_index')
            ->get();

        return view('specials.index', compact('specials', 'promotions'));
    }

    public function show(int $id, string $slug)
    {
        $special = Special::where('id', $id)->where('is_active', true)->firstOrFail();

        return view('specials.show', compact('special'));
    }
}
