@props([
    'href' => null,
    'image' => null,
    'imageAlt' => '',
    'fit' => 'contain', // 'contain' (studio shots on a white backdrop) | 'fill' (crop-to-cover, for photos of variable quality e.g. pre-owned/specials)
    'badge' => null,
    'badgeClass' => 'bg-brand text-white',
    'eyebrow' => null,
    'title' => null,
    'description' => null,
])

<a @if($href) href="{{ $href }}" @endif
   {{ $attributes->merge(['class' => 'group flex flex-col bg-white rounded border border-gray-200 shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden']) }}>

    <div class="relative overflow-hidden {{ $fit === 'contain' ? 'bg-white flex items-center justify-center p-6' : 'bg-gray-100' }}" style="aspect-ratio:4/3;">
        @if($image)
        <img src="{{ $image }}" alt="{{ $imageAlt }}"
             class="{{ $fit === 'contain' ? 'max-h-full max-w-full object-contain' : 'absolute inset-0 w-full h-full object-cover' }} group-hover:scale-105 transition-transform duration-500">
        @endif
        @if($badge)
        <span class="absolute top-3 left-3 {{ $badgeClass }} text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-sm">
            {{ $badge }}
        </span>
        @endif
    </div>

    <div class="p-4 border-t border-gray-100 flex flex-col flex-1">
        @if($eyebrow)
        <p class="text-[11px] font-mono font-semibold tracking-wide uppercase text-gray-400">{{ $eyebrow }}</p>
        @endif
        @if($title)
        <h3 class="font-black text-gray-900 text-base uppercase tracking-wide mt-0.5 group-hover:text-brand transition leading-tight">
            {{ $title }}
        </h3>
        @endif
        @if($description)
        <p class="text-sm text-gray-500 mt-1 flex-1 line-clamp-2">{{ $description }}</p>
        @endif
        <div class="mt-auto pt-3">
            {{ $slot }}
        </div>
    </div>
</a>
