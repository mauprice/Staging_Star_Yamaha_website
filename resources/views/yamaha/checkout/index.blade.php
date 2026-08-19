@extends('yamaha.layout')

@section('title', 'Checkout')
@section('meta_description', 'Complete your purchase at Star Yamaha.')

@section('content')

    <div class="bg-ink border-b-2 border-brand">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <nav class="text-gray-500 text-xs uppercase tracking-widest font-semibold mb-4">
                <a href="{{ route('yamaha.cart.index') }}" class="hover:text-white transition">Cart</a>
                <span class="mx-2">›</span>
                <span class="text-white">Checkout</span>
            </nav>
            <h1 class="text-4xl font-black uppercase text-white tracking-tight">Checkout</h1>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        @if(session('error'))
        <div class="mb-8 p-5 rounded-lg border-l-4 border-red-500 bg-red-50 text-red-800">
            <p class="font-black text-sm">{{ session('error') }}</p>
        </div>
        @endif

        @if($errors->any())
        <div class="mb-8 p-5 rounded-lg border-l-4 border-red-500 bg-red-50 text-red-800">
            <p class="font-black text-sm mb-1">Please fix the following:</p>
            <ul class="text-sm list-disc list-inside">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="grid lg:grid-cols-3 gap-10">

            <div class="lg:col-span-2">

                @auth
                <div class="mb-6 p-4 rounded-lg bg-gray-50 border border-gray-200 text-sm text-gray-700">
                    Checking out as <span class="font-black">{{ auth()->user()->email }}</span>.
                </div>
                @else
                <div class="mb-6 p-4 rounded-lg bg-gray-50 border border-gray-200 text-sm text-gray-700 flex flex-wrap items-center gap-3">
                    <span>Have an account?</span>
                    <a href="{{ route('login', ['redirect' => route('yamaha.checkout.index')]) }}" class="font-black text-brand hover:text-brand-dark">Log in</a>
                    <span class="text-gray-300">|</span>
                    <span>New here?</span>
                    <a href="{{ route('register', ['redirect' => route('yamaha.checkout.index')]) }}" class="font-black text-brand hover:text-brand-dark">Create an account</a>
                    <span class="text-gray-400">— or just fill in your details below to check out as a guest.</span>
                </div>
                @endauth

                <form method="POST" action="{{ route('yamaha.checkout.store') }}" x-data="{ differentBilling: {{ old('different_billing', $prefill['different_billing'] ?? false) ? 'true' : 'false' }} }">
                    @csrf

                    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                        <h2 class="text-sm font-black uppercase tracking-widest text-gray-900 mb-4">Contact Details</h2>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-500 mb-1">Full Name</label>
                                <input type="text" name="name" value="{{ old('name', $prefill['name'] ?? (auth()->user()->name ?? '')) }}" required
                                       class="w-full border border-gray-300 rounded-lg p-3 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1">Email</label>
                                <input type="email" name="email" value="{{ old('email', $prefill['email'] ?? (auth()->user()->email ?? '')) }}" required
                                       class="w-full border border-gray-300 rounded-lg p-3 text-sm">
                                @auth
                                <p class="text-xs text-gray-400 mt-1">Defaults to your account email — change it if you'd like this order's confirmation sent elsewhere.</p>
                                @endauth
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1">Phone</label>
                                <input type="tel" name="phone" value="{{ old('phone', $prefill['phone'] ?? (auth()->user()->phone ?? '')) }}"
                                       class="w-full border border-gray-300 rounded-lg p-3 text-sm">
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                        <h2 class="text-sm font-black uppercase tracking-widest text-gray-900 mb-4">Shipping Address</h2>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-500 mb-1">Address Line 1</label>
                                <input type="text" name="line1" value="{{ old('line1', $prefill['line1'] ?? '') }}" required
                                       class="w-full border border-gray-300 rounded-lg p-3 text-sm">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-500 mb-1">Address Line 2 (optional)</label>
                                <input type="text" name="line2" value="{{ old('line2', $prefill['line2'] ?? '') }}"
                                       class="w-full border border-gray-300 rounded-lg p-3 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1">Suburb</label>
                                <input type="text" name="suburb" value="{{ old('suburb', $prefill['suburb'] ?? '') }}" required
                                       class="w-full border border-gray-300 rounded-lg p-3 text-sm">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 mb-1">State</label>
                                    <select name="state" required class="w-full border border-gray-300 rounded-lg p-3 text-sm">
                                        <option value="">—</option>
                                        @foreach(\App\Http\Controllers\CheckoutController::AU_STATES as $state)
                                        <option value="{{ $state }}" @selected(old('state', $prefill['state'] ?? null) === $state)>{{ $state }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 mb-1">Postcode</label>
                                    <input type="text" name="postcode" value="{{ old('postcode', $prefill['postcode'] ?? '') }}" required maxlength="10"
                                           class="w-full border border-gray-300 rounded-lg p-3 text-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-4">
                            <input type="checkbox" name="different_billing" value="1" x-model="differentBilling" class="rounded border-gray-300">
                            Use a different billing address
                        </label>

                        <div x-show="differentBilling" x-cloak class="grid sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-500 mb-1">Billing Address Line 1</label>
                                <input type="text" name="billing_line1" value="{{ old('billing_line1', $prefill['billing_line1'] ?? '') }}"
                                       class="w-full border border-gray-300 rounded-lg p-3 text-sm">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-500 mb-1">Billing Address Line 2 (optional)</label>
                                <input type="text" name="billing_line2" value="{{ old('billing_line2', $prefill['billing_line2'] ?? '') }}"
                                       class="w-full border border-gray-300 rounded-lg p-3 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1">Suburb</label>
                                <input type="text" name="billing_suburb" value="{{ old('billing_suburb', $prefill['billing_suburb'] ?? '') }}"
                                       class="w-full border border-gray-300 rounded-lg p-3 text-sm">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 mb-1">State</label>
                                    <select name="billing_state" class="w-full border border-gray-300 rounded-lg p-3 text-sm">
                                        <option value="">—</option>
                                        @foreach(\App\Http\Controllers\CheckoutController::AU_STATES as $state)
                                        <option value="{{ $state }}" @selected(old('billing_state', $prefill['billing_state'] ?? null) === $state)>{{ $state }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 mb-1">Postcode</label>
                                    <input type="text" name="billing_postcode" value="{{ old('billing_postcode', $prefill['billing_postcode'] ?? '') }}" maxlength="10"
                                           class="w-full border border-gray-300 rounded-lg p-3 text-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                        <h2 class="text-sm font-black uppercase tracking-widest text-gray-900 mb-4">Payment</h2>

                        @if(count($availableMethods) > 1)
                        <div class="space-y-3">
                            @foreach($availableMethods as $method)
                            <label class="flex items-start gap-3 p-4 border rounded-lg cursor-pointer transition {{ old('payment_method', $prefill['payment_method'] ?? $defaultMethod->value) === $method->value ? 'border-brand bg-brand-tint' : 'border-gray-200 hover:border-gray-300' }}">
                                <input type="radio" name="payment_method" value="{{ $method->value }}"
                                       {{ old('payment_method', $prefill['payment_method'] ?? $defaultMethod->value) === $method->value ? 'checked' : '' }}
                                       class="mt-0.5">
                                <span>
                                    <span class="block font-black text-sm text-gray-900">{{ $method->label() }}</span>
                                    <span class="block text-xs text-gray-500 mt-0.5">
                                        @if($method->value === 'stripe')
                                        Pay securely by card via Stripe. You'll be redirected to enter your details.
                                        @elseif($method->value === 'bank_transfer')
                                        We'll email you our bank details. Your order ships once we've confirmed the deposit.
                                        @endif
                                    </span>
                                </span>
                            </label>
                            @endforeach
                        </div>
                        @else
                        <input type="hidden" name="payment_method" value="{{ $defaultMethod->value }}">
                        @if($defaultMethod->value === 'stripe')
                        <p class="text-sm text-gray-500">You'll be redirected to Stripe's secure checkout to enter your card details. Your card details never touch our servers.</p>
                        @elseif($defaultMethod->value === 'bank_transfer')
                        <p class="text-sm text-gray-500">Pay by direct deposit — we'll email you our bank details. Your order ships once we've confirmed the deposit has been received.</p>
                        @endif
                        @endif
                    </div>

                    <x-btn type="submit" variant="primary" class="w-full sm:w-auto">
                        Continue to Payment →
                    </x-btn>
                </form>
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
                            <span>Total</span>
                            <span>${{ number_format($total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection
