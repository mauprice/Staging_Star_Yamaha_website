<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Yamaha Products') — NorthStar Yamaha</title>
    <meta name="description" content="@yield('meta_description', 'NorthStar Yamaha — authorised Yamaha dealer for motorcycles, scooters, watercraft, ATVs and more. Located in Australia.')">
    <link rel="canonical" href="@yield('canonical', url()->current())">

    <!-- Open Graph -->
    <meta property="og:site_name" content="NorthStar Yamaha">
    <meta property="og:locale" content="en_AU">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('title', 'Yamaha Products') — NorthStar Yamaha">
    <meta property="og:description" content="@yield('meta_description', 'NorthStar Yamaha — authorised Yamaha dealer for motorcycles, scooters, watercraft, ATVs and more. Located in Australia.')">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    <meta property="og:image" content="@yield('og_image', url('/images/star_yamaha_honda_logo.png'))">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'Yamaha Products') — NorthStar Yamaha">
    <meta name="twitter:description" content="@yield('meta_description', 'NorthStar Yamaha — authorised Yamaha dealer for motorcycles, scooters, watercraft, ATVs and more. Located in Australia.')">
    <meta name="twitter:image" content="@yield('og_image', url('/images/star_yamaha_honda_logo.png'))">

    <link rel="sitemap" type="application/xml" title="Sitemap" href="{{ route('sitemap') }}">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,700;1,900&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .nav-dropdown { display: none; }
        .nav-item:hover .nav-dropdown,
        .nav-item:focus-within .nav-dropdown { display: block; }

        /* Mega-menu: never overflow the viewport on narrow screens */
        .nav-mega { min-width: min(720px, 95vw); }

        /* Slider text: don't overflow on phones */
        .slider-text-block { max-width: min(36rem, 90vw); }
    </style>
</head>
<body class="bg-white text-gray-900 font-sans antialiased">

    {{-- Nav --}}
    <nav class="bg-white border-b-2 border-brand sticky top-0 z-50 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between" style="height: 80px;">

                {{-- Logo --}}
                <a href="{{ route('yamaha.index') }}" class="flex-shrink-0 py-2">
                    <img src="/images/star_yamaha_honda_logo.png"
                         alt="NorthStar Yamaha"
                         style="height: 60px; width: auto;">
                </a>

                {{-- Desktop Nav --}}
                <div class="hidden lg:flex items-stretch h-full text-xs font-bold uppercase tracking-wide">

                    {{-- Yamaha Products mega-menu --}}
                    <div class="nav-item relative flex items-center">
                        <a href="{{ route('yamaha.index') }}"
                           class="flex items-center gap-1 px-4 h-full text-gray-700 hover:text-brand border-b-2 border-transparent hover:border-brand transition-colors whitespace-nowrap">
                            Yamaha Products
                            <svg class="w-3 h-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </a>
                        {{-- Mega dropdown --}}
                        <div class="nav-dropdown nav-mega absolute top-full bg-white shadow-2xl border-t-2 border-brand z-50 py-6 px-6"
                             style="left:0;">
                            <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:0 2rem;">
                                @foreach(config('yamaha_nav.groups') as $groupSlug => $g)
                                <div class="mb-4">
                                    <a href="{{ route('yamaha.group', $groupSlug) }}"
                                       class="block text-xs font-black uppercase tracking-widest text-brand mb-2 hover:text-brand-dark pb-1 border-b border-gray-100">
                                        {{ $g['label'] }}
                                    </a>
                                    @foreach($g['categories'] as $catSlug => $catLabel)
                                    <a href="{{ route('yamaha.category', [$groupSlug, $catSlug]) }}"
                                       class="block py-1 text-xs font-semibold text-gray-600 hover:text-brand hover:translate-x-1 transition-all uppercase tracking-wide">
                                        {{ $catLabel }}
                                    </a>
                                    @endforeach
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Honda Products dropdown --}}
                    <div class="nav-item relative flex items-center">
                        <a href="{{ route('honda.index') }}"
                           class="flex items-center gap-1 px-4 h-full text-gray-700 hover:text-brand border-b-2 border-transparent hover:border-brand transition-colors whitespace-nowrap">
                            Honda Products
                            <svg class="w-3 h-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </a>
                        <div class="nav-dropdown absolute top-full left-0 bg-white shadow-2xl border-t-2 border-brand min-w-[180px] py-2 z-50">
                            @foreach(\Honda\Catalog\Models\HondaModel::select('category')->distinct()->pluck('category') as $hondaCategory)
                            <a href="{{ route('honda.category', $hondaCategory) }}"
                               class="block px-5 py-2.5 text-gray-700 hover:bg-brand-tint hover:text-brand transition-colors text-xs font-semibold uppercase tracking-wide">
                                {{ \App\Http\Controllers\HondaController::labelForCategory($hondaCategory) }}
                            </a>
                            @endforeach
                        </div>
                    </div>

                    {{-- Pre-Owned dropdown --}}
                    <div class="nav-item relative flex items-center">
                        <a href="{{ route('yamaha.preowned') }}"
                           class="flex items-center gap-1 px-4 h-full text-brand hover:text-brand-dark border-b-2 border-transparent hover:border-brand transition-colors whitespace-nowrap">
                            Pre-Owned
                            <svg class="w-3 h-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </a>
                        <div class="nav-dropdown absolute top-full left-0 bg-white shadow-2xl border-t-2 border-brand min-w-[180px] py-2 z-50">
                            <a href="{{ route('yamaha.preowned') }}"
                               class="block px-5 py-2.5 text-gray-700 hover:bg-brand-tint hover:text-brand transition-colors text-xs font-semibold uppercase tracking-wide">
                                Current Stock
                            </a>
                            <a href="{{ route('yamaha.sell') }}"
                               class="block px-5 py-2.5 text-gray-700 hover:bg-brand-tint hover:text-brand transition-colors text-xs font-semibold uppercase tracking-wide">
                                Sell My Bike
                            </a>
                        </div>
                    </div>

                    {{-- Divider --}}
                    <div class="w-px bg-gray-200 my-4 mx-1"></div>

                    <a href="{{ route('yamaha.specials') }}"
                       class="flex items-center px-3 h-full font-black text-brand hover:text-brand-dark border-b-2 border-transparent hover:border-brand transition-colors whitespace-nowrap">
                        Specials
                    </a>

                    {{-- Finance dropdown (lg to xl only) --}}
                    <div class="nav-item relative flex items-center xl:hidden">
                        <a href="{{ route('yamaha.finance') }}"
                           class="flex items-center gap-1 px-3 h-full text-gray-700 hover:text-brand border-b-2 border-transparent hover:border-brand transition-colors whitespace-nowrap">
                            Finance
                            <svg class="w-3 h-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </a>
                        <div class="nav-dropdown absolute top-full left-0 bg-white shadow-2xl border-t-2 border-brand min-w-[180px] py-2 z-50">
                            <a href="{{ route('yamaha.finance') }}"
                               class="block px-5 py-2.5 text-gray-700 hover:bg-brand-tint hover:text-brand transition-colors text-xs font-semibold uppercase tracking-wide">
                                Finance
                            </a>
                            <a href="{{ route('yamaha.insurance') }}"
                               class="block px-5 py-2.5 text-gray-700 hover:bg-brand-tint hover:text-brand transition-colors text-xs font-semibold uppercase tracking-wide">
                                Insurance
                            </a>
                        </div>
                    </div>

                    {{-- Finance + Insurance flat links (xl+ only) --}}
                    <a href="{{ route('yamaha.finance') }}"
                       class="hidden xl:flex items-center px-3 h-full text-gray-700 hover:text-brand border-b-2 border-transparent hover:border-brand transition-colors whitespace-nowrap">
                        Finance
                    </a>
                    <a href="{{ route('yamaha.insurance') }}"
                       class="hidden xl:flex items-center px-3 h-full text-gray-700 hover:text-brand border-b-2 border-transparent hover:border-brand transition-colors whitespace-nowrap">
                        Insurance
                    </a>

                    <a href="{{ route('yamaha.service') }}"
                       class="flex items-center px-3 h-full text-gray-700 hover:text-brand border-b-2 border-transparent hover:border-brand transition-colors text-center leading-tight">
                        Service &amp;<br>Tyres
                    </a>

                    <a href="{{ route('yamaha.about') }}"
                       class="flex items-center px-3 h-full text-gray-700 hover:text-brand border-b-2 border-transparent hover:border-brand transition-colors whitespace-nowrap">
                        About Us
                    </a>

                    <a href="{{ route('yamaha.news') }}"
                       class="flex items-center px-3 h-full text-gray-700 hover:text-brand border-b-2 border-transparent hover:border-brand transition-colors whitespace-nowrap">
                        News & Events
                    </a>

                    <a href="{{ route('yamaha.parts-finder') }}"
                       class="flex items-center px-3 h-full text-gray-700 hover:text-brand border-b-2 border-transparent hover:border-brand transition-colors whitespace-nowrap">
                        Parts Finder
                    </a>

                    <a href="{{ route('yamaha.shop-parts') }}"
                       class="flex items-center px-3 h-full text-white bg-brand hover:bg-brand-dark transition-colors font-black text-center leading-tight">
                        Shop<br>Accessories
                    </a>

                </div>

                {{-- Mobile menu button --}}
                <button id="mobile-menu-btn" class="lg:hidden p-2 text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div id="mobile-menu" class="hidden lg:hidden border-t border-gray-100 bg-white max-h-screen overflow-y-auto">

            {{-- Yamaha Products — level 1 --}}
            <div class="border-b border-gray-100" x-data="{ open: false }">
                <button @click="open = !open"
                        class="flex items-center justify-between w-full px-4 py-3 font-black text-sm uppercase tracking-wide text-gray-900 hover:text-brand">
                    Yamaha Products
                    <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                    {{-- Level 2 — each group as its own accordion --}}
                    @foreach(config('yamaha_nav.groups') as $groupSlug => $g)
                    <div x-data="{ openGroup: false }" class="border-t border-gray-100">
                        <button @click="openGroup = !openGroup"
                                class="flex items-center justify-between w-full px-6 py-2.5 text-sm font-black uppercase tracking-wide text-gray-700 hover:text-brand">
                            {{ $g['label'] }}
                            <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="openGroup ? 'rotate-180' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="openGroup" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                             class="bg-gray-50">
                            <a href="{{ route('yamaha.group', $groupSlug) }}"
                               class="block px-10 py-2 text-sm font-semibold text-brand border-l-2 border-brand ml-6">
                                View All {{ $g['label'] }}
                            </a>
                            @foreach($g['categories'] as $catSlug => $catLabel)
                            <a href="{{ route('yamaha.category', [$groupSlug, $catSlug]) }}"
                               class="block px-10 py-2 text-sm text-gray-600 hover:text-brand border-l-2 border-transparent hover:border-brand ml-6 transition-colors">
                                {{ $catLabel }}
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Pre-Owned --}}
            <div class="border-b border-gray-100" x-data="{ open: false }">
                <button @click="open = !open"
                        class="flex items-center justify-between w-full px-4 py-3 font-black text-sm uppercase tracking-wide text-brand hover:text-brand-dark">
                    Pre-Owned
                    <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                    <a href="{{ route('yamaha.preowned') }}"
                       class="block px-8 py-2.5 text-sm text-gray-600 hover:text-brand border-l-2 border-transparent hover:border-brand ml-4 transition-colors">
                        Current Stock
                    </a>
                    <a href="{{ route('yamaha.sell') }}"
                       class="block px-8 py-2.5 text-sm text-gray-600 hover:text-brand border-l-2 border-transparent hover:border-brand ml-4 transition-colors">
                        Sell My Bike
                    </a>
                </div>
            </div>

            {{-- Specials --}}
            <div class="border-b border-gray-100">
                <a href="{{ route('yamaha.specials') }}"
                   class="flex items-center justify-between px-4 py-3 font-black text-sm uppercase tracking-wide text-brand hover:text-brand-dark">
                    Specials
                </a>
                <a href="{{ route('yamaha.shop-parts') }}"
                   class="flex items-center justify-between px-4 py-3 font-black text-sm uppercase tracking-wide text-brand hover:text-brand-dark">
                    Shop Accessories
                </a>
            </div>

            {{-- External links --}}
            <div class="border-b border-gray-100">
                <div x-data="{ open: false }">
                    <button @click="open = !open"
                            class="flex items-center justify-between w-full px-4 py-3 font-black text-sm uppercase tracking-wide text-gray-900 hover:text-brand">
                        Finance
                        <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                        <a href="{{ route('yamaha.finance') }}"
                           class="block px-8 py-2.5 text-sm text-gray-600 hover:text-brand border-l-2 border-transparent hover:border-brand ml-4 transition-colors">
                            Finance
                        </a>
                        <a href="{{ route('yamaha.insurance') }}"
                           class="block px-8 py-2.5 text-sm text-gray-600 hover:text-brand border-l-2 border-transparent hover:border-brand ml-4 transition-colors">
                            Insurance
                        </a>
                    </div>
                </div>
                <a href="{{ route('yamaha.service') }}"
                   class="flex items-center justify-between px-4 py-3 font-black text-sm uppercase tracking-wide text-gray-900 hover:text-brand">
                    Tyres & Service
                </a>
                <a href="{{ route('yamaha.parts-finder') }}"
                   class="flex items-center justify-between px-4 py-3 font-black text-sm uppercase tracking-wide text-gray-900 hover:text-brand">
                    Parts Finder
                </a>
                <a href="{{ route('yamaha.about') }}"
                   class="flex items-center justify-between px-4 py-3 font-black text-sm uppercase tracking-wide text-gray-900 hover:text-brand">
                    About Us
                </a>
                <a href="{{ route('yamaha.news') }}"
                   class="flex items-center justify-between px-4 py-3 font-black text-sm uppercase tracking-wide text-gray-900 hover:text-brand">
                    News & Events
                </a>
            </div>

        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-ink text-gray-400 mt-20 border-t-4 border-brand">
        <div class="max-w-7xl mx-auto px-8 py-12">

            {{-- Brand row — full width --}}
            <div class="mb-10">
                <div class="inline-block bg-white rounded-md px-3 py-2 mb-4">
                    <img src="/images/star_yamaha_honda_logo.png" alt="NorthStar Yamaha" class="h-[46px] w-auto block">
                </div>
                <p class="text-sm mb-0.5">Authorised Yamaha Motor Dealer</p>
                <a href="https://northstarmotorcycles.com.au"
                   class="text-brand text-sm hover:text-brand-line transition-colors">
                    northstarmotorcycles.com.au →
                </a>
                <p class="text-xs text-gray-500 mt-3 leading-relaxed">
                    Pricing shown is the manufacturer's RRP including GST. Contact us for your ride-away price.
                </p>
            </div>

            {{-- 4-column grid: 2-col on mobile, 4-col on desktop --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-10">

                {{-- Product Range --}}
                <div>
                    <p class="text-white font-black text-[0.7rem] uppercase tracking-[0.15em] mb-4 pb-2 border-b border-gray-800">Product Range</p>
                    <div class="flex flex-col gap-2.5 text-sm">
                        @foreach(config('yamaha_nav.groups') as $slug => $g)
                        <a href="{{ route('yamaha.group', $slug) }}" class="text-gray-400 hover:text-white transition-colors">
                            {{ $g['label'] }}
                        </a>
                        @endforeach
                    </div>
                </div>

                {{-- Parts & Shop --}}
                <div>
                    <p class="text-white font-black text-[0.7rem] uppercase tracking-[0.15em] mb-4 pb-2 border-b border-gray-800">Parts & Shop</p>
                    <div class="flex flex-col gap-2.5 text-sm">
                        <a href="{{ route('yamaha.parts-finder') }}" class="text-gray-400 hover:text-white transition-colors">Parts Finder</a>
                        <a href="{{ route('yamaha.shop-parts') }}" class="text-gray-400 hover:text-white transition-colors">Shop Accessories</a>
                        <a href="https://shop.northstaryamaha.com.au/cms/page/road-gear#content" target="_blank" rel="noopener" class="text-gray-400 hover:text-white transition-colors">Road Gear</a>
                    </div>
                </div>

                {{-- Quick Links --}}
                <div>
                    <p class="text-white font-black text-[0.7rem] uppercase tracking-[0.15em] mb-4 pb-2 border-b border-gray-800">Quick Links</p>
                    <div class="flex flex-col gap-2.5 text-sm">
                        <a href="{{ route('yamaha.specials') }}" class="text-brand font-bold hover:text-brand-line transition-colors">Specials</a>
                        <a href="{{ route('yamaha.preowned') }}" class="text-gray-400 hover:text-white transition-colors">Pre-Owned</a>
                        <a href="{{ route('yamaha.sell') }}" class="text-gray-400 hover:text-white transition-colors">Sell My Bike</a>
                        <a href="{{ route('yamaha.finance') }}" class="text-gray-400 hover:text-white transition-colors">Finance</a>
                        <a href="{{ route('yamaha.insurance') }}" class="text-gray-400 hover:text-white transition-colors">Insurance</a>
                        <a href="{{ route('yamaha.service') }}" class="text-gray-400 hover:text-white transition-colors">Tyres & Service</a>
                        <a href="{{ route('yamaha.news') }}" class="text-gray-400 hover:text-white transition-colors">News & Events</a>
                        <a href="{{ route('yamaha.about') }}" class="text-gray-400 hover:text-white transition-colors">About Us / Contact</a>
                    </div>
                </div>

                {{-- Find Us --}}
                <div>
                    <p class="text-white font-black text-[0.7rem] uppercase tracking-[0.15em] mb-4 pb-2 border-b border-gray-800">Find Us</p>

                    <address class="not-italic text-sm leading-relaxed mb-4">
                        <span class="text-white font-semibold">{{ config('dealership.name') }}</span><br>
                        {{ config('dealership.address.street') }}<br>
                        {{ config('dealership.address.suburb') }} {{ config('dealership.address.state') }} {{ config('dealership.address.postcode') }}
                    </address>

                    <a href="{{ config('dealership.phone.href') }}"
                       class="block text-sm text-white hover:text-brand transition-colors font-semibold mb-1">
                        {{ config('dealership.phone.display') }}
                    </a>
                    <div class="flex flex-col gap-1 mb-5">
                        <a href="mailto:{{ config('dealership.email.sales') }}" class="block text-sm text-gray-400 hover:text-white transition-colors">{{ config('dealership.email.sales') }}</a>
                        <a href="mailto:{{ config('dealership.email.service') }}" class="block text-sm text-gray-400 hover:text-white transition-colors">{{ config('dealership.email.service') }}</a>
                        <a href="mailto:{{ config('dealership.email.spares') }}" class="block text-sm text-gray-400 hover:text-white transition-colors">{{ config('dealership.email.spares') }}</a>
                        <a href="mailto:{{ config('dealership.email.enquiries') }}" class="block text-sm text-gray-400 hover:text-white transition-colors">{{ config('dealership.email.enquiries') }}</a>
                    </div>

                    <p class="text-white font-black text-[0.65rem] uppercase tracking-[0.15em] mb-2">Trading Hours</p>
                    @foreach(config('dealership.hours') as $row)
                    <div class="flex justify-between text-xs py-0.5">
                        <span class="text-gray-400">{{ $row['days'] }}</span>
                        <span class="{{ $row['closed'] ? 'text-brand' : 'text-gray-300' }}">{{ $row['hours'] }}</span>
                    </div>
                    @endforeach
                    <p class="text-[0.65rem] text-gray-600 mt-2 leading-relaxed">Check Google for Public Holiday hours.</p>

                    <div class="flex gap-3 mt-5">
                        <a href="{{ config('dealership.social.facebook') }}" target="_blank" rel="noopener"
                           class="text-gray-500 hover:text-white transition-colors" aria-label="Facebook">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="{{ config('dealership.social.instagram') }}" target="_blank" rel="noopener"
                           class="text-gray-500 hover:text-white transition-colors" aria-label="Instagram">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </a>
                    </div>
                </div>

            </div>

            <div class="border-t border-gray-800 mt-10 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-500">
                <span>© {{ date('Y') }} NorthStar Yamaha. All rights reserved.</span>
                <div class="flex flex-wrap gap-4 justify-center">
                    <a href="{{ route('yamaha.privacy') }}" class="hover:text-gray-300 transition-colors">Privacy Policy</a>
                    <a href="{{ route('yamaha.returns') }}" class="hover:text-gray-300 transition-colors">Returns &amp; Exchanges</a>
                    <a href="{{ route('yamaha.delivery') }}" class="hover:text-gray-300 transition-colors">Delivery Information</a>
                </div>
            </div>
            <div class="mt-3 text-center text-[0.65rem] text-gray-700">
                Site by <a href="https://www.iwcdigital.com" target="_blank" rel="noopener" class="hover:text-gray-400 transition-colors">IWC Digital</a>
            </div>
        </div>
    </footer>

    <script>
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });
    </script>

</body>
</html>
