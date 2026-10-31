<?php

namespace App\Enums;

enum OrderItemFulfillmentStatus: string
{
    case Fulfilled = 'fulfilled';
    case BackOrdered = 'back_ordered';

    public function label(): string
    {
        return match ($this) {
            self::Fulfilled => 'Fulfilled',
            self::BackOrdered => 'Back Ordered',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Fulfilled => 'success',
            self::BackOrdered => 'warning',
        };
    }
}
