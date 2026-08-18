@extends('yamaha.layout')

@section('title', 'News & Events')
@section('meta_description', 'Stay up to date with the latest Yamaha news, events, racing results and product launches at Star Yamaha.')

@section('content')

<div class="bg-ink border-b-2 border-brand">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <nav class="text-gray-500 text-xs uppercase tracking-widest font-semibold mb-4">
            <a href="{{ route('yamaha.index') }}" class="hover:text-white transition">Home</a>
            <span class="mx-2">›</span>
            <span class="text-white">News & Events</span>
        </nav>
        <h1 class="text-4xl font-black uppercase text-white tracking-tight">News & Events</h1>
        <p class="text-gray-400 mt-2 text-sm">The latest from Yamaha — racing, launches, events and more.</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    {{-- Type filter --}}
    @if($types->count() > 1)
    <div class="flex flex-wrap gap-2 mb-8" x-data="{ active: 'all' }">
        <button @click="active = 'all'"
                :class="active === 'all' ? 'bg-brand text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                class="px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-wider transition">
            All
        </button>
        @foreach($types as $type)
        <button @click="active = '{{ $type }}'"
                :class="active === '{{ $type }}' ? 'bg-brand text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                class="px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-wider transition">
            {{ $type }}
        </button>
        @endforeach
    </div>
    @endif

    @if($news->isEmpty())
        <div class="text-center py-24">
            <p class="text-gray-400 text-lg">No news articles available right now.</p>
            <p class="text-gray-400 text-sm mt-2">Check back soon or <a href="{{ route('yamaha.about') }}" class="text-brand">contact us</a> for updates.</p>
        </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"
         @if($types->count() > 1) x-data="{ active: 'all' }" @endif>
        @foreach($news as $article)
        <a href="{{ route('yamaha.news.show', ['id' => $article->id, 'slug' => $article->slug]) }}"
           class="group flex flex-col rounded-xl overflow-hidden border border-gray-200 hover:border-brand hover:shadow-lg transition bg-white"
           @if($types->count() > 1)
           x-show="active === 'all' || active === '{{ $article->type }}'"
           @endif>

            {{-- Image --}}
            <div class="relative overflow-hidden bg-gray-100" style="aspect-ratio: 3/2;">
                @if($article->brief_image)
                <img src="{{ trim($article->brief_image) }}"
                     alt="{{ $article->head }}"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                @else
                <div class="w-full h-full flex items-center justify-center bg-gray-100">
                    <img src="/images/star_yamaha_honda_logo.png" alt="Star Yamaha" class="h-12 opacity-30">
                </div>
                @endif

                @if($article->type)
                <span class="absolute top-3 left-3 bg-brand text-white text-[0.65rem] font-black uppercase tracking-widest px-2.5 py-1 rounded shadow">
                    {{ $article->type }}
                </span>
                @endif
            </div>

            {{-- Content --}}
            <div class="flex flex-col flex-1 p-5">
                <h2 class="font-black text-gray-900 text-base leading-snug group-hover:text-brand transition mb-2">
                    {{ $article->head }}
                </h2>
                @if($article->clean_brief)
                <p class="text-gray-500 text-sm leading-relaxed line-clamp-3 flex-1">
                    {{ $article->clean_brief }}
                </p>
                @endif
                <span class="mt-4 text-brand text-xs font-black uppercase tracking-widest">
                    Read More →
                </span>
            </div>
        </a>
        @endforeach
    </div>
    @endif

</div>

@endsection
