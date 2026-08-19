<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PendingPayment = 'pending_payment';
    case AwaitingBankDeposit = 'awaiting_bank_deposit'; // Phase 4
    case Paid = 'paid';
    case PaymentFailed = 'payment_failed';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';
    case Processing = 'processing'; // Phase 5
    case Shipped = 'shipped'; // Phase 5
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::PendingPayment => 'Pending Payment',
            self::AwaitingBankDeposit => 'Awaiting Bank Deposit',
            self::Paid => 'Paid',
            self::PaymentFailed => 'Payment Failed',
            self::Cancelled => 'Cancelled',
            self::Refunded => 'Refunded',
            self::Processing => 'Processing',
            self::Shipped => 'Shipped',
            self::Completed => 'Completed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PendingPayment, self::AwaitingBankDeposit => 'warning',
            self::Paid, self::Completed => 'success',
            self::PaymentFailed, self::Cancelled => 'danger',
            self::Refunded => 'gray',
            self::Processing, self::Shipped => 'info',
        };
    }
}
