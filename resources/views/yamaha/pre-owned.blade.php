@extends('yamaha.layout')

@section('title', 'Pre-Owned Vehicles')
@section('meta_description', 'Browse quality pre-owned motorcycles and powersports vehicles at Star Yamaha. All vehicles inspected and priced to sell.')

@section('content')

    <div class="bg-ink border-b-2 border-brand">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <nav class="text-gray-500 text-xs uppercase tracking-widest font-semibold mb-4">
                <a href="{{ route('yamaha.index') }}" class="hover:text-white transition">Home</a>
                <span class="mx-2">›</span>
                <span class="text-white">Pre-Owned</span>
            </nav>
            <p class="text-brand text-xs font-black uppercase tracking-[0.28em] mb-2">Quality Used</p>
            <h1 class="text-4xl font-black uppercase text-white tracking-tight">Pre-Owned Vehicles</h1>
            <p class="text-gray-400 mt-2 text-sm">Quality inspected pre-owned motorcycles, watercraft &amp; more.</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        {{-- Filter bar --}}
        @if($categories->count() > 1)
        <div class="flex flex-wrap gap-2 mb-8" id="filter-bar">
            <button class="filter-btn active px-4 py-1.5 text-xs font-black uppercase tracking-widest rounded-full bg-brand text-white"
                    data-filter="all">All</button>
            @foreach($categories as $cat)
            <button class="filter-btn px-4 py-1.5 text-xs font-black uppercase tracking-widest rounded-full bg-gray-100 text-gray-700 hover:bg-brand-tint hover:text-brand-dark transition-colors"
                    data-filter="{{ $cat }}">{{ $cat }}</button>
            @endforeach
        </div>
        @endif

        @if($listings->isEmpty())
            <div class="text-center py-24">
                <p class="text-gray-400 text-lg">No pre-owned stock listed at the moment.</p>
                <p class="text-gray-400 text-sm mt-2">Call us on <a href="{{ config('dealership.phone.href') }}" class="text-brand">{{ config('dealership.phone.display') }}</a> — we may have something coming in.</p>
            </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="listings-grid">
            @foreach($listings as $listing)
            @php
                $cardImage = $listing->featured_image
                    ?? (is_array($listing->images) && count($listing->images) ? $listing->images[0] : null);
            @endphp
            <x-product-card
                href="{{ route('yamaha.preowned.show', ['id' => $listing->id, 'slug' => str($listing->title)->slug()]) }}"
                image="{{ $cardImage ? asset('storage/' . $cardImage) : null }}"
                :imageAlt="$listing->title"
                fit="fill"
                title="{{ $listing->title }}"
                data-category="{{ $listing->category }}">

                <div class="flex items-center gap-2 mb-1 -mt-2">
                    <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">{{ $listing->year }}</span>
                    <span class="text-[10px] font-black uppercase tracking-widest px-1.5 py-0.5 rounded-sm bg-amber-100 text-amber-800">{{ $listing->condition }}</span>
                </div>
                @if($listing->kms)
                <p class="text-xs text-gray-400 mb-2">{{ number_format($listing->kms) }} km</p>
                @endif
                @if($listing->price)
                <p class="text-xl font-black text-brand">${{ number_format($listing->price, 0) }}</p>
                @else
                <p class="text-sm text-gray-400 italic">Contact for pricing</p>
                @endif
            </x-product-card>
            @endforeach
        </div>
        @endif

        {{-- Sell my bike CTA --}}
        <div class="mt-16 rounded-xl overflow-hidden bg-gradient-to-br from-ink to-ink-2 border border-gray-800">
            <div class="p-10 flex flex-col md:flex-row items-center justify-between gap-6">
                <div>
                    <p class="text-xs uppercase tracking-widest font-black text-brand mb-1">Got a bike to sell?</p>
                    <h2 class="text-2xl font-black uppercase text-white tracking-tight">We Buy Pre-Owned Vehicles</h2>
                    <p class="text-gray-400 text-sm mt-2">No RWC required · Money paid within 24 hours · We come to you</p>
                </div>
                <x-btn href="{{ route('yamaha.sell') }}" variant="primary" class="flex-shrink-0 whitespace-nowrap">
                    Request a Valuation →
                </x-btn>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const filter = this.dataset.filter;
                document.querySelectorAll('.filter-btn').forEach(b => {
                    b.classList.remove('bg-brand', 'text-white');
                    b.classList.add('bg-gray-100', 'text-gray-700');
                });
                this.classList.add('bg-brand', 'text-white');
                this.classList.remove('bg-gray-100', 'text-gray-700');

                document.querySelectorAll('#listings-grid [data-category]').forEach(card => {
                    card.style.display = (filter === 'all' || card.dataset.category === filter) ? '' : 'none';
                });
            });
        });
    </script>

@endsection
