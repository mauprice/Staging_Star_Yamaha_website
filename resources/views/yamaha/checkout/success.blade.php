@extends('yamaha.layout')

@section('title', 'Order Confirmation')
@section('meta_description', 'Thank you for your order at Star Yamaha.')

@section('content')

@if($order->status->value === 'pending_payment')
<meta http-equiv="refresh" content="4">
@endif

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">

    @if($order->status->value === 'pending_payment')
        <div class="w-16 h-16 mx-auto mb-6 rounded-full border-4 border-gray-200 border-t-brand animate-spin"></div>
        <h1 class="text-2xl font-black text-gray-900 mb-2">Confirming your payment…</h1>
        <p class="text-gray-500">This page will update automatically. Please don't close this tab.</p>
    @else
        <div class="w-16 h-16 mx-auto mb-6 rounded-full bg-green-100 flex items-center justify-center">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        @if($order->status->value === 'awaiting_bank_deposit')
        <h1 class="text-3xl font-black text-gray-900 mb-2">Order Received</h1>
        <p class="text-gray-500 mb-1">Order <span class="font-black text-gray-900">{{ $order->order_number }}</span></p>
        <p class="text-gray-500 mb-8">Bank deposit instructions have been sent to {{ $order->customer_email }}. Your order will ship once we've confirmed the deposit.</p>
        @else
        <h1 class="text-3xl font-black text-gray-900 mb-2">Thank you for your order!</h1>
        <p class="text-gray-500 mb-1">Order <span class="font-black text-gray-900">{{ $order->order_number }}</span></p>
        <p class="text-gray-500 mb-8">A receipt has been sent to {{ $order->customer_email }}.</p>
        @endif

        <div class="bg-gray-50 rounded-xl border border-gray-200 p-6 text-left mb-8">
            @foreach($order->items as $item)
            <div class="flex justify-between py-2 text-sm">
                <span class="text-gray-700">{{ $item->product_name }}{{ $item->variant_label ? " ({$item->variant_label})" : '' }} × {{ $item->quantity }}</span>
                <span class="font-semibold text-gray-900">${{ number_format($item->line_total, 2) }}</span>
            </div>
            @endforeach
            <div class="flex justify-between pt-3 mt-2 border-t border-gray-200 text-base font-black text-brand">
                <span>{{ $order->status->value === 'awaiting_bank_deposit' ? 'Total Due' : 'Total' }}</span>
                <span>${{ number_format($order->total, 2) }}</span>
            </div>
        </div>

        @guest
        <p class="text-sm text-gray-400 mb-4">Want easy access to this order next time? <a href="{{ route('register') }}" class="text-brand font-bold hover:text-brand-dark">Create an account</a>.</p>
        @endguest

        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            @auth
            <x-btn href="{{ route('yamaha.account.orders.show', $order) }}" variant="primary">View Order</x-btn>
            @endauth
            <x-btn href="{{ route('yamaha.shop.index') }}" variant="secondary">Continue Shopping</x-btn>
        </div>
    @endif
</div>

@endsection
