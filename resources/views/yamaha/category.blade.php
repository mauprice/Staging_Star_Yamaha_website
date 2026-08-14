@extends('yamaha.layout')

@section('title', $subCategoryName . ' — ' . $groupName)
@section('meta_description', 'Shop the Yamaha ' . $subCategoryName . ' range at NorthStar Yamaha. Compare models, specs and pricing from your authorised dealer.')

@section('content')

<style>
    .slider-hero { height: 60vh; min-height: 400px; }
    @media (max-width: 639px) {
        .slider-hero { height: 42vw; min-height: 200px; }
    }
</style>

    {{-- Full-width Image Slider --}}
    <div class="relative overflow-hidden bg-ink slider-hero" style="max-height: 650px;">

        {{-- Slides --}}
        @if($sliderImages->isNotEmpty())
            @foreach($sliderImages as $i => $img)
            <div class="slider-slide absolute inset-0 transition-opacity duration-700"
                 style="opacity: {{ $i === 0 ? '1' : '0' }}; z-index: {{ $i === 0 ? '1' : '0' }};">
                <x-responsive-hero
                    :mobile="$img['mobile'] ?? null"
                    :tablet="$img['tablet'] ?? null"
                    :desktop="$img['desktop'] ?? $img"
                    :alt="$subCategoryName"
                    :priority="$i === 0" />
            </div>
            @endforeach
        @endif

        {{-- Gradient overlay — dark on left for text, fades out right --}}
        <div class="absolute inset-0 z-10 bg-gradient-to-r from-black/70 via-black/35 to-black/5"></div>

        {{-- Text content --}}
        <div class="absolute inset-0 z-20">
            <div class="relative h-full max-w-7xl mx-auto px-4 sm:px-8 flex flex-col justify-end pb-12">
                <nav class="text-gray-300 text-xs uppercase tracking-widest font-semibold mb-3">
                    <a href="{{ route('yamaha.index') }}" class="hover:text-white transition">Home</a>
                    <span class="mx-2">›</span>
                    <a href="{{ route('yamaha.group', $group) }}" class="hover:text-white transition">{{ $groupName }}</a>
                    <span class="mx-2">›</span>
                    <span class="text-white">{{ $subCategoryName }}</span>
                </nav>
                <h1 class="text-3xl sm:text-5xl md:text-7xl font-black uppercase text-white leading-none tracking-tight">
                    {{ $subCategoryName }}
                </h1>
                <p class="text-gray-300 mt-2 text-sm font-medium">
                    {{ $products->count() }} model{{ $products->count() !== 1 ? 's' : '' }} available
                </p>
            </div>
        </div>

        {{-- Dot navigation --}}
        @if($sliderImages->count() > 1)
        <div class="absolute bottom-5 left-1/2 -translate-x-1/2 z-20 flex items-center gap-2">
            @foreach($sliderImages as $i => $img)
            <button class="slider-dot w-2.5 h-2.5 rounded-full border-2 border-white transition-all duration-300 {{ $i === 0 ? 'bg-white scale-110' : 'bg-transparent' }}"
                    data-index="{{ $i }}"></button>
            @endforeach
        </div>

        {{-- Prev / Next arrows --}}
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

    {{-- Product Grid --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <h2 class="text-3xl font-black uppercase text-gray-900 mb-8 tracking-tight">{{ $groupName }}</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($products as $product)
            @php
                $slug = strtolower(str_replace(' ', '-', $product->model_name));
                $firstColorImg = $product->colors->first()?->color_image;
                $image = $firstColorImg ?? $product->summary_image ?? $product->heroBanners->first()?->image;
            @endphp
            <x-product-card
                href="{{ route('yamaha.product', [$group, $category, $slug]) }}"
                image="{{ $image }}"
                :imageAlt="$product->model_name"
                fit="contain"
                eyebrow="{{ $product->year_model }}"
                title="{{ $product->model_name }}"
                description="{{ $product->item_description }}"
                data-colors="{{ json_encode($product->colors->pluck('color_image')->filter()->values()) }}">

                <div class="flex items-end justify-between">
                    @if($product->recommended_retail > 0)
                    <div>
                        <p class="text-xl font-black text-brand leading-none">
                            ${{ number_format($product->recommended_retail, 0) }}
                        </p>
                        <p class="text-[10px] text-gray-400">RRP inc. GST</p>
                    </div>
                    @else
                    <p class="text-sm text-gray-400 italic">Contact for pricing</p>
                    @endif

                    @if($product->colors->count() > 0)
                    <div class="flex gap-1 mb-1">
                        @foreach($product->colors->take(5) as $color)
                        <div class="w-3.5 h-3.5 rounded-full border border-gray-300 flex-shrink-0"
                             style="background-color: {{ $color->color_code ?: '#e5e7eb' }}"
                             title="{{ $color->color_name }}"></div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </x-product-card>
            @endforeach
        </div>
    </section>

    {{-- Slider JS --}}
    @if($sliderImages->count() > 1)
    <script>
        (function () {
            const slides = document.querySelectorAll('.slider-slide');
            const dots   = document.querySelectorAll('.slider-dot');
            let current  = 0;
            let timer;

            function goTo(n) {
                slides[current].style.opacity = '0';
                slides[current].style.zIndex  = '0';
                dots[current].classList.remove('bg-white', 'scale-110');
                dots[current].classList.add('bg-transparent');

                current = (n + slides.length) % slides.length;

                slides[current].style.opacity = '1';
                slides[current].style.zIndex  = '1';
                dots[current].classList.add('bg-white', 'scale-110');
                dots[current].classList.remove('bg-transparent');
            }

            function startTimer() {
                clearInterval(timer);
                timer = setInterval(() => goTo(current + 1), 6000);
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

    {{-- Card colour swipe on mobile --}}
    <script>
        document.querySelectorAll('[data-colors]').forEach(function (card) {
            var colors = JSON.parse(card.dataset.colors || '[]');
            if (colors.length < 2) return;

            var img = card.querySelector('img');
            if (!img) return;

            var current = 0;
            var startX = 0, startY = 0, didSwipe = false;

            card.addEventListener('touchstart', function (e) {
                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;
                didSwipe = false;
            }, { passive: true });

            card.addEventListener('touchend', function (e) {
                var dx = e.changedTouches[0].clientX - startX;
                var dy = e.changedTouches[0].clientY - startY;
                if (Math.abs(dx) < 40 || Math.abs(dx) < Math.abs(dy)) return;
                didSwipe = true;
                current = (current + (dx < 0 ? 1 : -1) + colors.length) % colors.length;
                img.src = colors[current];
            }, { passive: true });

            card.addEventListener('click', function (e) {
                if (didSwipe) { e.preventDefault(); didSwipe = false; }
            });
        });
    </script>

@endsection
