<?php

namespace App\Mail;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Order $order)
    {
        $this->order->loadMissing(['items', 'shippingAddress', 'billingAddress']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Your Star Yamaha Order {$this->order->order_number}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.order-receipt', with: ['order' => $this->order]);
    }

    public function attachments(): array
    {
        $pdf = Pdf::loadView('invoices.order', ['order' => $this->order])
            ->setPaper('a4', 'portrait')
            ->output();

        return [
            Attachment::fromData(fn () => $pdf, "invoice-{$this->order->order_number}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
