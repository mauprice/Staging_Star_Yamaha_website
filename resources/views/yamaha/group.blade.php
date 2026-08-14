@extends('yamaha.layout')

@section('title', $groupName)
@section('meta_description', 'Browse the full Yamaha ' . $groupName . ' range at NorthStar Yamaha. Explore models, specs and pricing from your authorised Yamaha dealer.')

@section('content')

    {{-- Hero --}}
    <div class="relative text-white py-24 bg-ink">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="text-gray-400 text-sm mb-4">
                <a href="{{ route('yamaha.index') }}" class="hover:text-white">Home</a>
                <span class="mx-2">/</span>
                <span class="text-white">{{ $groupName }}</span>
            </nav>
            <h1 class="text-6xl md:text-8xl font-black uppercase leading-none tracking-tight">
                {{ $groupName }}
            </h1>
        </div>
    </div>

    {{-- Sub-category Cards --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($subCategories as $catSlug => $subCat)
            @php
                // If keys are integers (non-golf-car), build slug from the value
                if (is_int($catSlug)) {
                    $catSlug = strtolower(str_replace(' ', '-', $subCat));
                }
                $bannerImg = $previews[$subCat] ?? null;
            @endphp
            <x-media-tile
                href="{{ route('yamaha.category', [$group, $catSlug]) }}"
                image="{{ $bannerImg }}"
                :imageAlt="$subCat"
                label="{{ $subCat }}"
                sublabel="View Range →"
                ratio="16/9" />
            @endforeach
        </div>
    </section>

@endsection
