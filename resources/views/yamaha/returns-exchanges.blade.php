@extends('yamaha.layout')

@section('title', 'Returns & Exchanges')
@section('meta_description', 'NorthStar Yamaha returns and exchanges policy — conditions, warranty returns and how to arrange a return.')
@section('canonical', url('/returns-exchanges'))

@section('content')

    {{-- Page header --}}
    <div class="bg-ink border-b-2 border-brand">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <nav class="text-gray-500 text-xs uppercase tracking-widest font-semibold mb-4">
                <a href="{{ route('yamaha.index') }}" class="hover:text-white transition">Home</a>
                <span class="mx-2">›</span>
                <span class="text-white">Returns &amp; Exchanges</span>
            </nav>
            <h1 class="text-4xl font-black uppercase text-white tracking-tight">Returns &amp; Exchanges</h1>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="prose prose-gray max-w-none text-gray-600 leading-relaxed">

            <h2>Non-Warranty Returns &amp; Exchanges</h2>
            <p>
                If you change your mind regarding a purchase, we are not required to provide a refund or replacement.
                Customers must contact our team within 14 days of receipt to request returns or exchanges.
            </p>
            <p>
                Please note:
            </p>
            <ul>
                <li>Safety items such as helmets cannot be returned due to health regulations.</li>
                <li>Spare parts that are incompatible with your motorcycle cannot be returned.</li>
                <li>For items damaged in transit, photograph the packaging within 48 hours of delivery and notify our customer service team.</li>
            </ul>

            <h2>Terms &amp; Conditions</h2>
            <p>
                All returns or exchanges <strong>must</strong> be authorised by our customer service team <strong>before</strong> goods are returned.
                Items must be returned within 14 days in their original, resaleable condition with all tags attached.
            </p>
            <p>
                Any item returned to us damaged or in an unsaleable condition will not be refunded and will be returned to the customer at the customer's expense.
                Customers are responsible for return shipping costs and exchange freight costs.
            </p>

            <h2>Warranty Returns</h2>
            <p>
                Warranty is subject to the Australian Consumer Law (ACL). Most items carry a 12-month limited warranty on craftsmanship and materials.
            </p>
            <p>
                Customers must notify our service team before returning any faulty items, providing:
            </p>
            <ul>
                <li>The invoice number and date of purchase</li>
                <li>A brief description of the fault</li>
                <li>A photograph showing the fault</li>
            </ul>
            <p>
                Manufacturer approval may be required before we can process a refund or replacement.
            </p>

            <h2>Contact Us</h2>
            <p>
                To arrange a return or exchange, please contact our team:<br>
                Phone: <a href="{{ config('dealership.phone.href') }}">{{ config('dealership.phone.display') }}</a><br>
                Email: <a href="mailto:{{ config('dealership.email.sales') }}">{{ config('dealership.email.sales') }}</a>
            </p>

        </div>
    </div>

@endsection
