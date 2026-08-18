@extends('yamaha.layout')

@section('title', 'Yamaha Motor Finance')
@section('meta_description', 'Finance your new Yamaha with flexible payment options through Star Yamaha. Low rates and easy approval — ride away sooner.')

@section('content')

    {{-- Page header --}}
    <div class="bg-ink border-b-2 border-brand">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <nav class="text-gray-500 text-xs uppercase tracking-widest font-semibold mb-4">
                <a href="{{ route('yamaha.index') }}" class="hover:text-white transition">Home</a>
                <span class="mx-2">›</span>
                <span class="text-white">Finance</span>
            </nav>
            <h1 class="text-4xl font-black uppercase text-white tracking-tight">Yamaha Motor Finance</h1>
            <p class="text-gray-400 mt-2 text-sm">Get a finance quote and own your Yamaha sooner.</p>
        </div>
    </div>

    {{-- Finance iframe --}}
    <div class="bg-gray-100">
        <iframe
            src="https://finance.yamaha-motor.com.au/finance/createquote/ymfaus/aus/108869"
            title="Yamaha Motor Finance Quote"
            class="w-full border-0 block"
            style="min-height: 100vh; height: 900px; border: none; overflow: hidden; display: block;"
            scrolling="no"
            spellcheck="false"
            id="finance-iframe">
        </iframe>
    </div>

    <script>
        window.addEventListener('message', function (e) {
            const frame = document.getElementById('finance-iframe');
            if (!frame) return;
            if (typeof e.data === 'object' && e.data.height) {
                frame.style.height = e.data.height + 'px';
            }
            if (typeof e.data === 'string') {
                const h = parseInt(e.data);
                if (!isNaN(h) && h > 400) frame.style.height = h + 'px';
            }
        });
    </script>

@endsection
