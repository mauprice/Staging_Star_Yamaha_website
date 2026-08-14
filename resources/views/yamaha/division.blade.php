@extends('yamaha.layout')

@section('title', ucwords(strtolower($divisionName)))
@section('meta_description', 'Browse the Yamaha ' . $divisionName . ' range at NorthStar Yamaha. Models, specs and pricing from your authorised Yamaha dealer.')

@section('content')

    {{-- Hero --}}
    <div class="relative text-white py-20" style="background: linear-gradient(135deg, #001f5c 0%, #003087 100%);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="text-blue-300 text-sm mb-4">
                <a href="{{ route('yamaha.index') }}" class="hover:text-white">Home</a>
                <span class="mx-2">/</span>
                <span class="text-white">{{ ucwords(strtolower($divisionName)) }}</span>
            </nav>
            <h1 class="text-5xl md:text-7xl font-black uppercase leading-none">
                {{ ucwords(strtolower($divisionName)) }}
            </h1>
        </div>
    </div>

    {{-- Category Cards --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($categories as $primaryCategory => $items)
            @php
                $preview = $previews[$primaryCategory] ?? null;
                $bannerImg = $preview?->heroBanners->first()?->image ?? $preview?->summary_image;
                $divSlug = strtolower(str_replace(' ', '-', $divisionName));
                $catSlug = strtolower(str_replace(' ', '-', $primaryCategory));
            @endphp
            <a href="{{ route('yamaha.category', [$divSlug, $catSlug]) }}"
               class="group block rounded-xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 bg-gray-900">

                <div class="aspect-video relative overflow-hidden">
                    @if($bannerImg)
                    <img src="{{ $bannerImg }}" alt="{{ $primaryCategory }}"
                         class="w-full h-full object-cover opacity-70 group-hover:opacity-90 group-hover:scale-105 transition-all duration-500">
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 p-5">
                        <h3 class="text-white text-xl font-black uppercase leading-tight">
                            {{ ucwords(strtolower(str_replace('-', ' ', $primaryCategory))) }}
                        </h3>
                        <p class="text-blue-200 text-sm mt-1 group-hover:underline">
                            {{ $items->count() }} sub-categor{{ $items->count() !== 1 ? 'ies' : 'y' }} →
                        </p>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </section>

@endsection
