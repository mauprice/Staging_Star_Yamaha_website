<?php

namespace App\Http\Controllers;

use App\Mail\ServiceBookingMail;
use App\Models\ServiceBooking;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        return view('yamaha.tyres-service');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'           => 'required|string|max:100',
            'phone'          => 'required|string|max:30',
            'email'          => 'required|email|max:150',
            'vehicle_year'   => 'required|digits:4',
            'vehicle_model'  => 'required|string|max:150',
            'rego'           => 'nullable|string|max:20',
            'odometer'       => 'nullable|integer|min:0',
            'service_type'   => 'required|string',
            'preferred_date' => 'required|date|after:today',
            'preferred_time' => 'required|string',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $booking = ServiceBooking::create($data);

        $notificationEmail = Setting::get(
            'service_booking_email',
            env('BOOKING_EMAIL', 'service@staryamaha.com.au')
        );

        Mail::to($notificationEmail)
            ->send(new ServiceBookingMail($booking));

        return redirect()->route('yamaha.service')
            ->with('success', 'Thanks ' . $data['name'] . '! Your booking request has been sent. We\'ll be in touch shortly to confirm.');
    }
}
