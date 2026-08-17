@extends('yamaha.layout')

@section('title', $offer->title . ' — Honda Offers')
@section('og_image')
{{ $offer->image?->url() ?? url('/images/star_yamaha_honda_logo.png') }}
@endsection
@section('meta_description', $offer->title . ' — current Honda offer at NorthStar, your authorised Honda dealer.')

@section('content')

    <div class="bg-ink border-b-2 border-brand">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <nav class="text-gray-500 text-xs uppercase tracking-widest font-semibold mb-4">
                <a href="{{ route('yamaha.index') }}" class="hover:text-white transition">Home</a>
                <span class="mx-2">›</span>
                <a href="{{ route('honda.index') }}" class="hover:text-white transition">Honda</a>
                <span class="mx-2">›</span>
                <a href="{{ route('honda.offers') }}" class="hover:text-white transition">Offers</a>
                <span class="mx-2">›</span>
                <span class="text-white">{{ $offer->title }}</span>
            </nav>
            <h1 class="text-4xl font-black uppercase text-white tracking-tight">{{ $offer->title }}</h1>
            @if($offer->subtitle)
            <p class="text-gray-400 mt-2 text-sm max-w-2xl">{{ $offer->subtitle }}</p>
            @endif
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        @if($offer->body)
        <div class="prose prose-gray max-w-none text-gray-700 leading-relaxed mb-12">
            {!! $offer->body !!}
        </div>
        @endif

        @if($offer->children->isNotEmpty())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($offer->children as $child)
            <x-product-card
                href="{{ $child->linkUrl }}"
                :target="$child->honda_model_id ? null : '_blank'"
                image="{{ $child->image?->url() }}"
                :imageAlt="$child->title"
                fit="contain"
                title="{{ $child->title }}"
                description="{{ $child->subtitle }}">

                @if($child->price_label)
                <p class="text-xl font-black text-brand leading-none">{{ $child->price_label }}</p>
                @else
                <p class="text-sm text-gray-400 italic">Contact for pricing</p>
                @endif
            </x-product-card>
            @endforeach
        </div>
        @elseif($offer->cta_url)
        <a href="{{ $offer->linkUrl }}" @if(!$offer->honda_model_id) target="_blank" rel="noopener" @endif
           class="inline-block font-black py-3 px-8 rounded-lg transition text-white bg-brand hover:bg-brand-dark">
            {{ $offer->cta_label ?? 'View offer' }} →
        </a>
        @endif

    </div>

@endsection
