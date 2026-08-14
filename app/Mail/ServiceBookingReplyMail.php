<?php

namespace App\Mail;

use App\Models\ServiceBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ServiceBookingReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ServiceBooking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Service Booking Request — NorthStar Yamaha',
            replyTo: [new Address(
                config('mail.from.address', 'info@northstarmotorcycles.com.au'),
                config('mail.from.name', 'NorthStar Yamaha')
            )],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.service-booking-reply',
        );
    }
}
