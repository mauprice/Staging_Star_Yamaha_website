@extends('yamaha.layout')

@section('title', 'Order ' . $order->order_number)

@section('content')

<div class="bg-ink border-b-2 border-brand">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <nav class="text-gray-500 text-xs uppercase tracking-widest font-semibold mb-4">
            <a href="{{ route('yamaha.account.orders.index') }}" class="hover:text-white transition">My Orders</a>
            <span class="mx-2">›</span>
            <span class="text-white">{{ $order->order_number }}</span>
        </nav>
        <div class="flex items-center justify-between flex-wrap gap-3">
            <h1 class="text-3xl font-black uppercase text-white tracking-tight">Order {{ $order->order_number }}</h1>
            <span class="text-xs font-black uppercase tracking-wide px-3 py-1.5 rounded-full
                {{ $order->status->color() === 'success' ? 'bg-green-100 text-green-700' : ($order->status->color() === 'danger' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                {{ $order->status->label() }}
            </span>
        </div>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <div class="flex justify-end mb-6">
        <a href="{{ route('yamaha.account.orders.invoice', $order) }}" target="_blank"
           class="text-sm font-black text-brand hover:text-brand-dark">Download Invoice (PDF) →</a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-8">
        @foreach($order->items as $item)
        <div class="flex justify-between gap-4 p-5 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
            <div>
                <p class="font-black text-gray-900 text-sm">{{ $item->product_name }}{{ $item->variant_label ? " ({$item->variant_label})" : '' }}</p>
                <p class="text-xs text-gray-400 mt-0.5">${{ number_format($item->unit_price, 2) }} × {{ $item->quantity }}</p>
            </div>
            <p class="font-black text-gray-900">${{ number_format($item->line_total, 2) }}</p>
        </div>
        @endforeach
        <div class="bg-gray-50 p-5 space-y-1">
            <div class="flex justify-between text-sm text-gray-600">
                <span>Subtotal</span><span>${{ number_format($order->subtotal, 2) }}</span>
            </div>
            <div class="flex justify-between text-sm text-gray-600">
                <span>Shipping</span><span>${{ number_format($order->shipping_total, 2) }}</span>
            </div>
            <div class="flex justify-between text-lg font-black text-brand pt-2 border-t border-gray-200">
                <span>Total</span><span>${{ number_format($order->total, 2) }}</span>
            </div>
        </div>
    </div>

    <div class="grid sm:grid-cols-2 gap-6">
        <div class="bg-gray-50 rounded-xl border border-gray-200 p-6">
            <h2 class="text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Shipping Address</h2>
            @if($order->shippingAddress)
            <p class="text-sm text-gray-700 leading-relaxed">
                {{ $order->shippingAddress->full_name }}<br>
                {{ $order->shippingAddress->line1 }}<br>
                @if($order->shippingAddress->line2){{ $order->shippingAddress->line2 }}<br>@endif
                {{ $order->shippingAddress->suburb }} {{ $order->shippingAddress->state }} {{ $order->shippingAddress->postcode }}
            </p>
            @endif
        </div>
        <div class="bg-gray-50 rounded-xl border border-gray-200 p-6">
            <h2 class="text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Payment</h2>
            <p class="text-sm text-gray-700">{{ $order->payment_method?->label() ?? '—' }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $order->paid_at ? 'Paid ' . $order->paid_at->format('d M Y, g:ia') : 'Not yet paid' }}</p>
        </div>
    </div>

</div>

@endsection
