<?php

namespace App\Filament\Resources\ServiceBookings\Pages;

use App\Filament\Resources\ServiceBookings\ServiceBookingResource;
use Filament\Resources\Pages\ListRecords;

class ListServiceBookings extends ListRecords
{
    protected static string $resource = ServiceBookingResource::class;

    protected string $view = 'filament.resources.service-bookings.list-service-bookings';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
