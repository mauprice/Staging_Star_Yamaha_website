@extends('yamaha.layout')

@section('title', 'Honda Product Range')
@section('meta_description', 'Browse the full Honda motorcycle range at Star Yamaha — road, off road and work range models, specs and pricing from your authorised dealer.')

@section('content')

    {{-- Hero --}}
    <div class="relative text-white py-24 bg-ink">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="text-gray-400 text-sm mb-4">
                <a href="{{ route('yamaha.index') }}" class="hover:text-white">Home</a>
                <span class="mx-2">/</span>
                <span class="text-white">Honda</span>
            </nav>
            <h1 class="text-6xl md:text-8xl font-black uppercase leading-none tracking-tight">
                Honda
            </h1>
            <p class="text-gray-300 mt-2 text-sm font-medium">
                {{ collect($categoryPreviews)->sum('count') }} models across {{ count($categoryPreviews) }} ranges
            </p>
        </div>
    </div>

    {{-- Category Cards --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <div class="lg:col-span-3 grid grid-cols-1 sm:grid-cols-2 gap-6">
                @foreach($categoryPreviews as $slug => $preview)
                <x-media-tile
                    href="{{ route('honda.category', $slug) }}"
                    image="{{ $preview['image'] }}"
                    :imageAlt="$preview['label']"
                    label="{{ $preview['label'] }}"
                    sublabel="{{ $preview['count'] }} model{{ $preview['count'] !== 1 ? 's' : '' }} →"
                    ratio="16/9" />
                @endforeach
            </div>
            <aside class="lg:col-span-1">
                <x-honda-offer-panel :offers="$offers" />
            </aside>
        </div>
    </section>

@endsection
