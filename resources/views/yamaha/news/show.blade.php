@extends('yamaha.layout')

@section('title', $article->head . ' — News & Events')
@section('meta_description', Str::limit($article->clean_brief, 160))
@section('og_type', 'article')
@section('og_image', trim($article->image ?? $article->brief_image ?? url('/images/star_yamaha_honda_logo.png')))

@section('content')

{{-- Hero banner --}}
@if($article->image)
<div class="w-full overflow-hidden bg-ink" style="max-height: 480px;">
    <img src="{{ trim($article->image) }}"
         alt="{{ $article->head }}"
         class="w-full object-cover object-center"
         style="max-height: 480px;">
</div>
@endif

<div class="bg-ink border-b-2 border-brand">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <nav class="text-gray-500 text-xs uppercase tracking-widest font-semibold mb-4">
            <a href="{{ route('yamaha.index') }}" class="hover:text-white transition">Home</a>
            <span class="mx-2">›</span>
            <a href="{{ route('yamaha.news') }}" class="hover:text-white transition">News & Events</a>
            <span class="mx-2">›</span>
            <span class="text-white">{{ Str::limit($article->head, 50) }}</span>
        </nav>

        @if($article->type)
        <span class="inline-block bg-brand text-white text-[0.65rem] font-black uppercase tracking-widest px-3 py-1 rounded mb-3">
            {{ $article->type }}
        </span>
        @endif

        <h1 class="text-3xl sm:text-4xl font-black uppercase text-white tracking-tight leading-tight">
            {{ $article->head }}
        </h1>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">

        {{-- Main content --}}
        <div class="lg:col-span-2">
            @if($article->content)
            <div class="prose prose-lg prose-gray max-w-none leading-relaxed">
                {!! $article->content !!}
            </div>
            @elseif($article->clean_brief)
            <p class="text-gray-600 text-lg leading-relaxed">{{ $article->clean_brief }}</p>
            @endif

            @if($article->full_content_url)
            <div class="mt-8">
                <a href="{{ $article->full_content_url }}"
                   target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 text-brand font-black text-sm uppercase tracking-widest hover:text-brand-dark transition">
                    View on Yamaha Website
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
            </div>
            @endif

            <div class="mt-10 pt-6 border-t border-gray-200">
                <a href="{{ route('yamaha.news') }}"
                   class="text-sm font-semibold text-gray-500 hover:text-gray-700 transition">
                    ← Back to News & Events
                </a>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            <div class="rounded-xl p-6 text-white bg-accent">
                <h3 class="font-black text-base uppercase mb-1">Want to Know More?</h3>
                <p class="text-blue-100/80 text-sm mb-4">Talk to our team about Yamaha news, events and products.</p>
                <a href="{{ config('dealership.phone.href') }}"
                   class="block text-center font-black py-3 px-6 rounded-lg text-white mb-2 transition bg-brand hover:bg-brand-dark">
                    Call {{ config('dealership.phone.display') }}
                </a>
                <a href="mailto:{{ config('dealership.email.sales') }}"
                   class="block text-center text-blue-100/80 text-sm hover:text-white transition">
                    Email Us →
                </a>
            </div>

            <div class="rounded-xl border border-gray-200 p-6">
                <h3 class="font-black text-sm uppercase tracking-widest text-gray-900 mb-4">Browse Yamaha Range</h3>
                <a href="{{ route('yamaha.index') }}"
                   class="block py-2 text-sm text-gray-600 hover:text-brand transition border-b border-gray-100">
                    View All Bikes →
                </a>
                <a href="{{ route('yamaha.specials') }}"
                   class="block py-2 text-sm text-gray-600 hover:text-brand transition border-b border-gray-100">
                    Current Specials →
                </a>
                <a href="{{ route('yamaha.preowned') }}"
                   class="block py-2 text-sm text-gray-600 hover:text-brand transition">
                    Pre-Owned →
                </a>
            </div>
        </div>

    </div>
</div>

@endsection
