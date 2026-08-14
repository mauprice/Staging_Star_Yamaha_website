<?php

namespace App\Filament\Pages;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected ?string $subheading = 'Overview of inventory and sales performance';

    public function mount(): void
    {
        if (auth()->user()?->hasRole('Service Advisor')) {
            $this->redirect('/admin/service-dashboard');
        }
    }
}
