<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Models\Setting;

/**
 * Which payment methods checkout can actually offer: a gateway is only
 * offered when its credentials are configured AND it's switched on in
 * Admin > Payment Settings. Direct deposit needs no external credentials -
 * it's dealer-verified by hand - so only the switch applies to it, and
 * it's the fallback default when nothing else is available.
 */
class PaymentAvailability
{
    public function stripeConfigured(): bool
    {
        return filled(config('services.stripe.key')) && filled(config('services.stripe.secret'));
    }

    public function paypalConfigured(): bool
    {
        return filled(config('services.paypal.client_id')) && filled(config('services.paypal.client_secret'));
    }

    public function stripeEnabled(): bool
    {
        return Setting::get('payment_stripe_enabled', '1') === '1';
    }

    public function paypalEnabled(): bool
    {
        return Setting::get('payment_paypal_enabled', '1') === '1';
    }

    public function bankTransferEnabled(): bool
    {
        return Setting::get('payment_bank_transfer_enabled', '1') === '1';
    }

    /**
     * @return list<PaymentMethod>
     */
    public function availableMethods(): array
    {
        return array_values(array_filter([
            $this->stripeConfigured() && $this->stripeEnabled() ? PaymentMethod::Stripe : null,
            $this->paypalConfigured() && $this->paypalEnabled() ? PaymentMethod::PayPal : null,
            $this->bankTransferEnabled() ? PaymentMethod::BankTransfer : null,
        ]));
    }

    public function defaultMethod(): PaymentMethod
    {
        return $this->availableMethods()[0] ?? PaymentMethod::BankTransfer;
    }
}
