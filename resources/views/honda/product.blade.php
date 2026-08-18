@extends('yamaha.layout')

@section('title', $model->name . ' — ' . $subcategoryLabel)
@section('og_image')
{{ $model->ogImage?->url() ?? url('/images/star_yamaha_honda_logo.png') }}
@endsection
@section('og_type', 'product')
@section('meta_description', $model->name . ' — ' . $subcategoryLabel . ' available at Star Yamaha. View specs, colours and pricing from your authorised Honda dealer.')

@section('content')

@php
    $activeColourImage = $model->colours->first()?->image?->url();
    $heroImage = $activeColourImage ?? $model->ogImage?->url();
    $galleryAssets = $model->assets->where('pivot.role', 'gallery');
@endphp

    {{-- Full-bleed Hero --}}
    <div id="hero-section" class="relative text-white overflow-hidden bg-white h-[420px] sm:h-[460px] lg:h-[500px]">
        @if($heroImage)
        <img src="{{ $heroImage }}" alt="{{ $model->name }}" id="hero-image"
             loading="eager" fetchpriority="high"
             class="absolute inset-0 w-full h-full object-contain object-center lg:object-[1000px_center]">
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-ink/90 via-ink/80 to-ink/90 lg:bg-gradient-to-r lg:from-black/80 lg:via-black/40 lg:to-black/15"></div>

        <div class="relative h-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col justify-center">
            <nav class="text-gray-400 text-xs mb-4 sm:mb-6 uppercase tracking-widest font-semibold hidden sm:block">
                <a href="{{ route('yamaha.index') }}" class="hover:text-white">Home</a>
                <span class="mx-2">›</span>
                <a href="{{ route('honda.index') }}" class="hover:text-white">Honda</a>
                <span class="mx-2">›</span>
                <a href="{{ route('honda.category', $category) }}" class="hover:text-white">{{ $categoryLabel }}</a>
                <span class="mx-2">›</span>
                <a href="{{ route('honda.subcategory', [$category, $subcategory]) }}" class="hover:text-white">{{ $subcategoryLabel }}</a>
                <span class="mx-2">›</span>
                <span class="text-white">{{ $model->name }}</span>
            </nav>

            @if($model->tagline)
            <p class="text-brand-line text-xs font-black uppercase tracking-[0.3em] mb-2">{{ $model->tagline }}</p>
            @endif
            <h1 class="text-3xl sm:text-5xl md:text-8xl font-black uppercase leading-none tracking-tight mb-4 sm:mb-6">
                {{ $model->name }}
            </h1>

            @if($model->price_from)
            <div>
                <p class="text-gray-400 text-xs uppercase tracking-widest font-semibold">{{ $model->price_label ?? 'Ride Away From' }}</p>
                <p class="text-3xl sm:text-5xl font-black text-brand leading-none mt-1">
                    {{ $model->formatted_price }}
                </p>
                <p class="text-white/80 text-xs mt-1 drop-shadow">Contact us for your ride-away price</p>
            </div>
            @else
            <div>
                <p class="text-gray-300 text-sm font-semibold">Contact us for pricing</p>
            </div>
            @endif
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">

            {{-- Left: Main content --}}
            <div class="lg:col-span-2 space-y-14">

                {{-- Overview --}}
                @if($model->description)
                <section>
                    <h2 class="text-xs uppercase tracking-[0.3em] font-black text-brand mb-3">Overview</h2>
                    <div class="prose prose-lg prose-gray max-w-none text-gray-700 leading-relaxed">
                        {!! $model->description !!}
                    </div>
                </section>
                @endif

                {{-- Key Features --}}
                @if($model->features->isNotEmpty())
                <section>
                    <h2 class="text-xs uppercase tracking-[0.3em] font-black text-brand mb-6">Key Features</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        @foreach($model->features as $feature)
                        <div class="rounded-lg bg-white border border-gray-100 shadow-sm overflow-hidden">
                            @if($feature->image)
                            <div class="aspect-square w-full">
                                <img src="{{ $feature->image->url() }}" alt="{{ $feature->heading }}"
                                     class="w-full h-full object-cover">
                            </div>
                            @endif
                            <div class="p-5">
                                <h3 class="font-black text-gray-900 text-sm uppercase tracking-wide">{{ $feature->heading }}</h3>
                                @if($feature->body)
                                <p class="text-sm text-gray-600 mt-1 leading-snug">{{ $feature->body }}</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif

            </div>

            {{-- Right sidebar --}}
            <div class="space-y-6">

                {{-- Variant selector --}}
                @if($model->variants->count() > 1)
                <div class="bg-white rounded-xl p-6 border border-gray-200">
                    <h3 class="text-xs uppercase tracking-[0.3em] font-black text-brand mb-4">
                        Variants ({{ $model->variants->count() }})
                    </h3>
                    <div class="space-y-2">
                        @foreach($model->variants as $variant)
                        <div class="w-full flex items-center justify-between gap-3 p-2.5 rounded-lg border-[1.5px] border-transparent bg-gray-50">
                            <span class="text-sm font-semibold text-gray-700">{{ $variant->name }}</span>
                            @if($variant->price)
                            <span class="text-sm font-black text-brand">{{ $variant->formatted_price }}</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Colour picker --}}
                @if($model->colours->isNotEmpty())
                <div id="color-picker-card" class="bg-white rounded-xl p-6 border border-gray-200">
                    <h3 class="text-xs uppercase tracking-[0.3em] font-black text-brand mb-4">
                        Available Colours ({{ $model->colours->count() }})
                    </h3>
                    <div class="space-y-2">
                        @foreach($model->colours as $colour)
                        <button onclick="switchColor(this, '{{ $colour->image?->url() }}', '{{ $colour->name }}')"
                                style="min-height:44px;"
                                class="color-swatch-btn w-full flex items-center gap-3 p-2.5 rounded-lg border-[1.5px] transition text-left
                                       {{ $loop->first ? 'bg-brand-tint border-brand' : 'bg-white border-transparent hover:border-gray-200' }}">
                            <span class="color-swatch-dot w-8 h-8 rounded-full border-2 border-white flex-shrink-0 {{ $loop->first ? 'ring-2 ring-brand' : 'ring-1 ring-gray-300' }}"
                                  style="background-color: {{ $colour->hex ?: '#ccc' }}"></span>
                            <span class="color-swatch-name text-sm {{ $loop->first ? 'font-extrabold text-brand-dark' : 'font-semibold text-gray-700' }}">
                                {{ $colour->name }}
                            </span>
                        </button>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Current Offers --}}
                <x-honda-offer-panel :offers="$offers" />

                {{-- Enquiry CTA --}}
                <div class="rounded-xl p-6 text-white bg-accent">
                    <h3 class="font-black text-lg uppercase mb-1">Interested in the {{ $model->name }}?</h3>
                    <p class="text-blue-100/80 text-sm mb-4">Talk to our team for your best ride-away price.</p>
                    <a href="mailto:sales@staryamaha.com.au?subject=Enquiry: {{ $model->name }}"
                       class="block text-center font-black py-3 px-6 rounded-lg transition text-white bg-brand hover:bg-brand-dark">
                        Enquire Now
                    </a>
                    <a href="tel:+61" class="block text-center text-blue-100/80 text-sm mt-3 hover:text-white transition">
                        Or call us →
                    </a>
                </div>

                {{-- Pricing disclaimer --}}
                <p class="text-xs text-gray-400 leading-relaxed">
                    * Pricing shown is sourced from Honda's ride-away price calculator and may not reflect
                    dealer delivery or on-road costs. Contact us for your personalised quote.
                </p>
            </div>

        </div>

        {{-- Specifications — tabbed by section --}}
        @if(!empty($specGroups))
        <div class="mt-14">
            <section class="mt-2">
                <p class="text-xs uppercase tracking-[0.3em] font-black text-brand mb-2">Technical Data</p>
                <h2 class="text-2xl font-black uppercase text-gray-900 mb-6 tracking-tight">Specifications</h2>

                <div class="rounded-2xl overflow-hidden border border-gray-200 shadow-sm">
                    {{-- Tab nav --}}
                    <div class="flex overflow-x-auto bg-ink border-b border-gray-800">
                        @foreach(array_keys($specGroups) as $i => $section)
                        <button
                            onclick="switchSpecTab('{{ Str::slug($section) }}')"
                            id="spec-tab-{{ Str::slug($section) }}"
                            class="spec-tab flex-shrink-0 px-5 py-3.5 text-xs font-black uppercase tracking-widest transition-colors whitespace-nowrap
                                   {{ $i === 0 ? 'text-white border-b-2 border-brand' : 'text-gray-500 border-b-2 border-transparent hover:text-gray-300' }}">
                            {{ $section }}
                        </button>
                        @endforeach
                    </div>

                    {{-- Tab panels --}}
                    @foreach($specGroups as $section => $categories)
                    @php $panelId = Str::slug($section); @endphp
                    <div id="spec-panel-{{ $panelId }}" class="spec-panel bg-white {{ !$loop->first ? 'hidden' : '' }}">
                        @foreach($categories as $categoryName => $rows)
                        <div class="px-6 pt-4">
                            <p class="text-[11px] font-black uppercase tracking-widest text-gray-400 mb-1">{{ $categoryName }}</p>
                        </div>
                        <div class="divide-y divide-gray-100 pb-2">
                            @foreach($rows as $label => $value)
                            <div class="flex items-start gap-4 px-6 py-3 {{ $loop->odd ? 'bg-white' : 'bg-gray-50/60' }}">
                                <span class="text-xs font-black uppercase tracking-wide text-gray-400 w-40 flex-shrink-0 pt-0.5">{{ $label }}</span>
                                @if(is_array($value))
                                <span class="text-sm font-semibold text-gray-900 leading-snug space-y-0.5">
                                    @foreach($value as $variantName => $variantValue)
                                    <span class="block">{{ $variantValue }} <span class="text-gray-400 font-normal">({{ $variantName === '_' ? 'default' : $variantName }})</span></span>
                                    @endforeach
                                </span>
                                @else
                                <span class="text-sm font-semibold text-gray-900 leading-snug">{{ $value }}</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @endforeach
                    </div>
                    @endforeach
                </div>
            </section>
        </div>

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

        {{-- Gallery --}}
        @if($galleryAssets->isNotEmpty())
        <div class="mt-14">
            <h2 class="text-xs uppercase tracking-[0.3em] font-black text-brand mb-6">Gallery</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                @foreach($galleryAssets as $asset)
                <button type="button" onclick="openLightbox('{{ $asset->url() }}', '{{ $model->name }}')"
                        class="aspect-[4/3] overflow-hidden rounded bg-white flex items-center justify-center p-3 cursor-zoom-in">
                    <img src="{{ $asset->url() }}" alt="{{ $model->name }}"
                         class="max-w-full max-h-full object-contain hover:scale-105 transition-transform duration-500">
                </button>
                @endforeach
            </div>
        </div>

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
            void el.offsetWidth;
            el.classList.add('ns-fade');
        }

        function switchColor(btn, imageUrl, colorName) {
            if (imageUrl) {
                const heroImg = document.getElementById('hero-image');
                if (heroImg) {
                    heroImg.src = imageUrl;
                    replayFade(heroImg);
                }
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
    </script>

@endsection
