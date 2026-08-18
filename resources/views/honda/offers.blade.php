@extends('yamaha.layout')

@section('title', 'Honda Offers')
@section('meta_description', 'Current Honda motorcycle offers and promotions at Star Yamaha — finance deals, model runout pricing and more from your authorised Honda dealer.')

@section('content')

    <div class="bg-ink border-b-2 border-brand">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <nav class="text-gray-500 text-xs uppercase tracking-widest font-semibold mb-4">
                <a href="{{ route('yamaha.index') }}" class="hover:text-white transition">Home</a>
                <span class="mx-2">›</span>
                <a href="{{ route('honda.index') }}" class="hover:text-white transition">Honda</a>
                <span class="mx-2">›</span>
                <span class="text-white">Offers</span>
            </nav>
            <h1 class="text-4xl font-black uppercase text-white tracking-tight">Honda Offers</h1>
            <p class="text-gray-400 mt-2 text-sm">Current finance deals, runout pricing and promotions — while stocks last.</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        @if($offers->isEmpty())
            <div class="text-center py-24">
                <p class="text-gray-400 text-lg">No offers available right now.</p>
                <p class="text-gray-400 text-sm mt-2">Check back soon for the latest Honda promotions.</p>
            </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($offers as $offer)
            <x-deal-card
                href="{{ $offer->linkUrl }}"
                :target="(!$offer->honda_model_id && $offer->children->isEmpty()) ? '_blank' : null"
                image="{{ $offer->image?->url() }}"
                :imageAlt="$offer->title"
                eyebrow="Honda Offer"
                title="{{ $offer->title }}"
                description="{{ $offer->subtitle }}"
                :ctaLabel="$offer->children->isNotEmpty() ? 'View the range →' : ($offer->cta_label ? $offer->cta_label . ' →' : 'View offer →')" />
            @endforeach
        </div>
        @endif

    </div>

@endsection
