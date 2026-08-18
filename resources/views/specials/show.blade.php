@extends('yamaha.layout')

@section('title', $special->title . ' — Specials')
@section('og_image')
{{ $special->featured_image ? asset('storage/' . $special->featured_image) : url('/images/star_yamaha_honda_logo.png') }}
@endsection
@section('og_type', 'article')
@section('meta_description', $special->title . ' — special offer at Star Yamaha. Limited time only, contact us for details.')

@section('content')

    <div class="bg-ink border-b-2 border-brand">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <nav class="text-gray-500 text-xs uppercase tracking-widest font-semibold mb-4">
                <a href="{{ route('yamaha.index') }}" class="hover:text-white transition">Home</a>
                <span class="mx-2">›</span>
                <a href="{{ route('yamaha.specials') }}" class="hover:text-white transition">Specials</a>
                <span class="mx-2">›</span>
                <span class="text-white">{{ $special->title }}</span>
            </nav>
            <h1 class="text-4xl font-black uppercase text-white tracking-tight">{{ $special->title }}</h1>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">

            {{-- Images --}}
            <div class="lg:col-span-2 space-y-4">
                @php
                    $mainImage = $special->featured_image
                        ?? (is_array($special->images) && count($special->images) ? $special->images[0] : null);
                @endphp
                @if($mainImage)
                <div class="rounded-xl overflow-hidden bg-gray-100 relative" style="aspect-ratio:4/3;">
                    <img src="{{ asset('storage/' . $mainImage) }}"
                         alt="{{ $special->title }}"
                         class="w-full h-full object-cover" id="main-image">
                    @if($special->special_price && $special->regular_price && $special->special_price < $special->regular_price)
                    <span class="absolute top-4 left-4 bg-brand text-white text-sm font-black uppercase tracking-widest px-3 py-1.5 rounded shadow">
                        Sale!
                    </span>
                    @endif
                </div>
                @endif

                @if($special->images && count($special->images) > 0)
                <div class="grid grid-cols-4 gap-2">
                    @if($special->featured_image)
                    <button onclick="document.getElementById('main-image').src='{{ asset('storage/' . $special->featured_image) }}'"
                            class="rounded-lg overflow-hidden border-2 border-brand transition" style="aspect-ratio:1;">
                        <img src="{{ asset('storage/' . $special->featured_image) }}" class="w-full h-full object-cover" alt="">
                    </button>
                    @endif
                    @foreach($special->images as $img)
                    <button onclick="document.getElementById('main-image').src='{{ asset('storage/' . $img) }}'"
                            class="rounded-lg overflow-hidden border-2 border-transparent hover:border-brand transition" style="aspect-ratio:1;">
                        <img src="{{ asset('storage/' . $img) }}" class="w-full h-full object-cover" alt="">
                    </button>
                    @endforeach
                </div>
                @endif

                @if($special->description)
                <div class="mt-6">
                    <h2 class="text-xs uppercase tracking-widest font-black text-brand mb-3">Description</h2>
                    <div class="prose prose-gray max-w-none text-gray-600 leading-relaxed">
                        {!! nl2br(e($special->description)) !!}
                    </div>
                </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-5">
                <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                    @if($special->special_price && $special->regular_price && $special->special_price < $special->regular_price)
                        <p class="text-sm text-gray-400 line-through mb-0.5">${{ number_format($special->regular_price, 0) }}.00</p>
                        <p class="text-4xl font-black text-brand mb-1">${{ number_format($special->special_price, 0) }}.00</p>
                        <p class="text-xs font-black uppercase tracking-widest text-green-600 mb-4">
                            Save ${{ number_format($special->regular_price - $special->special_price, 0) }}
                        </p>
                    @elseif($special->special_price)
                        <p class="text-4xl font-black text-brand mb-1">${{ number_format($special->special_price, 0) }}.00</p>
                        <div class="mb-4"></div>
                    @elseif($special->regular_price)
                        <p class="text-4xl font-black text-gray-900 mb-1">${{ number_format($special->regular_price, 0) }}.00</p>
                        <div class="mb-4"></div>
                    @else
                        <p class="text-lg text-gray-500 italic mb-4">Contact us for pricing</p>
                    @endif

                    <p class="text-xs text-gray-400 border-t border-gray-200 pt-4">
                        Ride-away price — conditions apply. While stocks last.
                    </p>
                </div>

                <div class="rounded-xl p-6 text-white bg-accent">
                    <h3 class="font-black text-base uppercase mb-1">Interested in This Offer?</h3>
                    <p class="text-blue-100/80 text-sm mb-4">Contact our team to find out more or arrange a test ride.</p>
                    <a href="mailto:info@staryamaha.com.au?subject=Special Enquiry: {{ $special->title }}"
                       class="block text-center font-black py-3 px-6 rounded-lg text-white mb-2 transition bg-brand hover:bg-brand-dark">
                        Email Enquiry
                    </a>
                    <a href="{{ config('dealership.phone.href') }}" class="block text-center text-blue-100/80 text-sm hover:text-white transition">
                        Call {{ config('dealership.phone.display') }} →
                    </a>
                </div>

                <a href="{{ route('yamaha.specials') }}"
                   class="block text-center text-sm font-semibold text-gray-500 hover:text-gray-700 transition py-2">
                    ← Back to Specials
                </a>
            </div>
        </div>
    </div>

    <script>
        // Highlight active thumbnail
        document.querySelectorAll('#main-image').length && document.querySelectorAll('[onclick]').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('[onclick]').forEach(b => {
                    b.classList.remove('border-brand');
                    b.classList.add('border-transparent');
                });
                this.classList.add('border-brand');
                this.classList.remove('border-transparent');
            });
        });
    </script>

@endsection
