<?php

namespace App\Http\Controllers;

use App\Mail\CustomerServiceReplyMail;
use App\Models\ServiceBooking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ServiceBookingReplyController extends Controller
{
    public function show(ServiceBooking $serviceBooking): View
    {
        return view('yamaha.service-booking-reply', ['booking' => $serviceBooking]);
    }

    public function store(Request $request, ServiceBooking $serviceBooking): RedirectResponse
    {
        $data = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $serviceBooking->update([
            'customer_reply'      => $data['message'],
            'customer_replied_at' => now(),
        ]);

        Mail::to(env('BOOKING_EMAIL', 'service@staryamaha.com.au'))
            ->send(new CustomerServiceReplyMail($serviceBooking));

        return redirect($request->fullUrl())
            ->with('success', 'Thanks! Your message has been sent to Star Yamaha — we\'ll be in touch shortly.');
    }
}
