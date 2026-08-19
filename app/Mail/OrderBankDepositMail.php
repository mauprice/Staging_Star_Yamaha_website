<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent instead of OrderReceiptMail when payment_method is bank transfer -
 * there's nothing to receipt yet (no money has moved), so this is a
 * notification of the order plus the deposit details, not a receipt.
 */
class OrderBankDepositMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Order $order)
    {
        $this->order->loadMissing('items');
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Your Star Yamaha Order {$this->order->order_number} — Direct Deposit Details");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.order-bank-deposit', with: [
            'order' => $this->order,
            'bankName' => Setting::get('bank_deposit_bank_name'),
            'accountName' => Setting::get('bank_deposit_account_name'),
            'bsb' => Setting::get('bank_deposit_bsb'),
            'accountNumber' => Setting::get('bank_deposit_account_number'),
        ]);
    }
}
