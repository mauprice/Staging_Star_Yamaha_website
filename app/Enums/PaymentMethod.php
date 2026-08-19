<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Stripe = 'stripe';
    case PayPal = 'paypal'; // Phase 4
    case BankTransfer = 'bank_transfer'; // Phase 4

    public function label(): string
    {
        return match ($this) {
            self::Stripe => 'Card (Stripe)',
            self::PayPal => 'PayPal',
            self::BankTransfer => 'Direct Deposit',
        };
    }
}
