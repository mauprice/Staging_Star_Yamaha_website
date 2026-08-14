@props([
    'href' => null,
    'image' => null,
    'imageAlt' => '',
    'label' => null,
    'sublabel' => 'Browse Range →',
    'ratio' => '16/9',
])

<a @if($href) href="{{ $href }}" @endif
   {{ $attributes->merge(['class' => 'group relative flex items-end rounded overflow-hidden bg-ink shadow hover:shadow-2xl transition-all duration-300']) }}
   style="aspect-ratio: {{ $ratio }};">
    @if($image)
    <img src="{{ $image }}" alt="{{ $imageAlt }}"
         class="absolute inset-0 w-full h-full object-cover opacity-70 group-hover:opacity-90 group-hover:scale-105 transition-all duration-500">
    @endif
    <div class="absolute inset-0 bg-gradient-to-t from-ink/90 via-ink/20 to-transparent"></div>
    <div class="relative p-5 w-full">
        <h3 class="text-white text-xl font-black uppercase leading-tight group-hover:text-brand-line transition">
            {{ $label }}
        </h3>
        @if($sublabel)
        <span class="text-gray-300 text-xs font-bold uppercase tracking-wide group-hover:text-brand-line transition">
            {{ $sublabel }}
        </span>
        @endif
    </div>
    <div class="absolute bottom-0 left-0 w-0 group-hover:w-full h-[3px] bg-brand transition-all duration-300"></div>
</a>
