<?php

namespace App\Http\Controllers;

use App\Mail\CustomerServiceReplyMail;
use App\Models\ServiceBooking;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

        $notificationEmails = Setting::getEmailList(
            'service_booking_email',
            env('BOOKING_EMAIL', 'info@staryamaha.com.au')
        );

        try {
            Mail::to($notificationEmails)
                ->send(new CustomerServiceReplyMail($serviceBooking));
        } catch (\Throwable $e) {
            Log::error('Failed to send customer service reply notification email', [
                'booking_id' => $serviceBooking->id,
                'to'         => $notificationEmails,
                'error'      => $e->getMessage(),
            ]);
        }

        return redirect($request->fullUrl())
            ->with('success', 'Thanks! Your message has been sent to Star Yamaha — we\'ll be in touch shortly.');
    }
}
