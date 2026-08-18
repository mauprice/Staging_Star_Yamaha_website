@extends('yamaha.layout')

@section('title', 'Tyres & Service Booking')
@section('meta_description', 'Book your motorcycle for a service or tyre fitting at Star Yamaha. Expert technicians, genuine parts, and fast turnaround.')

@section('content')

    {{-- Page header --}}
    <div class="bg-ink border-b-2 border-brand">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <nav class="text-gray-500 text-xs uppercase tracking-widest font-semibold mb-4">
                <a href="{{ route('yamaha.index') }}" class="hover:text-white transition">Home</a>
                <span class="mx-2">›</span>
                <span class="text-white">Tyres & Service</span>
            </nav>
            <h1 class="text-4xl font-black uppercase text-white tracking-tight">Tyres & Service</h1>
            <p class="text-gray-400 mt-2 text-sm">Book your Yamaha in for a service, tyre fitting, or inspection.</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">

            {{-- Booking form --}}
            <div class="lg:col-span-2">

                {{-- Success message --}}
                @if(session('success'))
                <div class="mb-8 p-5 rounded-lg border-l-4 border-green-500 bg-green-50 text-green-800">
                    <p class="font-black text-sm">{{ session('success') }}</p>
                </div>
                @endif

                {{-- Validation errors --}}
                @if($errors->any())
                <div class="mb-8 p-5 rounded-lg border-l-4 border-red-500 bg-red-50 text-red-800">
                    <p class="font-black text-sm mb-2">Please fix the following errors:</p>
                    <ul class="list-disc list-inside text-sm space-y-1">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <h2 class="text-2xl font-black uppercase text-gray-900 mb-6 tracking-tight">Book a Service</h2>

                <form method="POST" action="{{ route('yamaha.service.store') }}" class="space-y-6">
                    @csrf

                    {{-- Customer details --}}
                    <fieldset class="border border-gray-200 rounded-lg p-6">
                        <legend class="text-xs font-black uppercase tracking-widest text-brand px-2">Your Details</legend>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mt-2">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wide text-gray-600 mb-1">Full Name *</label>
                                <input type="text" name="name" value="{{ old('name') }}" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-red-500 @error('name') border-red-400 @enderror"
                                       placeholder="Jane Smith">
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wide text-gray-600 mb-1">Phone *</label>
                                <input type="tel" name="phone" value="{{ old('phone') }}" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-red-500 @error('phone') border-red-400 @enderror"
                                       placeholder="07 3000 0000">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-black uppercase tracking-wide text-gray-600 mb-1">Email *</label>
                                <input type="email" name="email" value="{{ old('email') }}" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-red-500 @error('email') border-red-400 @enderror"
                                       placeholder="jane@example.com">
                            </div>
                        </div>
                    </fieldset>

                    {{-- Vehicle details --}}
                    <fieldset class="border border-gray-200 rounded-lg p-6">
                        <legend class="text-xs font-black uppercase tracking-widest text-brand px-2">Vehicle Details</legend>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mt-2">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wide text-gray-600 mb-1">Year *</label>
                                <input type="number" name="vehicle_year" value="{{ old('vehicle_year') }}" required
                                       min="1990" max="{{ date('Y') + 1 }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-red-500 @error('vehicle_year') border-red-400 @enderror"
                                       placeholder="{{ date('Y') }}">
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wide text-gray-600 mb-1">Make & Model *</label>
                                <input type="text" name="vehicle_model" value="{{ old('vehicle_model') }}" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-red-500 @error('vehicle_model') border-red-400 @enderror"
                                       placeholder="Yamaha MT-07">
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wide text-gray-600 mb-1">Registration</label>
                                <input type="text" name="rego" value="{{ old('rego') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-red-500"
                                       placeholder="123 ABC">
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wide text-gray-600 mb-1">Odometer (km)</label>
                                <input type="number" name="odometer" value="{{ old('odometer') }}" min="0"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-red-500"
                                       placeholder="15000">
                            </div>
                        </div>
                    </fieldset>

                    {{-- Service details --}}
                    <fieldset class="border border-gray-200 rounded-lg p-6">
                        <legend class="text-xs font-black uppercase tracking-widest text-brand px-2">Service Request</legend>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mt-2">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-black uppercase tracking-wide text-gray-600 mb-1">Service Type *</label>
                                <select name="service_type" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-red-500 bg-white @error('service_type') border-red-400 @enderror">
                                    <option value="">— Select service —</option>
                                    <optgroup label="Scheduled Service">
                                        <option value="Minor Service (1,000 km / 1 month)" {{ old('service_type') === 'Minor Service (1,000 km / 1 month)' ? 'selected' : '' }}>Minor Service (1,000 km / 1 month)</option>
                                        <option value="Standard Service (6,000 km / 12 months)" {{ old('service_type') === 'Standard Service (6,000 km / 12 months)' ? 'selected' : '' }}>Standard Service (6,000 km / 12 months)</option>
                                        <option value="Major Service (12,000 km / 24 months)" {{ old('service_type') === 'Major Service (12,000 km / 24 months)' ? 'selected' : '' }}>Major Service (12,000 km / 24 months)</option>
                                    </optgroup>
                                    <optgroup label="Tyres">
                                        <option value="Tyre Fitting (supply & fit)" {{ old('service_type') === 'Tyre Fitting (supply & fit)' ? 'selected' : '' }}>Tyre Fitting (supply & fit)</option>
                                        <option value="Tyre Fitting (customer supplied)" {{ old('service_type') === 'Tyre Fitting (customer supplied)' ? 'selected' : '' }}>Tyre Fitting (customer supplied)</option>
                                        <option value="Tyre Inspection & Advice" {{ old('service_type') === 'Tyre Inspection & Advice' ? 'selected' : '' }}>Tyre Inspection & Advice</option>
                                    </optgroup>
                                    <optgroup label="Other">
                                        <option value="Pre-Purchase Inspection" {{ old('service_type') === 'Pre-Purchase Inspection' ? 'selected' : '' }}>Pre-Purchase Inspection</option>
                                        <option value="Warranty Repair" {{ old('service_type') === 'Warranty Repair' ? 'selected' : '' }}>Warranty Repair</option>
                                        <option value="General Repair / Diagnosis" {{ old('service_type') === 'General Repair / Diagnosis' ? 'selected' : '' }}>General Repair / Diagnosis</option>
                                        <option value="Other (see notes)" {{ old('service_type') === 'Other (see notes)' ? 'selected' : '' }}>Other (see notes)</option>
                                    </optgroup>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wide text-gray-600 mb-1">Preferred Date *</label>
                                <input type="date" name="preferred_date" value="{{ old('preferred_date') }}" required
                                       min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-red-500 @error('preferred_date') border-red-400 @enderror">
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wide text-gray-600 mb-1">Preferred Time *</label>
                                <select name="preferred_time" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-red-500 bg-white @error('preferred_time') border-red-400 @enderror">
                                    <option value="">— Select time —</option>
                                    <option value="First available">First available</option>
                                    <option value="Morning (8am – 12pm)">Morning (8am – 12pm)</option>
                                    <option value="Afternoon (12pm – 5pm)">Afternoon (12pm – 5pm)</option>
                                    <option value="8:00 am">8:00 am</option>
                                    <option value="9:00 am">9:00 am</option>
                                    <option value="10:00 am">10:00 am</option>
                                    <option value="11:00 am">11:00 am</option>
                                    <option value="12:00 pm">12:00 pm</option>
                                    <option value="1:00 pm">1:00 pm</option>
                                    <option value="2:00 pm">2:00 pm</option>
                                    <option value="3:00 pm">3:00 pm</option>
                                    <option value="4:00 pm">4:00 pm</option>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-black uppercase tracking-wide text-gray-600 mb-1">Additional Notes</label>
                                <textarea name="notes" rows="4"
                                          class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-red-500 resize-none"
                                          placeholder="Describe the issue or anything we should know…">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </fieldset>

                    <button type="submit"
                            class="w-full py-4 px-8 text-white font-black text-sm uppercase tracking-widest rounded-lg transition-colors bg-brand hover:bg-brand-dark">
                        Send Booking Request →
                    </button>

                    <p class="text-xs text-gray-400 text-center">
                        We'll contact you within 1 business day to confirm your booking.
                    </p>
                </form>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">

                {{-- Contact info --}}
                <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                    <h3 class="text-xs font-black uppercase tracking-widest text-brand mb-4">Contact Service</h3>
                    <div class="space-y-3 text-sm text-gray-700">
                        <div class="flex items-start gap-3">
                            <svg class="w-4 h-4 text-brand mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            <div>
                                <p class="font-semibold">Phone</p>
                                <a href="{{ config('dealership.phone.href') }}" class="text-brand hover:text-brand-dark">{{ config('dealership.phone.display') }}</a>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <svg class="w-4 h-4 text-brand mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <div>
                                <p class="font-semibold">Email</p>
                                <a href="mailto:info@staryamaha.com.au" class="text-brand hover:text-brand-dark break-all">info@staryamaha.com.au</a>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <svg class="w-4 h-4 text-brand mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <p class="font-semibold">Hours</p>
                                <p class="text-gray-500 text-xs leading-relaxed">Mon – Fri: 8:00am – 5:00pm<br>Sat: 8:00am – 12:00pm<br>Sun: Closed</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- What to expect --}}
                <div class="bg-ink rounded-xl p-6 text-white">
                    <h3 class="text-xs font-black uppercase tracking-widest text-brand mb-4">What to Expect</h3>
                    <ul class="space-y-3 text-sm text-gray-300">
                        <li class="flex items-start gap-2">
                            <span class="text-brand font-black mt-0.5">1.</span>
                            Submit the booking form
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-brand font-black mt-0.5">2.</span>
                            We'll call or email to confirm your time
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-brand font-black mt-0.5">3.</span>
                            Drop off your vehicle on the day
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-brand font-black mt-0.5">4.</span>
                            We'll contact you when it's ready
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </div>

@endsection
