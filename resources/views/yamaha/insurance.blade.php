@extends('yamaha.layout')

@section('title', 'Yamaha Motor Insurance')
@section('meta_description', 'Protect your ride with Yamaha Motor Insurance. Comprehensive cover for motorcycles, scooters, watercraft and more. Get a quote through NorthStar Yamaha.')

@section('content')

    {{-- Page header --}}
    <div class="bg-ink border-b-2 border-brand">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <nav class="text-gray-500 text-xs uppercase tracking-widest font-semibold mb-4">
                <a href="{{ route('yamaha.index') }}" class="hover:text-white transition">Home</a>
                <span class="mx-2">›</span>
                <span class="text-white">Insurance</span>
            </nav>
            <h1 class="text-4xl font-black uppercase text-white tracking-tight">Yamaha Motor Insurance</h1>
            <p class="text-gray-400 mt-2 text-sm">Get a quote for your Yamaha — motorcycle, watercraft, ATV and more.</p>
        </div>
    </div>

    {{-- JotForm embed --}}
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <iframe
            id="JotFormIFrame-210237648192860"
            title="YMI Quote Request"
            onload="window.parent.scrollTo(0,0)"
            allowtransparency="true"
            allowfullscreen="true"
            allow="geolocation; microphone; camera"
            src="https://form.jotform.com/210237648192860?isIframeEmbed=1"
            frameborder="0"
            scrolling="no"
            style="min-width: 100%; height: 1041px; border: none;">
        </iframe>
    </div>

    <script>
        // Allow JotForm to resize the iframe dynamically
        window.addEventListener('message', function (e) {
            if (typeof e.data === 'object' && e.data.action === 'setHeight') {
                const frame = document.getElementById('JotFormIFrame-210237648192860');
                if (frame) frame.style.height = e.data.height + 'px';
            }
        });
    </script>

@endsection
