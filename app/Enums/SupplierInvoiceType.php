<?php

namespace App\Enums;

enum SupplierInvoiceType: string
{
    case Invoice = 'invoice';
    case Credit = 'credit';

    public function label(): string
    {
        return match ($this) {
            self::Invoice => 'Invoice',
            self::Credit => 'Credit',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Invoice => 'success',
            self::Credit => 'warning',
        };
    }
}
