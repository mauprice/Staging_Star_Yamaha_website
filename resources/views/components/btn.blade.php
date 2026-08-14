@props([
    'variant' => 'primary', // primary | secondary | dark | accent | text
    'href' => null,
    'target' => null,
    'type' => 'button',
])

@php
$base = 'inline-flex items-center justify-center font-black text-xs uppercase tracking-widest transition-colors';

$variants = [
    'primary'   => 'text-white bg-brand hover:bg-brand-dark rounded px-6 py-3.5',
    'secondary' => 'text-gray-900 bg-transparent border-2 border-gray-900 hover:bg-gray-900 hover:text-white rounded px-6 py-3',
    'dark'      => 'text-white bg-ink border-2 border-white/80 hover:bg-ink-2 rounded px-6 py-3',
    'accent'    => 'text-white bg-accent hover:opacity-90 rounded px-6 py-3.5',
    'text'      => 'text-brand hover:text-brand-dark font-black text-xs',
];

$classes = $base . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp

@if($href)
<a href="{{ $href }}" @if($target) target="{{ $target }}" rel="noopener" @endif
   {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
