<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\PaymentAvailability;
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
    }

    public function save(): void
    {
        Setting::set('bank_deposit_bank_name', trim($this->bank_deposit_bank_name));
        Setting::set('bank_deposit_account_name', trim($this->bank_deposit_account_name));
        Setting::set('bank_deposit_bsb', trim($this->bank_deposit_bsb));
        Setting::set('bank_deposit_account_number', trim($this->bank_deposit_account_number));

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }

    public function getGatewayStatus(): array
    {
        $availability = app(PaymentAvailability::class);

        return [
            'stripe' => $availability->stripeConfigured(),
            'paypal' => $availability->paypalConfigured(),
        ];
    }
}
