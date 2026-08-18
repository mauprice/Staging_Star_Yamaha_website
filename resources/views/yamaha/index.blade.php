@extends('yamaha.layout')

@section('title', 'Yamaha Product Range')
@section('meta_description', 'Star Yamaha is an authorised Yamaha Motor dealer offering the full range of Yamaha motorcycles, scooters, watercraft, ATVs, side-by-sides and more.')

@section('content')

<style>
    .slider-hero { height: 65vh; min-height: 420px; }
    @media (max-width: 639px) {
        .slider-hero { height: 42vw; min-height: 200px; }
    }
</style>

    {{-- Hero Slider --}}
    <div class="relative overflow-hidden bg-ink slider-hero" style="max-height: 700px;">

        @if($slides->isNotEmpty())
            @foreach($slides as $i => $promo)
            <div class="slider-slide absolute inset-0 transition-opacity duration-700"
                 style="opacity: {{ $i === 0 ? '1' : '0' }}; z-index: {{ $i === 0 ? '1' : '0' }};">
                @if(($promo->fit ?? 'cover') === 'contain')
                <div class="absolute inset-0 bg-center bg-cover scale-110 blur-2xl opacity-50" style="background-image: url('{{ $promo->image }}');"></div>
                <img src="{{ $promo->image }}" alt="{{ $promo->head }}"
                     @if($i === 0) loading="eager" fetchpriority="high" @else loading="lazy" @endif
                     class="relative w-full h-full object-contain object-center">
                @else
                <img src="{{ $promo->image }}" alt="{{ $promo->head }}"
                     @if($i === 0) loading="eager" fetchpriority="high" @else loading="lazy" @endif
                     class="w-full h-full object-cover object-center">
                @endif
            </div>
            @endforeach
        @else
            {{-- Fallback if no promotions --}}
            <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-ink via-ink-2 to-brand-dark"></div>
        @endif

        {{-- Gradient overlay --}}
        <div class="absolute inset-0 z-10 bg-gradient-to-r from-black/70 via-black/30 to-black/5"></div>

        {{-- Slide text --}}
        @if($slides->isNotEmpty())
        <div class="absolute inset-0 z-20">
            <div class="relative h-full max-w-7xl mx-auto px-4 sm:px-8">
                @foreach($slides as $i => $promo)
                <div class="slider-text absolute bottom-14 left-4 sm:left-8 right-4 sm:right-auto transition-opacity duration-700 max-w-sm sm:max-w-lg"
                     style="opacity: {{ $i === 0 ? '1' : '0' }};">
                    @if($promo->type)
                    <p class="text-brand-line text-xs font-black uppercase tracking-[0.3em] mb-2">{{ $promo->type }}</p>
                    @endif
                    <h2 class="text-2xl sm:text-4xl md:text-6xl font-black uppercase text-white leading-none tracking-tight">
                        {{ $promo->head }}
                    </h2>
                    @if($promo->brief)
                    <p class="text-gray-300 mt-2 text-xs sm:text-sm leading-relaxed line-clamp-2 hidden sm:block">{{ $promo->brief }}</p>
                    @endif
                    <x-btn href="{{ $promo->link }}" variant="dark" class="mt-4">
                        View Offer →
                    </x-btn>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Dot navigation --}}
        @if($slides->count() > 1)
        <div class="absolute bottom-5 left-1/2 -translate-x-1/2 z-20 flex items-center gap-2">
            @foreach($slides as $i => $promo)
            <button class="slider-dot w-2.5 h-2.5 rounded-full border-2 border-white transition-all duration-300 {{ $i === 0 ? 'bg-white scale-110' : 'bg-transparent' }}"
                    data-index="{{ $i }}"></button>
            @endforeach
        </div>

        <button id="slider-prev" class="absolute left-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-black/40 hover:bg-black/70 text-white flex items-center justify-center transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
        <button id="slider-next" class="absolute right-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-black/40 hover:bg-black/70 text-white flex items-center justify-center transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
        @endif
    </div>

    {{-- Category strip — promoted to prominent media tiles per audit (was a thin row of small icons) --}}
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
            <p class="text-[0.65rem] font-black uppercase tracking-[0.2em] text-gray-400 mb-2">Yamaha Range</p>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 md:gap-4">
                @foreach(\App\Support\YamahaDivisions::filterVisible([
                    'road'       => 'Road',
                    'off-road'   => 'Off Road',
                    'atv-rov'    => 'ATV / ROV',
                    'watercraft' => 'Watercraft',
                    'golf-car'   => 'Golf',
                ]) as $slug => $label)
                <x-media-tile
                    href="{{ route('yamaha.group', $slug) }}"
                    image="{{ $groupPreviews[$slug]['image'] ?? null }}"
                    :imageAlt="$label"
                    label="{{ $label }}"
                    sublabel="View Range →"
                    ratio="16/10" />
                @endforeach
            </div>

            @if(!empty($hondaCategoryPreviews))
            <p class="text-[0.65rem] font-black uppercase tracking-[0.2em] text-gray-400 mt-5 mb-2">Honda Range</p>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 md:gap-4">
                @foreach($hondaCategoryPreviews as $slug => $preview)
                <x-media-tile
                    href="{{ route('honda.category', $slug) }}"
                    image="{{ $preview['image'] }}"
                    :imageAlt="$preview['label']"
                    label="{{ $preview['label'] }}"
                    sublabel="View Range →"
                    ratio="16/10" />
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Current Promotions --}}
    @if($promotions->where('active', true)->isNotEmpty())
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <p class="text-xs uppercase tracking-[0.3em] font-black text-brand mb-1">Current Deals</p>
        <h2 class="text-3xl font-black uppercase text-gray-900 mb-8 tracking-tight">Specials & Offers</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($promotions->where('active', true)->take(6) as $promo)
            <x-deal-card
                href="{{ $promo->full_content_url }}"
                target="_blank"
                image="{{ $promo->brief_image }}"
                :imageAlt="$promo->head"
                eyebrow="{{ $promo->type }}"
                title="{{ $promo->head }}"
                description="{{ $promo->brief }}" />
            @endforeach
        </div>
    </section>
    @endif

    {{-- Product Group Cards --}}
    <section class="bg-ink py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-xs uppercase tracking-[0.3em] font-black text-brand mb-1">Explore</p>
            <h2 class="text-3xl font-black uppercase text-white mb-10 tracking-tight">Shop by Category</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach(\App\Support\YamahaDivisions::filterVisible([
                    'road'       => 'Road Motorcycles',
                    'off-road'   => 'Off Road',
                    'atv-rov'    => 'ATV / ROV',
                    'watercraft' => 'Watercraft',
                    'golf-car'   => 'Golf Cars',
                ]) as $slug => $label)
                <x-media-tile
                    href="{{ route('yamaha.group', $slug) }}"
                    image="{{ $groupPreviews[$slug]['image'] ?? null }}"
                    :imageAlt="$label"
                    label="{{ $label }}"
                    ratio="16/9" />
                @endforeach
            </div>
        </div>
    </section>

    {{-- Slider JS --}}
    @if($slides->count() > 1)
    <script>
        (function () {
            const slides    = document.querySelectorAll('.slider-slide');
            const texts     = document.querySelectorAll('.slider-text');
            const dots      = document.querySelectorAll('.slider-dot');
            let current     = 0;
            let timer;

            function goTo(n) {
                slides[current].style.opacity = '0';
                slides[current].style.zIndex  = '0';
                texts[current].style.opacity  = '0';
                dots[current].classList.remove('bg-white', 'scale-110');
                dots[current].classList.add('bg-transparent');

                current = (n + slides.length) % slides.length;

                slides[current].style.opacity = '1';
                slides[current].style.zIndex  = '1';
                texts[current].style.opacity  = '1';
                dots[current].classList.add('bg-white', 'scale-110');
                dots[current].classList.remove('bg-transparent');
            }

            function startTimer() {
                clearInterval(timer);
                timer = setInterval(() => goTo(current + 1), 7000);
            }

            dots.forEach(dot => dot.addEventListener('click', () => {
                goTo(parseInt(dot.dataset.index));
                startTimer();
            }));

            document.getElementById('slider-prev').addEventListener('click', () => {
                goTo(current - 1); startTimer();
            });
            document.getElementById('slider-next').addEventListener('click', () => {
                goTo(current + 1); startTimer();
            });

            startTimer();
        })();
    </script>
    @endif

@endsection
