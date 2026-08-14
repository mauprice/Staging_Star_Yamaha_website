<?php

namespace App\Filament\Pages;

use App\Models\ServiceBooking;
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
    }
}
