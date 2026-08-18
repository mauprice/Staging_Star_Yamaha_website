@extends('yamaha.layout')

@section('title', $subcategoryLabel . ' — ' . $categoryLabel . ' — Honda')
@section('meta_description', 'Shop the Honda ' . $subcategoryLabel . ' range at Star Yamaha. Compare models, specs and pricing from your authorised dealer.')

@section('content')

    {{-- Hero --}}
    <div class="relative text-white py-24 bg-ink">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="text-gray-400 text-sm mb-4">
                <a href="{{ route('yamaha.index') }}" class="hover:text-white">Home</a>
                <span class="mx-2">/</span>
                <a href="{{ route('honda.index') }}" class="hover:text-white">Honda</a>
                <span class="mx-2">/</span>
                <a href="{{ route('honda.category', $category) }}" class="hover:text-white">{{ $categoryLabel }}</a>
                <span class="mx-2">/</span>
                <span class="text-white">{{ $subcategoryLabel }}</span>
            </nav>
            <h1 class="text-6xl md:text-8xl font-black uppercase leading-none tracking-tight">
                {{ $subcategoryLabel }}
            </h1>
            <p class="text-gray-300 mt-2 text-sm font-medium">
                {{ $models->count() }} model{{ $models->count() !== 1 ? 's' : '' }} available
            </p>
        </div>
    </div>

    {{-- Product Grid --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <div class="lg:col-span-3 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($models as $model)
            @php
                $image = $model->colours->first()?->image?->url() ?? $model->ogImage?->url();
            @endphp
            <x-product-card
                href="{{ route('honda.product', [$category, $subcategory, $model->slug]) }}"
                image="{{ $image }}"
                :imageAlt="$model->name"
                fit="contain"
                title="{{ $model->name }}"
                description="{{ $model->tagline }}">

                <div class="flex items-end justify-between">
                    @if($model->price_from)
                    <div>
                        <p class="text-xl font-black text-brand leading-none">
                            {{ $model->formatted_price }}
                        </p>
                        <p class="text-[10px] text-gray-400">{{ $model->price_label ?? 'Ride away' }}</p>
                    </div>
                    @else
                    <p class="text-sm text-gray-400 italic">Contact for pricing</p>
                    @endif

                    @if($model->colours->count() > 0)
                    <div class="flex gap-1 mb-1">
                        @foreach($model->colours->take(5) as $colour)
                        <div class="w-3.5 h-3.5 rounded-full border border-gray-300 flex-shrink-0"
                             style="background-color: {{ $colour->hex ?: '#e5e7eb' }}"
                             title="{{ $colour->name }}"></div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </x-product-card>
            @endforeach
            </div>
            <aside class="lg:col-span-1">
                <x-honda-offer-panel :offers="$offers" />
            </aside>
        </div>
    </section>

@endsection
