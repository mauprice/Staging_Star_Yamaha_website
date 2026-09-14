<?php

namespace App\Mail;

use App\Models\ServiceBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ServiceBookingConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ServiceBooking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'We\'ve received your service booking request — Star Yamaha',
            replyTo: [new Address('info@staryamaha.com.au', 'Star Yamaha')],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.service-booking-confirmation',
        );
    }
}
