@extends('yamaha.layout')

@section('title', 'Parts Finder')
@section('meta_description', 'Search the genuine Yamaha parts catalogue by model, year or part number — exploded diagrams, part numbers and pricing for your bike, ATV or watercraft.')

@section('content')

    {{-- Page header --}}
    <div class="bg-ink border-b-2 border-brand">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <nav class="text-gray-500 text-xs uppercase tracking-widest font-semibold mb-4">
                <a href="{{ route('yamaha.index') }}" class="hover:text-white transition">Home</a>
                <span class="mx-2">›</span>
                <span class="text-white">Parts Finder</span>
            </nav>
            <h1 class="text-4xl font-black uppercase text-white tracking-tight">Parts Finder</h1>
            <p class="text-gray-400 mt-2 text-sm">Find genuine parts and exploded diagrams for your Yamaha, by model or part number.</p>
        </div>
    </div>

    <div id="parts-app"></div>

    @vite(['resources/js/parts-app.js'])

@endsection
