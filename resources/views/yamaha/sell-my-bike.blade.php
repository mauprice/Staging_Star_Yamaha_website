@extends('yamaha.layout')

@section('title', 'Sell My Bike')
@section('meta_description', 'Looking to sell your bike? NorthStar Yamaha buys quality used motorcycles and powersports vehicles. Get in touch for a fast, fair valuation.')

@section('content')

    {{-- Hero --}}
    <div class="relative text-white overflow-hidden flex items-center justify-center bg-ink" style="min-height:320px;">
        <div class="absolute inset-0" style="background:url('https://images.unsplash.com/photo-1567345600613-6e3f4b4e5394?w=1400&q=80') center/cover no-repeat; opacity:0.25;"></div>
        <div class="relative text-center px-4 py-16">
            <p class="text-xs uppercase tracking-[0.3em] font-black text-red-400 mb-3">We Buy Pre-Owned Vehicles</p>
            <h1 class="text-3xl sm:text-5xl md:text-7xl font-black uppercase leading-none tracking-tight">Sell Your Bike</h1>
            <p class="text-gray-300 mt-4 text-lg">Call us on <a href="{{ config('dealership.phone.href') }}" class="text-white font-black hover:text-red-400 transition">{{ config('dealership.phone.display') }}</a></p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">

            {{-- Form --}}
            <div class="lg:col-span-2">

                @if(session('success'))
                <div class="mb-8 p-5 rounded-lg border-l-4 border-green-500 bg-green-50 text-green-800">
                    <p class="font-black text-sm">{{ session('success') }}</p>
                </div>
                @endif

                @if($errors->any())
                <div class="mb-8 p-5 rounded-lg border-l-4 border-red-500 bg-red-50 text-red-800">
                    <p class="font-black text-sm mb-2">Please fix the following:</p>
                    <ul class="list-disc list-inside text-sm space-y-1">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
                @endif

                <h2 class="text-2xl font-black uppercase text-gray-900 mb-6 tracking-tight">Request a Valuation</h2>

                <form method="POST" action="{{ route('yamaha.sell.store') }}" class="space-y-6">
                    @csrf

                    <fieldset class="border border-gray-200 rounded-lg p-6">
                        <legend class="text-xs font-black uppercase tracking-widest text-brand px-2">Your Details</legend>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mt-2">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wide text-gray-600 mb-1">Full Name *</label>
                                <input type="text" name="name" value="{{ old('name') }}" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-red-500 @error('name') border-red-400 @enderror"
                                       placeholder="Jane Smith">
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wide text-gray-600 mb-1">Phone *</label>
                                <input type="tel" name="phone" value="{{ old('phone') }}" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-red-500"
                                       placeholder="07 3000 0000">
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wide text-gray-600 mb-1">Email *</label>
                                <input type="email" name="email" value="{{ old('email') }}" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-red-500"
                                       placeholder="jane@example.com">
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wide text-gray-600 mb-1">Suburb *</label>
                                <input type="text" name="suburb" value="{{ old('suburb') }}" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-red-500"
                                       placeholder="North Lakes">
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="border border-gray-200 rounded-lg p-6">
                        <legend class="text-xs font-black uppercase tracking-widest text-brand px-2">Your Vehicle</legend>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mt-2">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wide text-gray-600 mb-1">Year *</label>
                                <input type="number" name="year" value="{{ old('year') }}" required
                                       min="1970" max="{{ date('Y') + 1 }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-red-500"
                                       placeholder="{{ date('Y') }}">
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wide text-gray-600 mb-1">Make *</label>
                                <input type="text" name="make" value="{{ old('make') }}" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-red-500"
                                       placeholder="Yamaha">
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wide text-gray-600 mb-1">Model *</label>
                                <input type="text" name="model" value="{{ old('model') }}" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-red-500"
                                       placeholder="MT-07">
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wide text-gray-600 mb-1">Odometer (km)</label>
                                <input type="number" name="kms" value="{{ old('kms') }}" min="0"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-red-500"
                                       placeholder="15000">
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wide text-gray-600 mb-1">Asking Price ($)</label>
                                <input type="number" name="asking_price" value="{{ old('asking_price') }}" min="0"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-red-500"
                                       placeholder="Leave blank if open to offers">
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wide text-gray-600 mb-1">Condition *</label>
                                <select name="condition" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-red-500 bg-white">
                                    <option value="">— Select —</option>
                                    <option value="Excellent" {{ old('condition') === 'Excellent' ? 'selected' : '' }}>Excellent</option>
                                    <option value="Good" {{ old('condition') === 'Good' ? 'selected' : '' }}>Good</option>
                                    <option value="Fair" {{ old('condition') === 'Fair' ? 'selected' : '' }}>Fair</option>
                                    <option value="Needs Work" {{ old('condition') === 'Needs Work' ? 'selected' : '' }}>Needs Work</option>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-black uppercase tracking-wide text-gray-600 mb-1">Additional Information</label>
                                <textarea name="message" rows="4"
                                          class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-red-500 resize-none"
                                          placeholder="Mods, service history, known issues, reason for selling…">{{ old('message') }}</textarea>
                            </div>
                        </div>
                    </fieldset>

                    <button type="submit"
                            class="w-full py-4 font-black text-sm uppercase tracking-widest rounded-lg text-white transition bg-brand hover:bg-brand-dark">
                        Send Valuation Request →
                    </button>
                    <p class="text-xs text-gray-400 text-center">We'll get back to you within 1 business day.</p>
                </form>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                <div class="bg-ink rounded-xl p-6 text-white">
                    <p class="text-xs uppercase tracking-widest font-black text-brand mb-4">Why Sell to Us?</p>
                    <ul class="space-y-4 text-sm text-gray-300">
                        @foreach([
                            ['No Safety Certificate (RWC) required', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                            ['Money paid within 24 hours', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                            ['We can pay out your existing finance', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                            ['Honest and open about pricing', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                            ['We come to you', 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z'],
                        ] as [$point, $icon])
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-brand flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
                            </svg>
                            {{ $point }}
                        </li>
                        @endforeach
                    </ul>
                </div>

                <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                    <p class="text-xs uppercase tracking-widest font-black text-brand mb-3">Prefer to Call?</p>
                    <a href="{{ config('dealership.phone.href') }}" class="block text-2xl font-black text-gray-900 hover:text-brand transition">
                        {{ config('dealership.phone.display') }}
                    </a>
                    <p class="text-xs text-gray-400 mt-1">Mon–Fri 8:30am–5:30pm · Sat 8:30am–4pm</p>
                </div>

                <a href="{{ route('yamaha.preowned') }}"
                   class="block text-center text-sm font-semibold text-gray-500 hover:text-gray-700 py-2 transition">
                    ← View Current Pre-Owned Stock
                </a>
            </div>
        </div>
    </div>

@endsection
