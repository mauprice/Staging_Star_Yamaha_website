<?php

namespace App\Filament\Resources\ServiceBookings;

use App\Filament\Resources\ServiceBookings\Pages\EditServiceBooking;
use App\Filament\Resources\ServiceBookings\Pages\ListServiceBookings;
use App\Filament\Resources\ServiceBookings\Schemas\ServiceBookingForm;
use App\Filament\Resources\ServiceBookings\Tables\ServiceBookingsTable;
use App\Models\ServiceBooking;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ServiceBookingResource extends Resource
{
    protected static ?string $model = ServiceBooking::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $navigationLabel = 'Service Bookings';

    protected static ?string $modelLabel = 'Service Booking';

    protected static ?string $pluralModelLabel = 'Service Bookings';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Communications';
    }

    public static function getNavigationBadge(): ?string
    {
        $count = ServiceBooking::whereNull('read_at')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Schema $schema): Schema
    {
        return ServiceBookingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiceBookingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceBookings::route('/'),
            'edit'  => EditServiceBooking::route('/{record}/edit'),
        ];
    }
}
