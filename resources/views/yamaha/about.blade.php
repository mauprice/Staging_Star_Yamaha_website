@extends('yamaha.layout')

@section('title', 'About Us')
@section('meta_description', 'Star Yamaha is a family-owned Yamaha Superstore on Brisbane\'s Northside. Learn about our dealership, owners Michael and Lisa Edwards, and our history.')
@section('canonical', url('/about-us'))

@section('content')

    {{-- Page header --}}
    <div class="bg-ink border-b-2 border-brand">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <nav class="text-gray-500 text-xs uppercase tracking-widest font-semibold mb-4">
                <a href="{{ route('yamaha.index') }}" class="hover:text-white transition">Home</a>
                <span class="mx-2">›</span>
                <span class="text-white">About Us</span>
            </nav>
            <h1 class="text-4xl font-black uppercase text-white tracking-tight">About Star Yamaha</h1>
            <p class="text-gray-400 mt-2 text-sm">Brisbane's Northside Yamaha Superstore — family owned &amp; operated.</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">

            {{-- Main content --}}
            <div class="lg:col-span-2 space-y-12">

                {{-- About --}}
                <section>
                    <p class="text-xs uppercase tracking-[0.3em] font-black text-brand mb-2">Who We Are</p>
                    <h2 class="text-3xl font-black uppercase text-gray-900 mb-6 tracking-tight">Brisbane's Yamaha Superstore</h2>

                    <div class="prose prose-gray max-w-none text-gray-600 leading-relaxed space-y-4">
                        <p>
                            Star Yamaha is a Yamaha Superstore, a family business located on Brisbane's Northside and privately owned by
                            Michael Edwards. We are proud to be one of Queensland's premier Yamaha dealers and the
                            first site in the region to carry the full Yamaha range under one roof: motorbikes, SSV, WaveRunners and golf cars.
                        </p>
                        <p>
                            Our North Lakes dealership enjoys a prominent position on Flinders Parade, just down from Bunnings and Costco,
                            with street frontage on the North-South arterial road and visible from the Bruce Highway. The site features a large
                            showroom, outdoor display, accessories area and a fully equipped workshop. We are large enough to offer competitive
                            prices, yet small enough to give personalised service; and one of the few Brisbane dealerships where you can deal
                            directly with the business owner.
                        </p>
                        <p>
                            Every staff member at Star Yamaha is a motorcycle or WaveRunner enthusiast; when Michael isn't on a bike, he's out on the water enjoying the Yamaha lifestyle.
                        </p>
                    </div>
                </section>

                {{-- History --}}
                <section>
                    <p class="text-xs uppercase tracking-[0.3em] font-black text-brand mb-2">Our History</p>
                    <h2 class="text-3xl font-black uppercase text-gray-900 mb-6 tracking-tight">Racing Roots &amp; Family Heritage</h2>

                    <div class="prose prose-gray max-w-none text-gray-600 leading-relaxed space-y-4">
                        <p>
                            Michael Edwards has raced in the Australian Superbike Championship for many years, channelling that
                            competitive energy directly into running Star Yamaha. His passion for motorsport shapes everything we do, from the
                            bikes we stock to the way we service them.
                        </p>
                        <p>
                            The dealership has deep family roots in the Yamaha network. Michael's brother Matthew established Star Yamaha
                            in the mid-1990s, which grew to become Australia's Number 1 Yamaha Dealer. Building on that legacy, the North Lakes
                            dealership replaced the former Toombul store (established 2000) and the Bowen Hills location (established 2012),
                            bringing everything under one roof on the Northside.
                        </p>
                    </div>
                </section>

                {{-- What we offer --}}
                <section>
                    <p class="text-xs uppercase tracking-[0.3em] font-black text-brand mb-2">What We Offer</p>
                    <h2 class="text-3xl font-black uppercase text-gray-900 mb-6 tracking-tight">Full-Service Dealership</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach([
                            ['icon'=>'M12 2a10 10 0 100 20A10 10 0 0012 2zm0 18a8 8 0 110-16 8 8 0 010 16z M12 6v6l4 2', 'title'=>'New Yamaha Vehicles', 'desc'=>'Full range of road, off-road, watercraft, ATV/ROV and golf car.'],
                            ['icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'title'=>'Genuine Parts & Accessories', 'desc'=>'Extensive in-store range plus our online parts finder for genuine Yamaha parts.'],
                            ['icon'=>'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z', 'title'=>'Workshop & Service', 'desc'=>'Qualified technicians servicing and repairing most brands of bikes and watercraft, plus tyres and accessories.'],
                            ['icon'=>'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'title'=>'Finance', 'desc'=>'Yamaha Motor Finance options to help you own your vehicle sooner.'],
                            ['icon'=>'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'title'=>'Insurance', 'desc'=>'Yamaha Motor Insurance (YMI) quotes for motorcycles, watercraft and more.'],
                            ['icon'=>'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z', 'title'=>'Pre-Owned Vehicles', 'desc'=>'Inspected and quality-checked pre-owned motorcycles and powersports vehicles.'],
                        ] as $item)
                        <div class="flex gap-4 p-5 rounded-xl border border-gray-100 bg-gray-50">
                            <svg class="w-6 h-6 text-brand flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $item['icon'] }}"/>
                            </svg>
                            <div>
                                <h3 class="font-black text-gray-900 text-sm uppercase tracking-wide">{{ $item['title'] }}</h3>
                                <p class="text-xs text-gray-500 mt-1 leading-relaxed">{{ $item['desc'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </section>

                {{-- Map --}}
                <section id="find-us">
                    <p class="text-xs uppercase tracking-[0.3em] font-black text-brand mb-2">Find Us</p>
                    <h2 class="text-3xl font-black uppercase text-gray-900 mb-6 tracking-tight">Our Location</h2>
                    <div class="rounded-xl overflow-hidden border border-gray-200 shadow-sm" style="height:400px;">
                        <iframe
                            src="https://www.openstreetmap.org/export/embed.html?bbox=152.9970%2C-27.2270%2C153.0070%2C-27.2237&layer=mapnik&marker=-27.225324%2C153.002027"
                            width="100%" height="100%" style="border:0;" loading="lazy"
                            title="Star Yamaha on OpenStreetMap"
                            referrerpolicy="no-referrer">
                        </iframe>
                    </div>
                    <p class="text-xs text-gray-400 mt-2 text-center">
                        {{ config('dealership.address.full') }} —
                        <a href="https://www.openstreetmap.org/?mlat={{ config('dealership.map.lat') }}&mlon={{ config('dealership.map.lng') }}#map=17/{{ config('dealership.map.lat') }}/{{ config('dealership.map.lng') }}"
                           target="_blank" rel="noopener" class="text-brand hover:underline">
                            Get Directions →
                        </a>
                    </p>
                </section>

            </div>

            {{-- Sidebar --}}
            <div class="space-y-6" id="contact">

                {{-- Contact card --}}
                <div class="bg-ink rounded-xl p-6 text-white">
                    <p class="text-xs uppercase tracking-widest font-black text-brand mb-5">Contact Us</p>

                    <div class="space-y-5">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-brand flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <div class="text-sm text-gray-300 leading-relaxed">
                                {{ config('dealership.address.street') }}<br>
                                {{ config('dealership.address.suburb') }} {{ config('dealership.address.state') }} {{ config('dealership.address.postcode') }}
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-brand flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            <div>
                                <a href="{{ config('dealership.phone.href') }}"
                                   class="block text-sm text-white hover:text-brand transition font-semibold">
                                    {{ config('dealership.phone.display') }}
                                </a>
                                <span class="text-xs text-gray-500">Fax: {{ config('dealership.fax') }}</span>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-brand flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <div class="space-y-1">
                                <a href="mailto:{{ config('dealership.email.sales') }}"
                                   class="block text-sm text-gray-300 hover:text-white transition break-all">
                                    {{ config('dealership.email.sales') }}
                                </a>
                                <a href="mailto:{{ config('dealership.email.service') }}"
                                   class="block text-sm text-gray-300 hover:text-white transition break-all">
                                    {{ config('dealership.email.service') }}
                                </a>
                                <a href="mailto:{{ config('dealership.email.spares') }}"
                                   class="block text-sm text-gray-300 hover:text-white transition break-all">
                                    {{ config('dealership.email.spares') }}
                                </a>
                                <a href="mailto:{{ config('dealership.email.enquiries') }}"
                                   class="block text-sm text-gray-300 hover:text-white transition break-all">
                                    {{ config('dealership.email.enquiries') }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-800 mt-6 pt-5">
                        <a href="mailto:{{ config('dealership.email.sales') }}"
                           class="block text-center font-black text-sm py-3 px-6 rounded-lg text-white uppercase tracking-widest transition bg-brand hover:bg-brand-dark">
                            Email Us →
                        </a>
                    </div>
                </div>

                {{-- Trading hours --}}
                <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                    <p class="text-xs uppercase tracking-widest font-black text-brand mb-4">Trading Hours</p>
                    <div class="space-y-2 text-sm">
                        @foreach(config('dealership.hours') as $row)
                        <div class="flex justify-between items-center py-1.5 border-b border-gray-100 last:border-0">
                            <span class="font-semibold text-gray-700">{{ $row['days'] }}</span>
                            <span class="{{ $row['closed'] ? 'text-brand font-semibold' : 'text-gray-600' }}">{{ $row['hours'] }}</span>
                        </div>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-400 mt-3 leading-relaxed">
                        Always check Google for Public Holiday hours.
                    </p>
                </div>

                {{-- Social --}}
                <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                    <p class="text-xs uppercase tracking-widest font-black text-brand mb-4">Follow Us</p>
                    <div class="space-y-3">
                        <a href="{{ config('dealership.social.facebook') }}" target="_blank" rel="noopener"
                           class="flex items-center gap-3 text-sm font-semibold text-gray-700 hover:text-blue-600 transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                            Star Yamaha
                        </a>
                        <a href="{{ config('dealership.social.instagram') }}" target="_blank" rel="noopener"
                           class="flex items-center gap-3 text-sm font-semibold text-gray-700 hover:text-pink-600 transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                            @staryamaha
                        </a>
                    </div>
                </div>

                {{-- Service CTA --}}
                <a href="{{ route('yamaha.service') }}"
                   class="block rounded-xl p-5 text-white text-center transition bg-brand hover:bg-brand-dark">
                    <p class="font-black text-base uppercase tracking-wide">Book a Service</p>
                    <p class="text-red-100 text-xs mt-1">Tyres, servicing &amp; repairs</p>
                </a>

            </div>
        </div>
    </div>

@endsection
