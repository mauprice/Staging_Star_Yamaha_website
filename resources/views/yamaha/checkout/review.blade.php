@extends('yamaha.layout')

@section('title', 'Review Your Order')
@section('meta_description', 'Review and confirm your direct deposit order at Star Yamaha.')

@section('content')

    <div class="bg-ink border-b-2 border-brand">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <nav class="text-gray-500 text-xs uppercase tracking-widest font-semibold mb-4">
                <a href="{{ route('yamaha.cart.index') }}" class="hover:text-white transition">Cart</a>
                <span class="mx-2">›</span>
                <a href="{{ route('yamaha.checkout.index') }}" class="hover:text-white transition">Checkout</a>
                <span class="mx-2">›</span>
                <span class="text-white">Review</span>
            </nav>
            <h1 class="text-4xl font-black uppercase text-white tracking-tight">Review Your Order</h1>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <div class="mb-8 p-5 rounded-lg border-l-4 border-brand bg-brand-tint text-gray-800">
            <p class="font-black text-sm mb-1">No payment is taken yet</p>
            <p class="text-sm">You're paying by direct deposit. Confirm below to place your order — we'll email you our bank details, and your order ships once we've verified the deposit.</p>
        </div>

        <div class="grid lg:grid-cols-3 gap-10">

            <div class="lg:col-span-2 space-y-6">

                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 class="text-sm font-black uppercase tracking-widest text-gray-900 mb-3">Contact Details</h2>
                    <p class="text-sm text-gray-700">{{ $validated['name'] }}</p>
                    <p class="text-sm text-gray-700">{{ $validated['email'] }}</p>
                    @if(!empty($validated['phone']))
                    <p class="text-sm text-gray-700">{{ $validated['phone'] }}</p>
                    @endif
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 class="text-sm font-black uppercase tracking-widest text-gray-900 mb-3">Shipping Address</h2>
                    <p class="text-sm text-gray-700 leading-relaxed">
                        {{ $validated['line1'] }}<br>
                        @if(!empty($validated['line2'])){{ $validated['line2'] }}<br>@endif
                        {{ $validated['suburb'] }} {{ $validated['state'] }} {{ $validated['postcode'] }}
                    </p>
                </div>

                @if(!empty($validated['different_billing']))
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 class="text-sm font-black uppercase tracking-widest text-gray-900 mb-3">Billing Address</h2>
                    <p class="text-sm text-gray-700 leading-relaxed">
                        {{ $validated['billing_line1'] }}<br>
                        @if(!empty($validated['billing_line2'])){{ $validated['billing_line2'] }}<br>@endif
                        {{ $validated['billing_suburb'] }} {{ $validated['billing_state'] }} {{ $validated['billing_postcode'] }}
                    </p>
                </div>
                @endif

                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 class="text-sm font-black uppercase tracking-widest text-gray-900 mb-3">Payment Method</h2>
                    <p class="text-sm text-gray-700 font-black">Direct Deposit</p>
                    <p class="text-xs text-gray-500 mt-1">Bank details and your reference number will be emailed to you once you confirm.</p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <form method="POST" action="{{ route('yamaha.checkout.confirm') }}">
                        @csrf
                        <x-btn type="submit" variant="primary" class="w-full sm:w-auto">
                            Confirm &amp; Place Order →
                        </x-btn>
                    </form>
                    <a href="{{ route('yamaha.checkout.index') }}" class="inline-flex items-center justify-center text-sm font-semibold text-gray-500 hover:text-gray-700 transition">
                        ← Edit Details
                    </a>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-gray-50 rounded-xl border border-gray-200 p-6 sticky top-24">
                    <h2 class="text-sm font-black uppercase tracking-widest text-gray-900 mb-4">Order Summary</h2>
                    <div class="divide-y divide-gray-200">
                        @foreach($items as $item)
                        <div class="flex justify-between gap-3 py-3 text-sm">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $item->display_name }}</p>
                                <p class="text-xs text-gray-400">Qty {{ $item->quantity }}</p>
                            </div>
                            <p class="font-black text-gray-900 whitespace-nowrap">${{ number_format($item->line_total, 2) }}</p>
                        </div>
                        @endforeach
                    </div>
                    <div class="border-t border-gray-200 mt-4 pt-4 space-y-2 text-sm">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal</span>
                            <span>${{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Shipping</span>
                            <span>{{ $shippingTotal > 0 ? '$' . number_format($shippingTotal, 2) : 'Free' }}</span>
                        </div>
                        <div class="flex justify-between text-lg font-black text-brand pt-2 border-t border-gray-200">
                            <span>Total Due</span>
                            <span>${{ number_format($total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection
