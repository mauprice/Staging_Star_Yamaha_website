@extends('yamaha.layout')

@section('title', 'Shop Accessories')
@section('meta_description', 'Shop genuine Yamaha accessories, clothing, helmets and riding gear at Star Yamaha.')

@section('content')

    <div class="bg-ink border-b-2 border-brand">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <nav class="text-gray-500 text-xs uppercase tracking-widest font-semibold mb-4">
                <a href="{{ route('yamaha.index') }}" class="hover:text-white transition">Home</a>
                <span class="mx-2">›</span>
                <span class="text-white">Shop Accessories</span>
            </nav>
            <p class="text-brand text-xs font-black uppercase tracking-[0.28em] mb-2">Genuine Gear</p>
            <h1 class="text-4xl font-black uppercase text-white tracking-tight">Shop Accessories</h1>
            <p class="text-gray-400 mt-2 text-sm">Clothing, helmets, riding gear and genuine accessories.</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        {{-- Filter bar --}}
        <div class="flex flex-wrap gap-2 mb-8">
            <a href="{{ route('yamaha.shop.index') }}"
               class="px-4 py-1.5 text-xs font-black uppercase tracking-widest rounded-full transition-colors {{ request('category') ? 'bg-gray-100 text-gray-700 hover:bg-brand-tint hover:text-brand-dark' : 'bg-brand text-white' }}">
                All
            </a>
            @foreach($categories as $cat)
            <a href="{{ route('yamaha.shop.index', ['category' => $cat]) }}"
               class="px-4 py-1.5 text-xs font-black uppercase tracking-widest rounded-full transition-colors {{ request('category') === $cat ? 'bg-brand text-white' : 'bg-gray-100 text-gray-700 hover:bg-brand-tint hover:text-brand-dark' }}">
                {{ $cat }}
            </a>
            @endforeach
        </div>

        @if($products->isEmpty())
            <div class="text-center py-24">
                <p class="text-gray-400 text-lg">No products found.</p>
                @if(request('category') || request('q'))
                <p class="text-gray-400 text-sm mt-2">
                    <a href="{{ route('yamaha.shop.index') }}" class="text-brand hover:underline">Clear filters</a>
                </p>
                @endif
            </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($products as $product)
            <x-product-card
                :href="route('yamaha.shop.show', ['id' => $product->id, 'slug' => $product->slug])"
                :image="$product->heroImage ? asset('storage/' . $product->heroImage->path) : null"
                :imageAlt="$product->name"
                fit="contain"
                :badge="$product->category"
                :title="$product->name"
                :description="$product->brand">

                @if($product->isClothing())
                <p class="text-sm text-gray-500">From <span class="text-xl font-black text-brand">${{ number_format($product->price, 2) }}</span></p>
                @else
                <p class="text-xl font-black text-brand">${{ number_format($product->price, 2) }}</p>
                @endif
                @if($product->total_stock <= 0)
                <p class="text-xs text-red-500 font-semibold uppercase tracking-wide mt-1">Out of stock</p>
                @endif
            </x-product-card>
            @endforeach
        </div>

        <div class="mt-10">
            {{ $products->links() }}
        </div>
        @endif
    </div>

@endsection
