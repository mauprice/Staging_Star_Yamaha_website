<?php

namespace App\Filament\Resources\ServiceBookings\Tables;

use App\Models\ServiceBooking;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class ServiceBookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('read_at')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state, ServiceBooking $record) => match(true) {
                        $record->hasBeenReplied() => 'Replied',
                        $state !== null            => 'Read',
                        default                    => 'New',
                    })
                    ->color(fn ($state, ServiceBooking $record) => match(true) {
                        $record->hasBeenReplied() => 'success',
                        $state !== null            => 'gray',
                        default                    => 'danger',
                    })
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Customer')
                    ->weight('bold')
                    ->description(fn (ServiceBooking $record) => $record->email)
                    ->searchable(),

                TextColumn::make('phone')
                    ->searchable(),

                TextColumn::make('vehicle_model')
                    ->label('Vehicle')
                    ->description(fn (ServiceBooking $record) => $record->vehicle_year)
                    ->searchable(),

                TextColumn::make('service_type')
                    ->label('Service')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                TextColumn::make('preferred_date')
                    ->label('Requested Date')
                    ->date('d M Y')
                    ->description(fn (ServiceBooking $record) => $record->preferred_time)
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Received')
                    ->dateTime('d M Y, g:ia')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordClasses(fn (ServiceBooking $record) => $record->isUnread() ? 'font-semibold' : 'opacity-75')
            ->filters([
                Filter::make('unread')
                    ->label('Unread only')
                    ->query(fn (Builder $query) => $query->whereNull('read_at'))
                    ->default(),
            ])
            ->recordActions([
                Action::make('markRead')
                    ->label('Mark as Read')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (ServiceBooking $record) => $record->isUnread())
                    ->action(fn (ServiceBooking $record) => $record->update(['read_at' => Carbon::now()])),
                EditAction::make()->label('View'),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('markAllRead')
                        ->label('Mark as Read')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn (Collection $records) => $records->each(
                            fn (ServiceBooking $r) => $r->read_at ?? $r->update(['read_at' => Carbon::now()])
                        ))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No service bookings yet')
            ->emptyStateDescription('Booking requests submitted on the website will appear here.')
            ->emptyStateIcon('heroicon-o-envelope');
    }
}
