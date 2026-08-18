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

                    @if($product->isClothing())
                        @if($product->variants->isEmpty())
                        <p class="text-4xl font-black text-brand mt-1 mb-4">From ${{ number_format($product->price, 2) }}</p>
                        <p class="text-sm text-red-500 font-semibold">Currently unavailable — no sizes in stock.</p>
                        @else
                        <div x-data="{
                                variants: @js($product->variants->map(fn ($v) => ['id' => $v->id, 'size' => $v->size, 'colour' => $v->colour, 'price' => (float) $v->effective_price, 'quantity' => $v->quantity])),
                                size: '', colour: '', quantity: 1, adding: false, added: false,
                                get selected() { return this.variants.find(v => v.size === this.size && v.colour === this.colour) ?? null },
                                get canAdd() { return this.selected && this.selected.quantity > 0 && !this.adding },
                                async add() {
                                    if (!this.canAdd) return;
                                    this.adding = true;
                                    const ok = await window.addToCart({ product_id: {{ $product->id }}, product_variant_id: this.selected.id, quantity: this.quantity }, $el);
                                    this.adding = false;
                                    if (ok) { this.added = true; setTimeout(() => this.added = false, 2000) }
                                }
                            }">
                            <p class="text-4xl font-black text-brand mt-1 mb-4"
                               x-text="selected ? ('$' + selected.price.toFixed(2)) : 'From ${{ number_format($product->price, 2) }}'"></p>

                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-1">Size</label>
                            <select x-model="size" class="w-full border border-gray-300 rounded-lg p-2.5 mb-3 text-sm">
                                <option value="">Select size</option>
                                @foreach($product->variants->pluck('size')->unique() as $size)
                                <option value="{{ $size }}">{{ $size }}</option>
                                @endforeach
                            </select>

                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-1">Colour</label>
                            <select x-model="colour" class="w-full border border-gray-300 rounded-lg p-2.5 mb-3 text-sm">
                                <option value="">Select colour</option>
                                @foreach($product->variants->pluck('colour')->unique() as $colour)
                                <option value="{{ $colour }}">{{ $colour }}</option>
                                @endforeach
                            </select>

                            <p class="text-xs text-gray-500 mb-3 min-h-[1rem]"
                               x-text="selected ? (selected.quantity > 0 ? selected.quantity + ' in stock' : 'Out of stock') : ''"></p>

                            <div x-show="selected && selected.quantity > 0" x-cloak class="flex items-center gap-3 mb-4">
                                <label class="text-xs font-black uppercase tracking-widest text-gray-500">Qty</label>
                                <input type="number" x-model.number="quantity" min="1" :max="selected ? Math.min(selected.quantity, 99) : 1"
                                       class="w-20 border border-gray-300 rounded-lg p-2 text-sm">
                            </div>

                            <button type="button" @click="add()" :disabled="!canAdd"
                                class="w-full font-black py-3.5 rounded-lg uppercase tracking-widest text-sm transition"
                                :class="canAdd ? 'bg-brand hover:bg-brand-dark text-white' : 'bg-gray-300 text-gray-500 cursor-not-allowed'">
                                <span x-show="!adding && !added" x-text="!selected ? 'Select size & colour' : (selected.quantity > 0 ? 'Add to Cart' : 'Out of Stock')"></span>
                                <span x-show="adding" x-cloak>Adding…</span>
                                <span x-show="added" x-cloak>✓ Added to Cart</span>
                            </button>
                        </div>
                        @endif
                    @else
                        <p class="text-4xl font-black text-brand mt-1 mb-4">${{ number_format($product->price, 2) }}</p>
                        @if($product->total_stock > 0)
                        <div x-data="{
                                quantity: 1, adding: false, added: false,
                                async add() {
                                    this.adding = true;
                                    const ok = await window.addToCart({ product_id: {{ $product->id }}, quantity: this.quantity }, $el);
                                    this.adding = false;
                                    if (ok) { this.added = true; setTimeout(() => this.added = false, 2000) }
                                }
                            }">
                            <p class="text-xs text-gray-500 mb-3">{{ $product->total_stock }} in stock</p>
                            <div class="flex items-center gap-3 mb-4">
                                <label class="text-xs font-black uppercase tracking-widest text-gray-500">Qty</label>
                                <input type="number" x-model.number="quantity" min="1" max="{{ min($product->total_stock, 99) }}"
                                       class="w-20 border border-gray-300 rounded-lg p-2 text-sm">
                            </div>
                            <button type="button" @click="add()" :disabled="adding"
                                class="w-full bg-brand hover:bg-brand-dark text-white font-black py-3.5 rounded-lg uppercase tracking-widest text-sm transition disabled:opacity-60">
                                <span x-show="!adding && !added">Add to Cart</span>
                                <span x-show="adding" x-cloak>Adding…</span>
                                <span x-show="added" x-cloak>✓ Added to Cart</span>
                            </button>
                        </div>
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
