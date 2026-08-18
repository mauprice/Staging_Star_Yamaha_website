@extends('yamaha.layout')

@section('title', 'Specials')
@section('meta_description', 'Browse current Yamaha special offers and deals at Star Yamaha. Limited-time promotions on motorcycles, scooters, ATVs and accessories.')

@section('content')

    <div class="bg-ink border-b-2 border-brand">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <nav class="text-gray-500 text-xs uppercase tracking-widest font-semibold mb-4">
                <a href="{{ route('yamaha.index') }}" class="hover:text-white transition">Home</a>
                <span class="mx-2">›</span>
                <span class="text-white">Specials</span>
            </nav>
            <h1 class="text-4xl font-black uppercase text-white tracking-tight">Specials</h1>
            <p class="text-gray-400 mt-2 text-sm">Exclusive deals on new bikes, watercraft &amp; more — while stocks last.</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        @if($specials->isEmpty())
            <div class="text-center py-24">
                <p class="text-gray-400 text-lg">No specials available right now.</p>
                <p class="text-gray-400 text-sm mt-2">Check back soon or call us on <a href="{{ config('dealership.phone.href') }}" class="text-brand">{{ config('dealership.phone.display') }}</a> for current offers.</p>
            </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($specials as $special)
            @php
                $cardImage = $special->featured_image
                    ?? (is_array($special->images) && count($special->images) ? $special->images[0] : null);
                $onSale = $special->special_price && $special->regular_price && $special->special_price < $special->regular_price;
            @endphp
            <x-product-card
                href="{{ route('yamaha.specials.show', ['id' => $special->id, 'slug' => str($special->title)->slug()]) }}"
                image="{{ $cardImage ? asset('storage/' . $cardImage) : null }}"
                :imageAlt="$special->title"
                fit="fill"
                :badge="$onSale ? 'Sale!' : null"
                title="{{ $special->title }}">

                @if($special->special_price)
                    @if($special->regular_price && $special->regular_price != $special->special_price)
                    <p class="text-xs text-gray-400 line-through">${{ number_format($special->regular_price, 0) }}.00</p>
                    @endif
                    <p class="text-xl font-black text-brand">${{ number_format($special->special_price, 0) }}.00</p>
                @elseif($special->regular_price)
                    <p class="text-xl font-black text-gray-900">${{ number_format($special->regular_price, 0) }}.00</p>
                @else
                    <p class="text-sm text-gray-400 italic">Contact for pricing</p>
                @endif
            </x-product-card>
            @endforeach
        </div>
        @endif

    </div>

    {{-- Yamaha Current Deals from API --}}
    @if($promotions->isNotEmpty())
    <div class="bg-gray-50 border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

            <div class="mb-8">
                <p class="text-xs uppercase tracking-widest font-black text-brand mb-1">From Yamaha Motor Australia</p>
                <h2 class="text-2xl font-black uppercase text-gray-900 tracking-tight">Current Deals & Offers</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($promotions as $promo)
                <a href="{{ $promo->full_content_url ?: '#' }}"
                   @if($promo->full_content_url) target="_blank" rel="noopener" @endif
                   class="group flex flex-col rounded-xl overflow-hidden border border-gray-200 hover:border-brand hover:shadow-lg transition bg-white">

                    {{-- Image --}}
                    <div class="relative overflow-hidden bg-gray-100" style="aspect-ratio: 3/2;">
                        @if($promo->brief_image)
                        <img src="{{ trim($promo->brief_image) }}"
                             alt="{{ $promo->head }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                             loading="lazy">
                        @else
                        <div class="w-full h-full flex items-center justify-center bg-gray-100">
                            <img src="/images/star_yamaha_honda_logo.png" alt="Star Yamaha" class="h-12 opacity-30">
                        </div>
                        @endif

                        @if($promo->type)
                        <span class="absolute top-3 left-3 bg-brand text-white text-[0.65rem] font-black uppercase tracking-widest px-2.5 py-1 rounded shadow">
                            {{ $promo->type }}
                        </span>
                        @endif
                    </div>

                    {{-- Content --}}
                    <div class="flex flex-col flex-1 p-5">
                        <h3 class="font-black text-gray-900 text-base leading-snug group-hover:text-brand transition mb-2">
                            {{ $promo->head }}
                        </h3>
                        @if($promo->brief)
                        <p class="text-gray-500 text-sm leading-relaxed line-clamp-3 flex-1">
                            {{ strip_tags($promo->brief) }}
                        </p>
                        @endif
                        @if($promo->full_content_url)
                        <span class="mt-4 text-brand text-xs font-black uppercase tracking-widest">
                            View Offer →
                        </span>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        {{-- CTA --}}
        <div class="rounded-xl overflow-hidden bg-gradient-to-br from-ink to-ink-2 border border-gray-800">
            <div class="p-10 flex flex-col md:flex-row items-center justify-between gap-6">
                <div>
                    <p class="text-xs uppercase tracking-widest font-black text-brand mb-1">Need more information?</p>
                    <h2 class="text-2xl font-black uppercase text-white tracking-tight">Talk to Our Team</h2>
                    <p class="text-gray-400 text-sm mt-2">Our experts can help you find the best deal and arrange finance.</p>
                </div>
                <x-btn href="{{ config('dealership.phone.href') }}" variant="primary" class="flex-shrink-0 whitespace-nowrap">
                    Call {{ config('dealership.phone.display') }} →
                </x-btn>
            </div>
        </div>
    </div>

@endsection
