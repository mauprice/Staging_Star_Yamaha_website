<?php

namespace App\Filament\Pages;

use App\Models\ServiceBooking;
use App\Models\Setting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;

class ServiceDashboard extends Page
{
    protected string $view = 'filament.admin.pages.service-dashboard';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static ?string $navigationLabel = 'Service Dashboard';

    protected static ?string $title = 'Service Dashboard';

    protected static ?int $navigationSort = -10;

    public int $totalCount    = 0;
    public int $unreadCount   = 0;
    public int $unrepliedCount = 0;
    public int $todayCount    = 0;

    /** @var string[] */
    public array $notification_emails = [];

    /** @var \Illuminate\Database\Eloquent\Collection */
    public $recentBookings;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['Admin', 'Manager', 'Service Advisor']) ?? false;
    }

    public function mount(): void
    {
        $this->totalCount     = ServiceBooking::count();
        $this->unreadCount    = ServiceBooking::whereNull('read_at')->count();
        $this->unrepliedCount = ServiceBooking::whereNull('replied_at')->count();
        $this->todayCount     = ServiceBooking::whereDate('preferred_date', Carbon::today())->count();
        $this->recentBookings = ServiceBooking::latest()->take(10)->get();

        $this->notification_emails = Setting::getEmailList(
            'service_booking_email',
            env('BOOKING_EMAIL', 'info@staryamaha.com.au')
        );
    }

    public function saveNotificationEmail(): void
    {
        $emails = collect($this->notification_emails)
            ->map(fn ($email) => trim((string) $email))
            ->filter()
            ->unique()
            ->values();

        if ($emails->isEmpty()) {
            Notification::make()
                ->title('Add at least one email address')
                ->danger()
                ->send();
            return;
        }

        $invalid = $emails->reject(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL));

        if ($invalid->isNotEmpty()) {
            Notification::make()
                ->title('Invalid email address: ' . $invalid->join(', '))
                ->danger()
                ->send();
            return;
        }

        $this->notification_emails = $emails->all();
        Setting::set('service_booking_email', $emails->join(', '));

        Notification::make()
            ->title($emails->count() > 1 ? 'Notification emails saved' : 'Notification email saved')
            ->success()
            ->send();
    }
}
