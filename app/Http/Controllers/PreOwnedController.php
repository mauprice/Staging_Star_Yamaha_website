<?php

namespace App\Http\Controllers;

use App\Mail\SellMyBikeMail;
use App\Models\PreOwnedListing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PreOwnedController extends Controller
{
    public function index(): View
    {
        $listings = PreOwnedListing::where('active', true)
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get();

        $categories = $listings->pluck('category')->unique()->sort()->values();

        return view('yamaha.pre-owned', compact('listings', 'categories'));
    }

    public function show(int $id, string $slug): View
    {
        $listing = PreOwnedListing::where('active', true)->findOrFail($id);

        return view('yamaha.pre-owned-detail', compact('listing'));
    }

    public function sellForm(): View
    {
        return view('yamaha.sell-my-bike');
    }

    public function sellStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'phone'         => 'required|string|max:30',
            'email'         => 'required|email|max:150',
            'suburb'        => 'required|string|max:100',
            'year'          => 'required|digits:4',
            'make'          => 'required|string|max:100',
            'model'         => 'required|string|max:100',
            'kms'           => 'nullable|integer|min:0',
            'asking_price'  => 'nullable|numeric|min:0',
            'condition'     => 'required|string',
            'message'       => 'nullable|string|max:1500',
        ]);

        Mail::to(env('BOOKING_EMAIL', 'sales@northstaryamaha.com.au'))
            ->send(new SellMyBikeMail($data));

        return redirect()->route('yamaha.sell')
            ->with('success', "Thanks {$data['name']}! We've received your valuation request and will be in touch shortly.");
    }
}
