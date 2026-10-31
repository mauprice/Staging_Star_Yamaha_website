<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\EnvFileWriter;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class PaymentSettings extends Page
{
    protected string $view = 'filament.admin.pages.payment-settings';

    protected static string|\UnitEnum|null $navigationGroup = 'Admin';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $navigationLabel = 'Payment Settings';

    protected static ?string $title = 'Payment Settings';

    protected static ?int $navigationSort = 2;

    public string $bank_deposit_bank_name = '';
    public string $bank_deposit_account_name = '';
    public string $bank_deposit_bsb = '';
    public string $bank_deposit_account_number = '';

    // Non-secret - safe to show the current value.
    public string $stripe_key = '';
    public string $paypal_client_id = '';
    public string $paypal_mode = 'sandbox';
    public string $paypal_webhook_id = '';

    // Whether each method is switched on for the online store checkout -
    // independent of whether its credentials are configured, so an admin
    // can temporarily turn a gateway off without clearing its keys.
    public bool $stripe_enabled = true;
    public bool $paypal_enabled = true;
    public bool $bank_transfer_enabled = true;

    // Secrets are never loaded into the form - these start (and stay,
    // after saving) blank. Leaving one blank on save means "don't
    // change it"; the matching clear_* checkbox is the only way to
    // actually remove a saved secret.
    public string $stripe_secret = '';
    public string $stripe_webhook_secret = '';
    public string $paypal_client_secret = '';

    public bool $clear_stripe_secret = false;
    public bool $clear_stripe_webhook_secret = false;
    public bool $clear_paypal_client_secret = false;

    // Tracked explicitly rather than re-read from config() after saving -
    // Artisan::call('config:clear') only clears the cache *file*, it can't
    // make this already-booted PHP process see the new .env values, so a
    // post-save config() read would still show what was true when the
    // request started, not what was just written.
    public bool $stripe_secret_saved = false;
    public bool $stripe_webhook_secret_saved = false;
    public bool $paypal_client_secret_saved = false;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['Admin', 'Manager']) ?? false;
    }

    public function mount(): void
    {
        $this->bank_deposit_bank_name = Setting::get('bank_deposit_bank_name');
        $this->bank_deposit_account_name = Setting::get('bank_deposit_account_name');
        $this->bank_deposit_bsb = Setting::get('bank_deposit_bsb');
        $this->bank_deposit_account_number = Setting::get('bank_deposit_account_number');

        $this->stripe_key = (string) config('services.stripe.key');
        $this->paypal_client_id = (string) config('services.paypal.client_id');
        $this->paypal_mode = (string) (config('services.paypal.mode') ?: 'sandbox');
        $this->paypal_webhook_id = (string) config('services.paypal.webhook_id');

        $this->stripe_secret_saved = filled(config('services.stripe.secret'));
        $this->stripe_webhook_secret_saved = filled(config('services.stripe.webhook_secret'));
        $this->paypal_client_secret_saved = filled(config('services.paypal.client_secret'));

        $this->stripe_enabled = Setting::get('payment_stripe_enabled', '1') === '1';
        $this->paypal_enabled = Setting::get('payment_paypal_enabled', '1') === '1';
        $this->bank_transfer_enabled = Setting::get('payment_bank_transfer_enabled', '1') === '1';
    }

    public function save(): void
    {
        Setting::set('bank_deposit_bank_name', trim($this->bank_deposit_bank_name));
        Setting::set('bank_deposit_account_name', trim($this->bank_deposit_account_name));
        Setting::set('bank_deposit_bsb', trim($this->bank_deposit_bsb));
        Setting::set('bank_deposit_account_number', trim($this->bank_deposit_account_number));

        Setting::set('payment_stripe_enabled', $this->stripe_enabled ? '1' : '0');
        Setting::set('payment_paypal_enabled', $this->paypal_enabled ? '1' : '0');
        Setting::set('payment_bank_transfer_enabled', $this->bank_transfer_enabled ? '1' : '0');

        $writer = app(EnvFileWriter::class);

        if (! $writer->isWritable()) {
            Notification::make()
                ->title('Bank details saved, but gateway keys were not')
                ->body('The server\'s .env file is not writable by the web process - ask your host to fix file permissions.')
                ->danger()
                ->send();

            return;
        }

        $stripeSecret = $this->resolveSecret($this->stripe_secret, $this->clear_stripe_secret);
        $stripeWebhookSecret = $this->resolveSecret($this->stripe_webhook_secret, $this->clear_stripe_webhook_secret);
        $paypalClientSecret = $this->resolveSecret($this->paypal_client_secret, $this->clear_paypal_client_secret);

        $writer->set([
            'STRIPE_KEY' => trim($this->stripe_key),
            'STRIPE_SECRET' => $stripeSecret,
            'STRIPE_WEBHOOK_SECRET' => $stripeWebhookSecret,
            'PAYPAL_CLIENT_ID' => trim($this->paypal_client_id),
            'PAYPAL_CLIENT_SECRET' => $paypalClientSecret,
            'PAYPAL_MODE' => trim($this->paypal_mode) ?: 'sandbox',
            'PAYPAL_WEBHOOK_ID' => trim($this->paypal_webhook_id),
        ]);

        // null means "left untouched" - only update the tracked saved-state
        // when a value was actually written this time.
        if ($stripeSecret !== null) {
            $this->stripe_secret_saved = $stripeSecret !== '';
        }
        if ($stripeWebhookSecret !== null) {
            $this->stripe_webhook_secret_saved = $stripeWebhookSecret !== '';
        }
        if ($paypalClientSecret !== null) {
            $this->paypal_client_secret_saved = $paypalClientSecret !== '';
        }

        $this->stripe_secret = '';
        $this->stripe_webhook_secret = '';
        $this->paypal_client_secret = '';
        $this->clear_stripe_secret = false;
        $this->clear_stripe_webhook_secret = false;
        $this->clear_paypal_client_secret = false;

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }

    /**
     * Blank input = leave the existing secret untouched (return null, which
     * EnvFileWriter skips); the "remove" checkbox is the only way to
     * actually blank out a previously saved secret.
     */
    private function resolveSecret(string $input, bool $clear): ?string
    {
        if ($clear) {
            return '';
        }

        $input = trim($input);

        return $input === '' ? null : $input;
    }

    public function isStripeConfigured(): bool
    {
        return filled($this->stripe_key) && $this->stripe_secret_saved;
    }

    public function isPaypalConfigured(): bool
    {
        return filled($this->paypal_client_id) && $this->paypal_client_secret_saved;
    }

    public function paypalWebhookUrl(): string
    {
        return route('yamaha.webhooks.paypal');
    }

    public function stripeWebhookUrl(): string
    {
        return route('yamaha.webhooks.stripe');
    }

    public function isEnvWritable(): bool
    {
        return app(EnvFileWriter::class)->isWritable();
    }
}
