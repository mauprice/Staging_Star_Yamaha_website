<?php

namespace App\Http\Controllers;

use App\Services\HondaPartsPricing;
use Honda\Parts\Models\Assembly;
use Honda\Parts\Models\Image;
use Honda\Parts\Models\ModelPart;
use Honda\Parts\Models\Part;
use Honda\Parts\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class HondaPartsCatalogueController extends Controller
{
    public function __construct(private readonly HondaPartsPricing $pricing) {}

    public function vehicles(Request $request): JsonResponse
    {
        $query = Vehicle::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('model', 'like', "%{$search}%")
                  ->orWhere('model_name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('year')) {
            $query->where('year', $request->input('year'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $vehicles = $query->orderBy('model')->paginate($request->input('per_page', 48));

        return response()->json($vehicles);
    }

    public function years(): JsonResponse
    {
        $years = Vehicle::select('year')
            ->whereNotNull('year')->where('year', '!=', '')
            ->distinct()->orderBy('year', 'desc')->pluck('year');

        return response()->json($years);
    }

    public function types(): JsonResponse
    {
        $types = Vehicle::select('type')
            ->whereNotNull('type')->where('type', '!=', '')
            ->distinct()->orderBy('type')->pluck('type');

        return response()->json($types);
    }

    public function show(int $id): JsonResponse
    {
        $vehicle = Vehicle::with('catalogue')->findOrFail($id);

        $assemblies = $vehicle->assemblies()
            ->with('assemblyImages')
            ->orderBy('page_no')
            ->get()
            ->map(fn ($a) => [
                'assembly_id' => $a->assembly_id,
                'name' => $a->name,
                'address_no' => $a->address_no,
                'page_no' => $a->page_no,
                'has_images' => $a->assemblyImages->isNotEmpty(),
            ]);

        return response()->json([
            'vehicle' => $vehicle,
            'assemblies' => $assemblies,
        ]);
    }

    public function showAssembly(int $id, Request $request): JsonResponse
    {
        $assembly = Assembly::with(['catalogue', 'parts', 'assemblyImages'])->findOrFail($id);

        $modelId = $request->integer('model_id') ?: null;

        $pivotByPartId = [];
        if ($modelId) {
            $pivotByPartId = ModelPart::where('model_id', $modelId)
                ->whereIn('part_id', $assembly->parts->pluck('part_id'))
                ->get(['part_id', 'quantity', 'optional'])
                ->keyBy('part_id');
        }

        $priceMap = $this->priceMapFor($assembly->parts->pluck('search_part_no'));

        // Batch-resolve every assembly image's (catalogue_id, image_id) pair in
        // one query rather than Image::resolveImage() per row — Honda's
        // image_id is only unique per catalogue, never globally.
        $imagePairs = $assembly->assemblyImages->map(fn ($ai) => $ai->image_id)->unique()->values();
        $imagesByKey = Image::where('catalogue_id', $assembly->catalogue_id)
            ->whereIn('image_id', $imagePairs)
            ->get()
            ->keyBy('image_id');

        $parts = $assembly->parts->map(function ($p) use ($pivotByPartId, $priceMap) {
            $pivot = $pivotByPartId[$p->part_id] ?? null;
            $price = $priceMap->get($p->search_part_no);

            return [
                'part_id' => $p->part_id,
                'part_no' => $p->part_no,
                'description' => $p->description,
                'ref_no' => $p->ref_no,
                'ref_x' => $p->ref_x,
                'ref_y' => $p->ref_y,
                'quantity' => $pivot->quantity ?? null,
                'optional' => $pivot->optional ?? null,
                'rrp' => $price['rrp'] ?? null,
                'superseded_by' => $price['superseded_by'] ?? null,
                'superseded_note' => $price['superseded_note'] ?? null,
            ];
        });

        $images = $assembly->assemblyImages->map(function ($ai) use ($imagesByKey) {
            $image = $imagesByKey->get($ai->image_id);
            return [
                'assembly_image_id' => $ai->assembly_image_id,
                'image_id' => $ai->image_id,
                'url' => $image ? $image->url : asset('images/placeholder.svg'),
                'offset_x' => $ai->image_offset_x,
                'offset_y' => $ai->image_offset_y,
                'width' => $image?->width,
                'height' => $image?->height,
                'extracted' => $image?->extracted ?? false,
            ];
        });

        return response()->json([
            'assembly' => [
                'assembly_id' => $assembly->assembly_id,
                'name' => $assembly->name,
                'address_no' => $assembly->address_no,
                'page_no' => $assembly->page_no,
            ],
            'images' => $images,
            'parts' => $parts,
        ]);
    }

    public function searchParts(Request $request): JsonResponse
    {
        $request->validate(['q' => 'required|string|min:3']);

        $q = trim($request->input('q'));
        $normalized = preg_replace('/[^A-Za-z0-9]/', '', $q);

        $rawParts = Part::where('search_part_no', 'like', "%{$normalized}%")
            ->orWhere('part_no', 'like', "%{$q}%")
            ->orWhere('description', 'like', "%{$q}%")
            ->with(['assembly'])
            ->limit(50)
            ->get();

        $priceMap = $this->priceMapFor($rawParts->pluck('search_part_no'));

        $parts = $rawParts->map(function ($p) use ($priceMap) {
            $vehicles = $p->vehicles()->limit(5)->get(['honda_parts_models.model_id', 'honda_parts_models.model', 'honda_parts_models.model_name']);
            $price = $priceMap->get($p->search_part_no);

            return [
                'part_id' => $p->part_id,
                'part_no' => $p->part_no,
                'description' => $p->description,
                'assembly_id' => $p->assembly_id,
                'assembly' => $p->assembly?->name,
                'vehicles' => $vehicles,
                'vehicle_count' => $p->vehicles()->count(),
                'rrp' => $price['rrp'] ?? null,
                'superseded_by' => $price['superseded_by'] ?? null,
                'superseded_note' => $price['superseded_note'] ?? null,
            ];
        });

        return response()->json($parts);
    }

    /**
     * @param  Collection<int, string>  $searchPartNumbers
     * @return Collection<string, array{rrp: ?float, superseded_by: ?string, superseded_note: ?string}>
     */
    private function priceMapFor(Collection $searchPartNumbers): Collection
    {
        return $this->pricing->forNumbers($searchPartNumbers)
            ->map(fn ($price) => [
                'rrp' => round($price['cents'] / 100, 2),
                'superseded_by' => $price['superseded_by'],
                'superseded_note' => $price['superseded_note'],
            ]);
    }
}
