@props([
    'href' => null,
    'target' => null,
    'image' => null,
    'imageAlt' => '',
    'badge' => null,
    'eyebrow' => null,
    'title' => null,
    'description' => null,
    'ctaLabel' => 'View Offer →',
])

{{-- Media Tile variant with a light body — used for specials/promo/deal cards. --}}
<a @if($href) href="{{ $href }}" @endif @if($target) target="{{ $target }}" rel="noopener" @endif
   {{ $attributes->merge(['class' => 'group flex flex-col bg-white rounded border border-gray-200 shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden']) }}>

    <div class="relative overflow-hidden" style="aspect-ratio:3/2;">
        @if($image)
        <img src="{{ $image }}" alt="{{ $imageAlt }}"
             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
        @endif
        @if($badge)
        <span class="absolute top-3 left-3 bg-brand text-white text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-sm">
            {{ $badge }}
        </span>
        @endif
    </div>

    <div class="p-4 flex flex-col flex-1">
        @if($eyebrow)
        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand mb-1.5">{{ $eyebrow }}</p>
        @endif
        @if($title)
        <h3 class="font-black text-gray-900 text-base uppercase leading-tight group-hover:text-brand transition">
            {{ $title }}
        </h3>
        @endif
        @if($description)
        <p class="text-sm text-gray-500 mt-2 leading-relaxed line-clamp-2">{{ $description }}</p>
        @endif

        <div class="mt-auto pt-3">
            {{ $slot->isEmpty() ? '' : $slot }}
            @if($slot->isEmpty() && $ctaLabel)
            <span class="text-brand text-xs font-black group-hover:underline uppercase tracking-wide">
                {{ $ctaLabel }}
            </span>
            @endif
        </div>
    </div>
</a>
