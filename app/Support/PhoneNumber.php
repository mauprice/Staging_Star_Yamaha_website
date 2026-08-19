<?php

namespace App\Support;

class PhoneNumber
{
    /**
     * Normalizes an AU phone number to +61XXXXXXXXX so registration and
     * checkout-time dedupe matching compare like-for-like formats.
     */
    public static function normalize(string $raw): ?string
    {
        $digits = preg_replace('/\D+/', '', $raw);

        if (! $digits) {
            return null;
        }

        if (str_starts_with($digits, '61')) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        return '+61' . $digits;
    }
}
