@extends('yamaha.layout')

@section('title', 'Checkout Cancelled')

@section('content')

<div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
    <h1 class="text-2xl font-black text-gray-900 mb-3">Checkout wasn't completed</h1>
    <p class="text-gray-500 mb-8">No payment was taken. Your items are still saved — you can restore them to your cart and try again.</p>

    <form method="POST" action="{{ route('yamaha.checkout.restore', $order) }}" class="inline-block">
        @csrf
        <x-btn type="submit" variant="primary">Restore Items to Cart</x-btn>
    </form>

    <div class="mt-4">
        <a href="{{ route('yamaha.shop.index') }}" class="text-sm text-gray-500 hover:text-gray-700 transition">Continue Shopping →</a>
    </div>
</div>

@endsection
