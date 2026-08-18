@extends('yamaha.layout')

@section('title', $product->name . ' — Shop')
@section('og_image')
{{ $product->heroImage ? asset('storage/' . $product->heroImage->path) : url('/images/star_yamaha_honda_logo.png') }}
@endsection
@section('og_type', 'product')
@section('meta_description', $product->name . ' — genuine Yamaha accessory available at Star Yamaha. ' . strip_tags((string) $product->description))

@section('content')

    <div class="bg-ink border-b-2 border-brand">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <nav class="text-gray-500 text-xs uppercase tracking-widest font-semibold mb-4">
                <a href="{{ route('yamaha.index') }}" class="hover:text-white transition">Home</a>
                <span class="mx-2">›</span>
                <a href="{{ route('yamaha.shop.index') }}" class="hover:text-white transition">Shop Accessories</a>
                <span class="mx-2">›</span>
                <span class="text-white">{{ $product->name }}</span>
            </nav>
            <h1 class="text-4xl font-black uppercase text-white tracking-tight">{{ $product->name }}</h1>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">

            {{-- Images --}}
            <div class="lg:col-span-2 space-y-4">
                @if($product->heroImage)
                <div class="rounded-xl overflow-hidden bg-white border border-gray-100 flex items-center justify-center p-8" style="aspect-ratio:4/3;">
                    <img src="{{ asset('storage/' . $product->heroImage->path) }}"
                         alt="{{ $product->name }}"
                         class="max-w-full max-h-full object-contain" id="main-image">
                </div>
                @endif

                @if($product->images->count() > 1)
                <div class="grid grid-cols-5 gap-2">
                    @foreach($product->images as $img)
                    <button onclick="document.getElementById('main-image').src='{{ asset('storage/' . $img->path) }}'"
                            class="rounded-lg overflow-hidden border-2 {{ $img->is_hero ? 'border-brand' : 'border-transparent' }} hover:border-brand transition bg-white p-1" style="aspect-ratio:1;">
                        <img src="{{ asset('storage/' . $img->path) }}" class="w-full h-full object-contain">
                    </button>
                    @endforeach
                </div>
                @endif

                @if($product->description)
                <div class="mt-6">
                    <h2 class="text-xs uppercase tracking-widest font-black text-brand mb-3">Description</h2>
                    <div class="prose prose-gray max-w-none text-gray-600 leading-relaxed">
                        {!! nl2br(e($product->description)) !!}
                    </div>
                </div>
                @endif

                <div class="mt-6">
                    <h2 class="text-xs uppercase tracking-widest font-black text-brand mb-3">Specifications</h2>
                    <dl class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm border-t border-gray-200 pt-4">
                        @foreach([
                            'Brand'         => $product->brand,
                            'Part Number'   => $product->part_number,
                            'Weight'        => $product->weight_kg ? $product->weight_kg . ' kg' : null,
                            'Dimensions'    => ($product->length_mm && $product->width_mm && $product->height_mm)
                                ? "{$product->length_mm} × {$product->width_mm} × {$product->height_mm} mm" : null,
                        ] as $label => $value)
                        @if($value)
                        <div>
                            <dt class="text-gray-500 font-semibold">{{ $label }}</dt>
                            <dd class="text-gray-900 font-black">{{ $value }}</dd>
                        </div>
                        @endif
                        @endforeach
                    </dl>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-5">
                <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                    <span class="text-xs text-gray-400 font-semibold uppercase">{{ $product->category }}</span>

                    <p class="text-4xl font-black text-brand mt-1 mb-4" id="display-price">
                        @if($product->isClothing())
                            From ${{ number_format($product->price, 2) }}
                        @else
                            ${{ number_format($product->price, 2) }}
                        @endif
                    </p>

                    @if($product->isClothing())
                        @if($product->variants->isEmpty())
                        <p class="text-sm text-red-500 font-semibold">Currently unavailable — no sizes in stock.</p>
                        @else
                        <div id="variant-picker" data-variants="{{ $product->variants->map(fn($v) => [
                            'id' => $v->id, 'size' => $v->size, 'colour' => $v->colour,
                            'price' => $v->effective_price, 'quantity' => $v->quantity,
                        ])->toJson() }}">
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-1">Size</label>
                            <select id="variant-size" class="w-full border border-gray-300 rounded-lg p-2.5 mb-3 text-sm">
                                <option value="">Select size</option>
                                @foreach($product->variants->pluck('size')->unique() as $size)
                                <option value="{{ $size }}">{{ $size }}</option>
                                @endforeach
                            </select>

                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-1">Colour</label>
                            <select id="variant-colour" class="w-full border border-gray-300 rounded-lg p-2.5 mb-3 text-sm">
                                <option value="">Select colour</option>
                                @foreach($product->variants->pluck('colour')->unique() as $colour)
                                <option value="{{ $colour }}">{{ $colour }}</option>
                                @endforeach
                            </select>

                            <p id="variant-stock" class="text-xs text-gray-500 mb-3 min-h-[1rem]"></p>

                            {{-- Wired to cart in a later phase --}}
                            <button type="button" disabled id="add-to-cart-btn"
                                class="w-full bg-gray-300 text-gray-500 font-black py-3.5 rounded-lg uppercase tracking-widest text-sm cursor-not-allowed transition">
                                Select size &amp; colour
                            </button>
                        </div>

                        <script>
                            (function () {
                                const el = document.getElementById('variant-picker');
                                const variants = JSON.parse(el.dataset.variants);
                                const sizeEl = document.getElementById('variant-size');
                                const colourEl = document.getElementById('variant-colour');
                                const stockEl = document.getElementById('variant-stock');
                                const priceEl = document.getElementById('display-price');
                                const btnEl = document.getElementById('add-to-cart-btn');

                                function update() {
                                    const match = variants.find(v => v.size === sizeEl.value && v.colour === colourEl.value);
                                    if (!match) {
                                        stockEl.textContent = '';
                                        btnEl.textContent = 'Select size & colour';
                                        return;
                                    }
                                    if (match.quantity > 0) {
                                        stockEl.textContent = match.quantity + ' in stock';
                                        priceEl.textContent = '$' + parseFloat(match.price).toFixed(2);
                                        btnEl.textContent = 'Add to Cart';
                                    } else {
                                        stockEl.textContent = 'Out of stock';
                                        btnEl.textContent = 'Out of Stock';
                                    }
                                }
                                sizeEl.addEventListener('change', update);
                                colourEl.addEventListener('change', update);
                            })();
                        </script>
                        @endif
                    @else
                        @if($product->total_stock > 0)
                        <p class="text-xs text-gray-500 mb-4">{{ $product->total_stock }} in stock</p>
                        {{-- Wired to cart in a later phase --}}
                        <button type="button" disabled
                            class="w-full bg-gray-300 text-gray-500 font-black py-3.5 rounded-lg uppercase tracking-widest text-sm cursor-not-allowed transition">
                            Add to Cart
                        </button>
                        @else
                        <p class="text-sm text-red-500 font-semibold mb-4">Out of stock</p>
                        @endif
                    @endif
                </div>

                <a href="{{ route('yamaha.shop.index') }}"
                   class="block text-center text-sm font-semibold text-gray-500 hover:text-gray-700 transition py-2">
                    ← Back to Shop
                </a>
            </div>
        </div>
    </div>

@endsection
