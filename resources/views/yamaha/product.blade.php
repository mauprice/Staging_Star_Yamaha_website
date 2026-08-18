@extends('yamaha.layout')

@section('title', $product->model_name . ' — ' . $subCategoryName)
@section('og_image')
{{ $product->summary_image ?? url('/images/star_yamaha_honda_logo.png') }}
@endsection
@section('og_type', 'product')
@section('meta_description', $product->model_name . ' — ' . $subCategoryName . ' available at Star Yamaha. View specs, colours and pricing from your authorised Yamaha dealer.')

@section('content')

@php
    $activeColorImage = $product->colors->first()?->color_image;
    $heroBanner = $product->banners->where('image_type', 1)->first();
    $heroImage = $activeColorImage ?? $heroBanner?->image ?? $product->summary_image;
    // Colour studio shots have no per-breakpoint crops, so the responsive
    // <source> variants only make sense when we're showing the banner image.
    $usingBannerHero = ! $activeColorImage && $heroBanner;
@endphp

    {{-- Full-bleed Hero --}}
    <div id="hero-section" class="relative text-white overflow-hidden bg-white h-[420px] sm:h-[460px] lg:h-[500px]">
        @if($heroImage)
        <picture id="hero-picture">
            @if($usingBannerHero && $heroBanner->image_mobile)<source media="(max-width: 767px)" srcset="{{ $heroBanner->image_mobile }}">@endif
            @if($usingBannerHero && $heroBanner->image_tablet)<source media="(max-width: 1023px)" srcset="{{ $heroBanner->image_tablet }}">@endif
            <img src="{{ $heroImage }}" alt="{{ $product->model_name }}" id="hero-image"
                 loading="eager" fetchpriority="high"
                 class="absolute inset-0 w-full h-full object-contain object-center lg:object-[1000px_center]">
        </picture>
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-ink/90 via-ink/80 to-ink/90 lg:bg-gradient-to-r lg:from-black/80 lg:via-black/40 lg:to-black/15"></div>

        <div class="relative h-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col justify-center">
            {{-- Breadcrumb --}}
            <nav class="text-gray-400 text-xs mb-4 sm:mb-6 uppercase tracking-widest font-semibold hidden sm:block">
                <a href="{{ route('yamaha.index') }}" class="hover:text-white">Home</a>
                <span class="mx-2">›</span>
                <a href="{{ route('yamaha.group', $group) }}" class="hover:text-white">{{ $groupName }}</a>
                <span class="mx-2">›</span>
                <a href="{{ route('yamaha.category', [$group, $category]) }}" class="hover:text-white">{{ $subCategoryName }}</a>
                <span class="mx-2">›</span>
                <span class="text-white">{{ $product->model_name }}</span>
            </nav>

            <p class="text-brand-line text-xs font-black uppercase tracking-[0.3em] mb-2">{{ $product->year_model }} Model</p>
            <h1 class="text-3xl sm:text-5xl md:text-8xl font-black uppercase leading-none tracking-tight mb-4 sm:mb-6">
                {{ $product->model_name }}
            </h1>

            @if($product->recommended_retail > 0)
            <div>
                <p class="text-gray-400 text-xs uppercase tracking-widest font-semibold">Ride Away From</p>
                <p class="text-3xl sm:text-5xl font-black text-brand leading-none mt-1">
                    ${{ number_format($product->recommended_retail, 0) }}
                </p>
                <p class="text-white/80 text-xs mt-1 drop-shadow">RRP inc. GST — contact us for your ride-away price</p>
            </div>
            @else
            <div>
                <p class="text-gray-300 text-sm font-semibold">Contact us for pricing</p>
            </div>
            @endif
            <a href="{{ route('yamaha.finance') }}" target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 mt-4 text-xs font-black uppercase tracking-widest text-white/70 hover:text-white transition">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Finance Available — Apply Now
            </a>
        </div>

        {{-- Colour swipe dots — mobile only --}}
        @if($product->colors->count() > 1)
        <div id="swipe-dots" class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex gap-2 sm:hidden">
            @foreach($product->colors as $color)
            <div class="swipe-dot w-2 h-2 rounded-full border border-white transition-all duration-200"
                 style="background:white; opacity:{{ $loop->first ? '1' : '0.35' }}; transform:{{ $loop->first ? 'scale(1.2)' : 'scale(1)' }};"></div>
            @endforeach
        </div>
        @endif

    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">

            {{-- Left: Main content --}}
            <div class="lg:col-span-2 space-y-14">

                {{-- Overview --}}
                @if($product->long_description || $product->description)
                <section>
                    <h2 class="text-xs uppercase tracking-[0.3em] font-black text-brand mb-3">Overview</h2>
                    <div class="prose prose-lg prose-gray max-w-none text-gray-700 leading-relaxed">
                        {!! $product->long_description ?? $product->description !!}
                    </div>
                </section>
                @endif

                {{-- Key Features --}}
                @if($product->features->isNotEmpty())
                <section>
                    <h2 class="text-xs uppercase tracking-[0.3em] font-black text-brand mb-6">Key Features</h2>

                    @php $firstFeatures = $product->features->take(2); $restFeatures = $product->features->skip(2); @endphp

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        @foreach($firstFeatures as $feature)
                        <div class="rounded-lg bg-white border border-gray-100 shadow-sm overflow-hidden">
                            @if($feature->image)
                            <div class="aspect-square w-full">
                                <img src="{{ $feature->image }}" alt="{{ $feature->title }}"
                                     class="w-full h-full object-cover">
                            </div>
                            @endif
                            <div class="p-5">
                                <h3 class="font-black text-gray-900 text-sm uppercase tracking-wide">{{ $feature->title }}</h3>
                                <div class="text-sm text-gray-600 mt-1 leading-snug prose prose-sm max-w-none">{!! $feature->description !!}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    @if($restFeatures->isNotEmpty())
                    <div id="extra-features" class="expand-collapse">
                        <div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-6">
                                @foreach($restFeatures as $feature)
                                <div class="rounded-lg bg-white border border-gray-100 shadow-sm overflow-hidden">
                                    @if($feature->image)
                                    <div class="aspect-square w-full">
                                        <img src="{{ $feature->image }}" alt="{{ $feature->title }}"
                                             class="w-full h-full object-cover">
                                    </div>
                                    @endif
                                    <div class="p-5">
                                        <h3 class="font-black text-gray-900 text-sm uppercase tracking-wide">{{ $feature->title }}</h3>
                                        <div class="text-sm text-gray-600 mt-1 leading-snug prose prose-sm max-w-none">{!! $feature->description !!}</div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <button type="button" onclick="toggleFeatures(this)"
                            class="mt-6 inline-flex items-center gap-2 text-xs font-black uppercase tracking-widest text-brand hover:text-brand-dark transition">
                        <span>Show More</span>
                        <svg class="w-4 h-4 transition-transform duration-300 ease-in-out" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <script>
                        function toggleFeatures(btn) {
                            const wrap = document.getElementById('extra-features');
                            const expanding = !wrap.classList.contains('is-open');

                            wrap.classList.toggle('is-open', expanding);
                            btn.querySelector('span').textContent = expanding ? 'Show Less' : 'Show More';
                            btn.querySelector('svg').classList.toggle('rotate-180', expanding);
                        }
                    </script>
                    @endif
                </section>
                @endif

            </div>

            {{-- Right sidebar --}}
            <div class="space-y-6">

                {{-- Colour picker --}}
                @if($product->colors->isNotEmpty())
                <div id="color-picker-card" class="bg-white rounded-xl p-6 border border-gray-200">
                    <h3 class="text-xs uppercase tracking-[0.3em] font-black text-brand mb-4">
                        Available Colours ({{ $product->colors->count() }})
                    </h3>
                    <div class="space-y-2">
                        @foreach($product->colors as $color)
                        <button onclick="switchColor(this, '{{ $color->color_image }}', '{{ $color->color_name }}')"
                                style="min-height:44px;"
                                class="color-swatch-btn w-full flex items-center gap-3 p-2.5 rounded-lg border-[1.5px] transition text-left
                                       {{ $loop->first ? 'bg-brand-tint border-brand' : 'bg-white border-transparent hover:border-gray-200' }}">
                            <span class="color-swatch-dot w-8 h-8 rounded-full border-2 border-white flex-shrink-0 {{ $loop->first ? 'ring-2 ring-brand' : 'ring-1 ring-gray-300' }}"
                                  style="background-color: {{ $color->color_code ?: '#ccc' }}"></span>
                            <span class="color-swatch-name text-sm {{ $loop->first ? 'font-extrabold text-brand-dark' : 'font-semibold text-gray-700' }}">
                                {{ $color->color_name }}
                            </span>
                        </button>
                        @endforeach
                    </div>
                    @if($product->colors->first()?->color_image)
                    <div class="mt-4 bg-white rounded-lg border border-gray-200 p-3">
                        <img id="color-preview" src="{{ $product->colors->first()->color_image }}"
                             alt="Colour preview"
                             class="w-full object-contain aspect-[4/3]">
                        <p id="color-name" class="text-xs text-center text-gray-500 mt-2 font-semibold">
                            {{ $product->colors->first()->color_name }}
                        </p>
                    </div>
                    @endif
                </div>
                @endif

                {{-- Enquiry CTA --}}
                <div class="rounded-xl p-6 text-white bg-accent">
                    <h3 class="font-black text-lg uppercase mb-1">Interested in the {{ $product->model_name }}?</h3>
                    <p class="text-blue-100/80 text-sm mb-4">Talk to our team for your best ride-away price.</p>
                    <a href="mailto:sales@staryamaha.com.au?subject=Enquiry: {{ $product->model_name }}"
                       class="block text-center font-black py-3 px-6 rounded-lg transition text-white bg-brand hover:bg-brand-dark">
                        Enquire Now
                    </a>
                    <a href="tel:+61" class="block text-center text-blue-100/80 text-sm mt-3 hover:text-white transition">
                        Or call us →
                    </a>
                </div>

                {{-- Finance CTA --}}
                <a href="{{ route('yamaha.finance') }}" target="_blank" rel="noopener"
                   class="group flex items-center gap-4 rounded-xl p-5 transition-colors"
                   style="background:#FFD600; border:2px solid #FFD600;">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 transition-colors"
                         style="background:rgba(0,0,0,0.12);">
                        <svg class="w-5 h-5" style="color:#1a1a1a;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-black text-sm uppercase tracking-wide" style="color:#1a1a1a;">Apply for Finance</p>
                        <p class="text-xs" style="color:#333;">Easy online application — opens in new tab</p>
                    </div>
                </a>

                {{-- Brochure --}}
                @if($product->brochure_url)
                <a href="{{ $product->brochure_url }}" target="_blank" rel="noopener"
                   class="flex items-center gap-3 p-4 rounded-xl border-2 border-gray-200 hover:border-gray-400 transition group">
                    <svg class="w-8 h-8 text-brand flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <div>
                        <p class="font-black text-gray-900 text-sm group-hover:text-brand transition">Download Brochure</p>
                        <p class="text-xs text-gray-500">PDF specification sheet</p>
                    </div>
                </a>
                @endif

                {{-- Pricing disclaimer --}}
                <p class="text-xs text-gray-400 leading-relaxed">
                    * Pricing shown is the manufacturer's Recommended Retail Price (RRP) including GST.
                    Ride-away pricing varies. Contact us for your personalised quote.
                </p>
            </div>

        </div>

        {{-- Specifications — full width, sits below the two-column grid entirely --}}
        @if(!empty($product->product_spec))
        <div class="mt-14">
            @php
                    $s = $product->product_spec;
                    $specGroups = [
                        'Engine' => [
                            'Engine Type'       => $s['Engine']       ?? null,
                            'Displacement (cc)' => $s['Displacement'] ?? null,
                            'Bore × Stroke'     => $s['Bore']         ?? null,
                            'Compression'       => $s['Compression']  ?? null,
                            'Cooling'           => $s['Cooling']      ?? null,
                            'Lubrication'       => $s['Lubrication']  ?? null,
                            'Fuel System'       => $s['Fuel']         ?? null,
                            'Ignition'          => $s['Ignition']     ?? null,
                            'Starter'           => $s['Starter']      ?? null,
                            'Max Output'        => $s['Max_Output']   ?? null,
                            'Max RPM'           => $s['Max_RPM']      ?? null,
                        ],
                        'Drivetrain' => [
                            'Transmission'      => $s['Transmission'] ?? null,
                            'Final Drive'       => $s['Final_Trans']  ?? null,
                            'Gear Ratios'       => $s['Gear_Ratio']   ?? null,
                            'Fuel Tank (L)'     => $s['Fuel_Tank']    ?? null,
                            'Oil Capacity (L)'  => $s['Oil']          ?? null,
                        ],
                        'Chassis' => [
                            'Frame'             => $s['Frame']             ?? null,
                            'Front Suspension'  => $s['Suspension_Front']  ?? null,
                            'Rear Suspension'   => $s['Suspension_Rear']   ?? null,
                            'Front Brakes'      => $s['Brakes_Front']      ?? null,
                            'Rear Brakes'       => $s['Brakes_Rear']       ?? null,
                            'Brakes'            => $s['Brakes']            ?? null,
                            'Steering'          => $s['Steering']          ?? null,
                            'Front Tyre'        => $s['Tyres_Front']       ?? null,
                            'Rear Tyre'         => $s['Tyres_Rear']        ?? null,
                        ],
                        'Dimensions' => [
                            'Length (mm)'            => $s['Length']      ?? null,
                            'Width (mm)'             => $s['Width']       ?? null,
                            'Height (mm)'            => $s['Height']      ?? null,
                            'Seat Height (mm)'       => $s['Seat_Height'] ?? null,
                            'Wheelbase (mm)'         => $s['Wheelbase']   ?? null,
                            'Ground Clearance (mm)'  => $s['Clearance']   ?? null,
                            'Wet Weight (kg)'        => $s['Weight']      ?? null,
                            'Dry Weight (kg)'        => $s['Dry_Weight']  ?? null,
                            'Storage (L)'            => $s['Storage']     ?? null,
                        ],
                    ];

                    // Remove empty rows, then empty groups
                    foreach ($specGroups as $g => $rows) {
                        $specGroups[$g] = array_filter($rows, fn($v) => $v !== null && $v !== '' && $v !== '0' && (string)$v !== '0');
                        if (empty($specGroups[$g])) unset($specGroups[$g]);
                    }

                    // Key highlight stats (shown as large callouts)
                    $highlights = array_filter([
                        ['label' => 'Displacement',  'value' => isset($s['Displacement']) ? number_format((float)$s['Displacement'], 0) . 'cc' : null],
                        ['label' => 'Seat Height',   'value' => isset($s['Seat_Height'])  ? $s['Seat_Height'] . ' mm' : null],
                        ['label' => 'Wet Weight',    'value' => isset($s['Weight'])        ? $s['Weight'] . ' kg' : null],
                        ['label' => 'Fuel Tank',     'value' => isset($s['Fuel_Tank'])     ? $s['Fuel_Tank'] . ' L' : null],
                    ], fn($h) => $h['value'] !== null);
                @endphp

                @if(!empty($specGroups))
                <section class="mt-2">
                    <p class="text-xs uppercase tracking-[0.3em] font-black text-brand mb-2">Technical Data</p>
                    <h2 class="text-2xl font-black uppercase text-gray-900 mb-6 tracking-tight">Specifications</h2>

                    {{-- Key stat highlights --}}
                    @if(!empty($highlights))
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-8">
                        @foreach($highlights as $h)
                        <div class="bg-ink rounded-xl p-4 text-center">
                            <p class="text-2xl font-black text-white leading-none tracking-tight">{{ $h['value'] }}</p>
                            <p class="text-[10px] uppercase tracking-widest text-gray-500 mt-1.5 font-semibold">{{ $h['label'] }}</p>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    {{-- Tabbed spec sections --}}
                    <div class="rounded-2xl overflow-hidden border border-gray-200 shadow-sm">

                        {{-- Tab nav --}}
                        <div class="flex overflow-x-auto bg-ink border-b border-gray-800">
                            @foreach(array_keys($specGroups) as $i => $groupName)
                            <button
                                onclick="switchSpecTab('{{ Str::slug($groupName) }}')"
                                id="spec-tab-{{ Str::slug($groupName) }}"
                                class="spec-tab flex-shrink-0 px-5 py-3.5 text-xs font-black uppercase tracking-widest transition-colors whitespace-nowrap
                                       {{ $i === 0 ? 'text-white border-b-2 border-brand' : 'text-gray-500 border-b-2 border-transparent hover:text-gray-300' }}">
                                {{ $groupName }}
                            </button>
                            @endforeach
                        </div>

                        {{-- Tab panels --}}
                        @foreach($specGroups as $groupName => $rows)
                        @php $panelId = Str::slug($groupName); @endphp
                        <div id="spec-panel-{{ $panelId }}"
                             class="spec-panel bg-white {{ !$loop->first ? 'hidden' : '' }}">
                            <div class="divide-y divide-gray-100">
                                @foreach($rows as $label => $value)
                                <div class="flex items-start gap-4 px-6 py-3.5 {{ $loop->odd ? 'bg-white' : 'bg-gray-50/60' }}">
                                    <span class="text-xs font-black uppercase tracking-wide text-gray-400 w-40 flex-shrink-0 pt-0.5">{{ $label }}</span>
                                    <span class="text-sm font-semibold text-gray-900 leading-snug">{{ $value }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach

                    </div>
                </section>

                <script>
                    function switchSpecTab(id) {
                        document.querySelectorAll('.spec-tab').forEach(t => {
                            t.classList.remove('text-white', 'border-brand');
                            t.classList.add('text-gray-500', 'border-transparent');
                        });
                        document.querySelectorAll('.spec-panel').forEach(p => p.classList.add('hidden'));
                        const tab = document.getElementById('spec-tab-' + id);
                        tab.classList.remove('text-gray-500', 'border-transparent');
                        tab.classList.add('text-white', 'border-brand');
                        document.getElementById('spec-panel-' + id).classList.remove('hidden');
                    }
                </script>
                @endif
            </div>
            @endif

        {{-- Gallery — full width, below the specifications --}}
        @if($product->images->isNotEmpty())
        <div class="mt-14">
            <h2 class="text-xs uppercase tracking-[0.3em] font-black text-brand mb-6">Gallery</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                @foreach($product->images as $img)
                <button type="button" onclick="openLightbox('{{ $img->image_url }}', '{{ $product->model_name }}')"
                        class="aspect-[4/3] overflow-hidden rounded bg-white flex items-center justify-center p-3 cursor-zoom-in">
                    <img src="{{ $img->image_url }}" alt="{{ $product->model_name }}"
                         class="max-w-full max-h-full object-contain hover:scale-105 transition-transform duration-500">
                </button>
                @endforeach
            </div>
        </div>

        {{-- Lightbox --}}
        <div id="lightbox" class="hidden fixed inset-0 z-[100] bg-black/90 items-center justify-center p-4 sm:p-8" onclick="closeLightbox()">
            <button type="button" onclick="closeLightbox()" aria-label="Close"
                    class="absolute top-4 right-4 sm:top-6 sm:right-6 text-white/80 hover:text-white transition">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <img id="lightbox-image" src="" alt="" class="max-w-full max-h-full object-contain">
        </div>

        <script>
            function openLightbox(url, alt) {
                const lb = document.getElementById('lightbox');
                document.getElementById('lightbox-image').src = url;
                document.getElementById('lightbox-image').alt = alt;
                lb.classList.remove('hidden');
                lb.classList.add('flex');
                document.body.style.overflow = 'hidden';
            }
            function closeLightbox() {
                const lb = document.getElementById('lightbox');
                lb.classList.add('hidden');
                lb.classList.remove('flex');
                document.getElementById('lightbox-image').src = '';
                document.body.style.overflow = '';
            }
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeLightbox();
            });
        </script>
        @endif

    </div>

    <script>
        function replayFade(el) {
            if (!el) return;
            el.classList.remove('ns-fade');
            void el.offsetWidth; // force reflow so the animation restarts
            el.classList.add('ns-fade');
        }

        function switchColor(btn, imageUrl, colorName) {
            if (imageUrl) {
                const heroImg = document.getElementById('hero-image');
                const previewImg = document.getElementById('color-preview');

                if (previewImg) { previewImg.src = imageUrl; replayFade(previewImg); }
                if (heroImg) {
                    heroImg.src = imageUrl;
                    replayFade(heroImg);
                    // Once a colour is selected, drop the banner's responsive
                    // <source> crops so a later resize can't revert the hero
                    // back to the default banner image.
                    document.querySelectorAll('#hero-picture source').forEach(s => s.remove());
                }
            }
            if (colorName) {
                document.getElementById('color-name').textContent = colorName;
            }

            document.querySelectorAll('.color-swatch-btn').forEach(b => {
                b.classList.remove('bg-brand-tint', 'border-brand');
                b.classList.add('bg-white', 'border-transparent');
                const name = b.querySelector('.color-swatch-name');
                const dot = b.querySelector('.color-swatch-dot');
                if (name) { name.classList.remove('text-brand-dark', 'font-extrabold'); name.classList.add('text-gray-700', 'font-semibold'); }
                if (dot) { dot.classList.remove('ring-2', 'ring-brand'); dot.classList.add('ring-1', 'ring-gray-300'); }
            });
            if (btn) {
                btn.classList.remove('bg-white', 'border-transparent');
                btn.classList.add('bg-brand-tint', 'border-brand');
                const name = btn.querySelector('.color-swatch-name');
                const dot = btn.querySelector('.color-swatch-dot');
                if (name) { name.classList.remove('text-gray-700', 'font-semibold'); name.classList.add('text-brand-dark', 'font-extrabold'); }
                if (dot) { dot.classList.remove('ring-1', 'ring-gray-300'); dot.classList.add('ring-2', 'ring-brand'); }
            }
        }

        // Mobile swipe to change colour — works on hero and the colour picker card
        (function () {
            const swatches = Array.from(document.querySelectorAll('.color-swatch-btn'));
            const dots = Array.from(document.querySelectorAll('.swipe-dot'));
            if (swatches.length < 2) return;

            let current = 0;

            function goToColor(idx) {
                current = (idx + swatches.length) % swatches.length;
                swatches[current].click();
                dots.forEach(function (d, i) {
                    d.style.opacity = i === current ? '1' : '0.35';
                    d.style.transform = i === current ? 'scale(1.2)' : 'scale(1)';
                });
            }

            function addSwipe(el) {
                if (!el) return;
                var startX = 0, startY = 0;
                el.addEventListener('touchstart', function (e) {
                    startX = e.touches[0].clientX;
                    startY = e.touches[0].clientY;
                }, { passive: true });
                el.addEventListener('touchend', function (e) {
                    var dx = e.changedTouches[0].clientX - startX;
                    var dy = e.changedTouches[0].clientY - startY;
                    if (Math.abs(dx) < 40 || Math.abs(dx) < Math.abs(dy)) return;
                    goToColor(dx < 0 ? current + 1 : current - 1);
                }, { passive: true });
            }

            addSwipe(document.getElementById('hero-section'));
            addSwipe(document.getElementById('color-picker-card'));
        })();
    </script>

@endsection
