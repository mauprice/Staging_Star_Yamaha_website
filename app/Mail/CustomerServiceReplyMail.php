<?php

namespace App\Mail;

use App\Filament\Resources\ServiceBookings\ServiceBookingResource;
use App\Models\ServiceBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerServiceReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ServiceBooking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Customer Reply — Booking #' . $this->booking->id . ' (' . $this->booking->name . ')',
            replyTo: [new Address($this->booking->email, $this->booking->name)],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.customer-service-reply',
            with: [
                'adminUrl' => ServiceBookingResource::getUrl('edit', ['record' => $this->booking]),
            ],
        );
    }
}
