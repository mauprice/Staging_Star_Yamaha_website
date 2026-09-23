<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderShippedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Order $order)
    {
        $this->order->loadMissing(['items', 'shippingAddress']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your Star Yamaha Order {$this->order->order_number} Has Shipped",
            replyTo: [new Address(config('dealership.email.enquiries'), config('dealership.name'))],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.order-shipped', with: ['order' => $this->order]);
    }
}
