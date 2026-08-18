@extends('yamaha.layout')

@section('title', 'Delivery Information')
@section('meta_description', 'Star Yamaha delivery information — shipping rates, timeframes and stock availability.')
@section('canonical', url('/delivery-information'))

@section('content')

    {{-- Page header --}}
    <div class="bg-ink border-b-2 border-brand">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <nav class="text-gray-500 text-xs uppercase tracking-widest font-semibold mb-4">
                <a href="{{ route('yamaha.index') }}" class="hover:text-white transition">Home</a>
                <span class="mx-2">›</span>
                <span class="text-white">Delivery Information</span>
            </nav>
            <h1 class="text-4xl font-black uppercase text-white tracking-tight">Delivery Information</h1>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="prose prose-gray max-w-none text-gray-600 leading-relaxed">

            <h2>Standard Flat Rate Shipping — $14.95*</h2>
            <p>
                Shipping your purchases directly to your door is an easy process. Depending on your location, Australia Post services will
                deliver your item usually within 5 working days of order (standard is 2 days for metro areas).
            </p>
            <p>All items use fixed-weight shipping regardless of location, enabling delivery to remote Australian areas at reasonable rates.</p>
            <p><em>*Note: bulky items may incur a surcharge, which will be quoted before payment and dispatch.</em></p>

            <h2>Express Post Flat Rate Shipping — $19.95*</h2>
            <p>Express Post orders will be delivered within 1–2 business days.</p>
            <p><em>*Note: bulky items may incur a surcharge, which will be quoted before payment and dispatch.</em></p>

            <h2>Tasmania</h2>
            <p>Shipping to Tasmania will incur a flat rate fee of $30.*</p>
            <p><em>*Note: bulky items may incur a surcharge, which will be quoted before payment and dispatch.</em></p>

            <h2>Online Stock</h2>
            <p>
                Although every attempt is made to ensure the stock displayed on our website is in our shop ready for distribution,
                occasionally this is not always the case due to our manual stock adjustment process.
            </p>
            <p>
                Prices, offers and product availability are subject to change. We reserve the right to refuse orders or cancel them
                if errors occur, including incorrect pricing. Should payment be processed erroneously, a full refund will be issued.
            </p>

            <h2>Questions?</h2>
            <p>
                Contact our team for any queries about delivery:<br>
                Phone: <a href="{{ config('dealership.phone.href') }}">{{ config('dealership.phone.display') }}</a><br>
                Email: <a href="mailto:{{ config('dealership.email.sales') }}">{{ config('dealership.email.sales') }}</a>
            </p>

        </div>
    </div>

@endsection
