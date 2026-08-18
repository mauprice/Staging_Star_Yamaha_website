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

    public string $notification_email = '';

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

        $this->notification_email = Setting::get(
            'service_booking_email',
            env('BOOKING_EMAIL', 'service@staryamaha.com.au')
        );
    }

    public function saveNotificationEmail(): void
    {
        if (! filter_var($this->notification_email, FILTER_VALIDATE_EMAIL)) {
            Notification::make()
                ->title('Invalid email address')
                ->danger()
                ->send();
            return;
        }

        Setting::set('service_booking_email', $this->notification_email);

        Notification::make()
            ->title('Notification email saved')
            ->success()
            ->send();
    }
}
