@extends('yamaha.layout')

@section('title', 'Reply to Star Yamaha')
@section('meta_description', 'Send a message to Star Yamaha about your service booking.')

@section('content')

    {{-- Page header --}}
    <div class="bg-ink border-b-2 border-brand">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <nav class="text-gray-500 text-xs uppercase tracking-widest font-semibold mb-4">
                <a href="{{ route('yamaha.index') }}" class="hover:text-white transition">Home</a>
                <span class="mx-2">›</span>
                <span class="text-white">Reply to Booking</span>
            </nav>
            <h1 class="text-4xl font-black uppercase text-white tracking-tight">Reply to Star Yamaha</h1>
            <p class="text-gray-400 mt-2 text-sm">Booking #{{ $booking->id }} — {{ $booking->vehicle_year }} {{ $booking->vehicle_model }}</p>
        </div>
    </div>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        {{-- Success message --}}
        @if(session('success'))
        <div class="mb-8 p-5 rounded-lg border-l-4 border-green-500 bg-green-50 text-green-800">
            <p class="font-black text-sm">{{ session('success') }}</p>
        </div>
        @endif

        {{-- Validation errors --}}
        @if($errors->any())
        <div class="mb-8 p-5 rounded-lg border-l-4 border-red-500 bg-red-50 text-red-800">
            <p class="font-black text-sm mb-2">Please fix the following:</p>
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Booking summary --}}
        <div class="bg-gray-50 rounded-xl p-6 border border-gray-200 mb-8">
            <h2 class="text-xs font-black uppercase tracking-widest text-brand mb-4">Your Booking</h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <div>
                    <dt class="font-semibold text-gray-500">Name</dt>
                    <dd class="text-gray-900">{{ $booking->name }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-gray-500">Vehicle</dt>
                    <dd class="text-gray-900">{{ $booking->vehicle_year }} {{ $booking->vehicle_model }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-gray-500">Service Type</dt>
                    <dd class="text-gray-900">{{ $booking->service_type }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-gray-500">Requested Date</dt>
                    <dd class="text-gray-900">{{ $booking->preferred_date->format('l, d M Y') }} @ {{ $booking->preferred_time }}</dd>
                </div>
            </dl>
        </div>

        <h2 class="text-2xl font-black uppercase text-gray-900 mb-2 tracking-tight">Send a Message</h2>
        <p class="text-sm text-gray-500 mb-6">Reply below and we'll get back to you at {{ $booking->email }}.</p>

        <form method="POST" action="{{ url()->full() }}" class="space-y-6">
            @csrf

            <textarea name="message" rows="6" required
                      class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-red-500 resize-none @error('message') border-red-400 @enderror"
                      placeholder="Type your message here…">{{ old('message') }}</textarea>

            <button type="submit"
                    class="w-full py-4 px-8 text-white font-black text-sm uppercase tracking-widest rounded-lg transition-colors bg-brand hover:bg-brand-dark">
                Send Message →
            </button>
        </form>

        <p class="text-xs text-gray-400 text-center mt-6">
            Prefer to talk? Call us on
            <a href="{{ config('dealership.phone.href') }}" class="text-brand hover:text-brand-dark">{{ config('dealership.phone.display') }}</a>
        </p>
    </div>

@endsection
