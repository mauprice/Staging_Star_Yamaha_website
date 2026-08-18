<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yamaha\Parts\Models\Assembly;
use Yamaha\Parts\Models\Part;
use Yamaha\Parts\Models\Price;
use Yamaha\Parts\Models\Product;

class PartsCatalogueController extends Controller
{
    // Star Yamaha only sells motorcycles — the wider YPIC catalogue this data
    // is sourced from also covers outboards, PWCs, etc. (catalogue 'MA'),
    // which are irrelevant here and hard-excluded everywhere below.
    private const MOTORCYCLE_TYPE = 0;

    public function products(Request $request): JsonResponse
    {
        $query = Product::query()->where('type', self::MOTORCYCLE_TYPE);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('model', 'like', "%{$search}%")
                    ->orWhere('common_name', 'like', "%{$search}%")
                    ->orWhere('prefix', 'like', "%{$search}%");
            });
        }

        if ($request->filled('year')) {
            $query->where('year', $request->input('year'));
        }

        $products = $query->orderBy('model')
            ->paginate($request->input('per_page', 50));

        $products->through(function (Product $p) {
            $p->thumbnail_url = $p->thumbnail_image_id
                ? 'https://parts.northstaryamaha.com.au/storage/images/'.$p->thumbnail_image_id.'.webp'
                : null;

            return $p;
        });

        return response()->json($products);
    }

    public function show(int $id): JsonResponse
    {
        $product = Product::where('type', self::MOTORCYCLE_TYPE)
            ->with(['contents.assemblies'])
            ->findOrFail($id);

        return response()->json([
            'product' => $product,
            'type_name' => $product->type_name,
        ]);
    }

    public function years(): JsonResponse
    {
        $years = Product::where('type', self::MOTORCYCLE_TYPE)
            ->select('year')
            ->whereNotNull('year')
            ->where('year', '!=', '')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return response()->json($years);
    }

    public function showAssembly(int $id): JsonResponse
    {
        $assembly = Assembly::whereHas('content.product', fn ($q) => $q->where('type', self::MOTORCYCLE_TYPE))
            ->with(['content.product', 'assemblyImages.image'])
            ->findOrFail($id);

        $rawParts = $assembly->allParts()->orderBy('image_ref_no')->get();

        $partNumbers = $rawParts->pluck('number')->filter()->unique()->values();
        $priceMap = Price::whereIn('part_number', $partNumbers)
            ->get(['part_number', 'rrp_cents', 'currency'])
            ->keyBy('part_number');

        $parts = $rawParts->map(function (Part $p) use ($priceMap) {
            $price = $priceMap->get($p->number);

            return [
                'part_id' => $p->part_id,
                'number' => $p->number,
                'desc' => $p->desc,
                'remark' => $p->remark,
                'qty' => $p->qty,
                'image_ref_no' => $p->image_ref_no,
                'img_x' => $p->img_x,
                'img_y' => $p->img_y,
                'parent_id' => $p->parent_id ?: null,
                'has_child' => $p->has_child,
                'rrp' => $price ? round($price->rrp_cents / 100, 2) : null,
                'currency' => $price?->currency,
            ];
        });

        $images = $assembly->assemblyImages->map(function ($ai) {
            $image = $ai->image;

            return [
                'assembly_image_id' => $ai->assembly_image_id,
                'image_id' => $ai->image_id,
                'url' => $image ? $image->url : asset('images/placeholder.svg'),
                'width' => $image?->width,
                'height' => $image?->height,
                'extracted' => $image?->extracted ?? false,
            ];
        });

        return response()->json([
            'assembly' => [
                'assembly_id' => $assembly->assembly_id,
                'title' => $assembly->title,
                'content_id' => $assembly->content_id,
                'content_title' => $assembly->content->title ?? '',
                'product_id' => $assembly->content->product_id ?? null,
                'model' => $assembly->content->product->model ?? '',
                'year' => $assembly->content->product->year ?? '',
            ],
            'images' => $images,
            'parts' => $parts,
        ]);
    }

    public function searchParts(Request $request): JsonResponse
    {
        $request->validate(['q' => 'required|string|min:3']);

        $q = trim($request->input('q'));

        $rawParts = Part::where(function ($query) use ($q) {
                $query->where('search_part_no', 'like', '%'.preg_replace('/[^A-Za-z0-9]/', '', $q).'%')
                    ->orWhere('number', 'like', "%{$q}%")
                    ->orWhere('desc', 'like', "%{$q}%");
            })
            ->whereHas('assembly.content.product', fn ($query) => $query->where('type', self::MOTORCYCLE_TYPE))
            ->with(['assembly.content.product'])
            ->limit(50)
            ->get();

        $partNumbers = $rawParts->pluck('number')->filter()->unique()->values();
        $priceMap = Price::whereIn('part_number', $partNumbers)
            ->get(['part_number', 'rrp_cents', 'currency'])
            ->keyBy('part_number');

        $parts = $rawParts->map(function (Part $p) use ($priceMap) {
            $price = $priceMap->get($p->number);

            return [
                'part_id' => $p->part_id,
                'number' => $p->number,
                'desc' => $p->desc,
                'qty' => $p->qty,
                'remark' => $p->remark,
                'assembly_id' => $p->assembly_id,
                'assembly' => $p->assembly?->title,
                'content' => $p->assembly?->content?->title,
                'model' => $p->assembly?->content?->product?->model,
                'year' => $p->assembly?->content?->product?->year,
                'product_id' => $p->assembly?->content?->product_id,
                'rrp' => $price ? round($price->rrp_cents / 100, 2) : null,
                'currency' => $price?->currency,
            ];
        });

        return response()->json($parts);
    }
}
