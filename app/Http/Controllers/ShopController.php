<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::where('active', true)
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%' . $request->string('q') . '%'))
            ->with('heroImage')
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate(24)
            ->withQueryString();

        return view('yamaha.shop', [
            'products'   => $products,
            'categories' => Product::CATEGORIES,
        ]);
    }

    public function show(int $id, string $slug): View
    {
        $product = Product::where('active', true)
            ->with([
                'images',
                'heroImage',
                'variants' => fn ($q) => $q->where('active', true),
            ])
            ->findOrFail($id);

        return view('yamaha.shop-detail', compact('product'));
    }
}
