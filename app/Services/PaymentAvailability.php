<?php

namespace App\Services;

use App\Enums\PaymentMethod;

/**
 * Which payment methods checkout can actually offer, based on whether
 * their gateway credentials are configured. Direct deposit needs no
 * external credentials - it's dealer-verified by hand - so it's always
 * available and is the fallback default when neither online gateway is
 * configured.
 */
class PaymentAvailability
{
    public function stripeConfigured(): bool
    {
        return filled(config('services.stripe.key')) && filled(config('services.stripe.secret'));
    }

    public function paypalConfigured(): bool
    {
        // PayPal gateway integration isn't built yet (planned for a later
        // phase) - always false so it's never offered as a selectable
        // option regardless of credentials being present in config.
        return false;
    }

    /**
     * @return list<PaymentMethod>
     */
    public function availableMethods(): array
    {
        return array_values(array_filter([
            $this->stripeConfigured() ? PaymentMethod::Stripe : null,
            $this->paypalConfigured() ? PaymentMethod::PayPal : null,
            PaymentMethod::BankTransfer,
        ]));
    }

    public function defaultMethod(): PaymentMethod
    {
        return match (true) {
            $this->stripeConfigured() => PaymentMethod::Stripe,
            $this->paypalConfigured() => PaymentMethod::PayPal,
            default => PaymentMethod::BankTransfer,
        };
    }
}
