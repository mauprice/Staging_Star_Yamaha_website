@extends('yamaha.layout')

@section('title', $categoryLabel . ' — Honda')
@section('meta_description', 'Browse the Honda ' . $categoryLabel . ' range at NorthStar. Explore models, specs and pricing from your authorised dealer.')

@section('content')

    {{-- Hero --}}
    <div class="relative text-white py-24 bg-ink">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="text-gray-400 text-sm mb-4">
                <a href="{{ route('yamaha.index') }}" class="hover:text-white">Home</a>
                <span class="mx-2">/</span>
                <a href="{{ route('honda.index') }}" class="hover:text-white">Honda</a>
                <span class="mx-2">/</span>
                <span class="text-white">{{ $categoryLabel }}</span>
            </nav>
            <h1 class="text-6xl md:text-8xl font-black uppercase leading-none tracking-tight">
                {{ $categoryLabel }}
            </h1>
        </div>
    </div>

    {{-- Sub-category Cards --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <div class="lg:col-span-3 grid grid-cols-1 sm:grid-cols-2 gap-6">
                @foreach($subcategories as $sub)
                <x-media-tile
                    href="{{ route('honda.subcategory', [$category, $sub]) }}"
                    image="{{ $previews[$sub] ?? null }}"
                    :imageAlt="$subcategoryLabels[$sub]"
                    label="{{ $subcategoryLabels[$sub] }}"
                    sublabel="View Range →"
                    ratio="16/9" />
                @endforeach
            </div>
            <aside class="lg:col-span-1">
                <x-honda-offer-panel :offers="$offers" />
            </aside>
        </div>
    </section>

@endsection
