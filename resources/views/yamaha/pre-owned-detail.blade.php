@extends('yamaha.layout')

@section('title', $listing->title . ' — Pre-Owned')
@section('og_image')
{{ $listing->featured_image ? asset('storage/' . $listing->featured_image) : url('/images/star_yamaha_honda_logo.png') }}
@endsection
@section('og_type', 'product')
@section('meta_description', $listing->title . ' — pre-owned vehicle for sale at Star Yamaha. Contact us for pricing and availability.')

@section('content')

    <div class="bg-ink border-b-2 border-brand">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <nav class="text-gray-500 text-xs uppercase tracking-widest font-semibold mb-4">
                <a href="{{ route('yamaha.index') }}" class="hover:text-white transition">Home</a>
                <span class="mx-2">›</span>
                <a href="{{ route('yamaha.preowned') }}" class="hover:text-white transition">Pre-Owned</a>
                <span class="mx-2">›</span>
                <span class="text-white">{{ $listing->title }}</span>
            </nav>
            <h1 class="text-4xl font-black uppercase text-white tracking-tight">{{ $listing->title }}</h1>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">

            {{-- Images --}}
            <div class="lg:col-span-2 space-y-4">
                @if($listing->featured_image)
                <div class="rounded-xl overflow-hidden bg-gray-100" style="aspect-ratio:4/3;">
                    <img src="{{ asset('storage/' . $listing->featured_image) }}"
                         alt="{{ $listing->title }}"
                         class="w-full h-full object-cover" id="main-image">
                </div>
                @endif

                @if($listing->images && count($listing->images) > 0)
                <div class="grid grid-cols-4 gap-2">
                    @foreach($listing->images as $img)
                    <button onclick="document.getElementById('main-image').src='{{ asset('storage/' . $img) }}'"
                            class="rounded-lg overflow-hidden border-2 border-transparent hover:border-brand transition" style="aspect-ratio:1;">
                        <img src="{{ asset('storage/' . $img) }}" class="w-full h-full object-cover">
                    </button>
                    @endforeach
                </div>
                @endif

                @if($listing->description)
                <div class="mt-6">
                    <h2 class="text-xs uppercase tracking-widest font-black text-brand mb-3">Description</h2>
                    <div class="prose prose-gray max-w-none text-gray-600 leading-relaxed">
                        {!! nl2br(e($listing->description)) !!}
                    </div>
                </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-5">
                <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-xs font-black uppercase tracking-widest px-2 py-1 rounded"
                              style="background:#fef3c7; color:#92400e;">{{ $listing->condition }}</span>
                        <span class="text-xs text-gray-400 font-semibold uppercase">{{ $listing->category }}</span>
                    </div>

                    @if($listing->price)
                    <p class="text-4xl font-black text-brand mb-1">${{ number_format($listing->price, 0) }}</p>
                    <p class="text-xs text-gray-400 mb-4">Ride-away price — contact us for details</p>
                    @else
                    <p class="text-lg text-gray-500 italic mb-4">Contact us for pricing</p>
                    @endif

                    <dl class="space-y-2 text-sm border-t border-gray-200 pt-4">
                        @foreach([
                            'Year'         => $listing->year,
                            'Make'         => $listing->make,
                            'Model'        => $listing->model,
                            'Colour'       => $listing->colour,
                            'Registration' => $listing->rego,
                            'Odometer'     => $listing->kms ? number_format($listing->kms) . ' km' : null,
                        ] as $label => $value)
                        @if($value)
                        <div class="flex justify-between">
                            <dt class="text-gray-500 font-semibold">{{ $label }}</dt>
                            <dd class="text-gray-900 font-black">{{ $value }}</dd>
                        </div>
                        @endif
                        @endforeach
                    </dl>
                </div>

                <div class="rounded-xl p-6 text-white bg-accent">
                    <h3 class="font-black text-base uppercase mb-1">Interested?</h3>
                    <p class="text-blue-100/80 text-sm mb-4">Contact our team to arrange an inspection or test ride.</p>
                    <a href="mailto:sales@staryamaha.com.au?subject=Pre-Owned Enquiry: {{ $listing->title }}"
                       class="block text-center font-black py-3 px-6 rounded-lg text-white mb-2 transition bg-brand hover:bg-brand-dark">
                        Email Enquiry
                    </a>
                    <a href="{{ config('dealership.phone.href') }}" class="block text-center text-blue-100/80 text-sm hover:text-white transition">
                        Call {{ config('dealership.phone.display') }} →
                    </a>
                </div>

                <a href="{{ route('yamaha.preowned') }}"
                   class="block text-center text-sm font-semibold text-gray-500 hover:text-gray-700 transition py-2">
                    ← Back to Pre-Owned
                </a>
            </div>
        </div>
    </div>

@endsection
