@extends('yamaha.layout')

@section('title', 'Your Cart')
@section('meta_description', 'Review your shopping cart at Star Yamaha.')

@section('content')

    <div class="bg-ink border-b-2 border-brand">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <nav class="text-gray-500 text-xs uppercase tracking-widest font-semibold mb-4">
                <a href="{{ route('yamaha.index') }}" class="hover:text-white transition">Home</a>
                <span class="mx-2">›</span>
                <span class="text-white">Your Cart</span>
            </nav>
            <h1 class="text-4xl font-black uppercase text-white tracking-tight">Your Cart</h1>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        @if(session('success'))
        <div class="mb-8 p-5 rounded-lg border-l-4 border-green-500 bg-green-50 text-green-800">
            <p class="font-black text-sm">{{ session('success') }}</p>
        </div>
        @endif

        @if($items->isEmpty())
        <div class="text-center py-24">
            <p class="text-gray-400 text-lg mb-4">Your cart is empty.</p>
            <x-btn href="{{ route('yamaha.shop.index') }}" variant="primary">
                Browse the Shop →
            </x-btn>
        </div>
        @else
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
            @foreach($items as $item)
            <div class="flex items-center gap-4 p-5 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                <div class="w-20 h-20 bg-gray-50 rounded-lg flex items-center justify-center flex-shrink-0 overflow-hidden">
                    @if($item->product->heroImage)
                    <img src="{{ asset('storage/' . $item->product->heroImage->path) }}"
                         alt="{{ $item->product->name }}" class="max-w-full max-h-full object-contain">
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <a href="{{ route('yamaha.shop.show', ['id' => $item->product->id, 'slug' => $item->product->slug]) }}"
                       class="font-black text-gray-900 hover:text-brand transition text-sm">
                        {{ $item->display_name }}
                    </a>
                    <p class="text-xs text-gray-400 mt-0.5">${{ number_format($item->unit_price, 2) }} each</p>
                    @if($item->quantity > $item->available_stock)
                    <p class="text-xs text-red-500 font-semibold mt-1">Only {{ $item->available_stock }} left in stock</p>
                    @endif
                </div>

                <form method="POST" action="{{ route('yamaha.cart.update', $item) }}" class="flex items-center gap-2">
                    @csrf
                    @method('PATCH')
                    <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ min($item->available_stock, 99) }}"
                           class="w-16 border border-gray-300 rounded-lg p-2 text-sm text-center">
                    <button type="submit" class="text-xs font-semibold text-gray-500 hover:text-brand transition px-2 py-2">
                        Update
                    </button>
                </form>

                <p class="w-24 text-right font-black text-gray-900">${{ number_format($item->line_total, 2) }}</p>

                <form method="POST" action="{{ route('yamaha.cart.destroy', $item) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-gray-300 hover:text-red-500 transition p-2" aria-label="Remove item">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </form>
            </div>
            @endforeach
        </div>

        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-gray-50 rounded-xl border border-gray-200 p-6">
            <div>
                <p class="text-xs uppercase tracking-widest font-black text-gray-500 mb-1">Subtotal</p>
                <p class="text-3xl font-black text-brand">${{ number_format($subtotal, 2) }}</p>
                <p class="text-xs text-gray-400 mt-1">Shipping calculated at checkout.</p>
            </div>
            <x-btn href="{{ route('yamaha.checkout.index') }}" variant="primary" class="w-full sm:w-auto py-4 px-10">
                Checkout →
            </x-btn>
        </div>

        <a href="{{ route('yamaha.shop.index') }}"
           class="inline-block mt-6 text-sm font-semibold text-gray-500 hover:text-gray-700 transition">
            ← Continue Shopping
        </a>
        @endif
    </div>

@endsection
