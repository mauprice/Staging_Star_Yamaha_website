@props(['offers'])

@if($offers->isNotEmpty())
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 pt-5 pb-3">
        <h3 class="text-xs uppercase tracking-[0.3em] font-black text-brand">Current Offers</h3>
    </div>
    <div class="divide-y divide-gray-100">
        @foreach($offers as $offer)
        <a href="{{ $offer->linkUrl }}"
           @if(!$offer->honda_model_id && $offer->children->isEmpty()) target="_blank" rel="noopener" @endif
           class="group flex gap-3 items-center p-4 hover:bg-gray-50 transition">
            @if($offer->image)
            <span class="w-14 h-14 flex-shrink-0 rounded-lg overflow-hidden bg-gray-50 flex items-center justify-center">
                <img src="{{ $offer->image->url() }}" alt="{{ $offer->title }}" class="w-full h-full object-contain">
            </span>
            @endif
            <span class="min-w-0 flex-1">
                <span class="block font-black text-sm text-gray-900 uppercase leading-tight group-hover:text-brand transition">
                    {{ $offer->title }}
                </span>
                @if($offer->price_label)
                <span class="block text-brand text-xs font-black mt-0.5">{{ $offer->price_label }}</span>
                @elseif($offer->subtitle)
                <span class="block text-gray-500 text-xs mt-0.5 line-clamp-2">{{ $offer->subtitle }}</span>
                @endif
            </span>
            <svg class="w-4 h-4 text-gray-300 flex-shrink-0 group-hover:text-brand transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        @endforeach
    </div>
    @if($offers->count() > 1)
    <a href="{{ route('honda.offers') }}" class="block text-center text-xs font-black uppercase tracking-wide text-brand hover:text-brand-dark py-3 bg-gray-50 border-t border-gray-100 transition">
        View all offers →
    </a>
    @endif
</div>
@endif
