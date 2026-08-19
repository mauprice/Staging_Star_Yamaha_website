<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent only to the verified email on an existing account when a guest
 * checkout's typed email/phone silently matched that account - never to
 * whatever was typed at checkout. Invites them to log in and view the
 * order; the deep link is auth-protected and ownership-checked, so a
 * forwarded email is useless to anyone but the real account holder.
 */
class OrderAccountMatchMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "An order was placed on your Star Yamaha account — {$this->order->order_number}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.order-account-match', with: ['order' => $this->order]);
    }
}
