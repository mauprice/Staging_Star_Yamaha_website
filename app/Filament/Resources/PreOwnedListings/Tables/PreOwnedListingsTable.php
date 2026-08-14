<?php

namespace App\Filament\Resources\PreOwnedListings\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PreOwnedListingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('featured_image')
                    ->label('')
                    ->disk('public')
                    ->visibility('public')
                    ->square()
                    ->size(56),

                TextColumn::make('title')
                    ->label('Listing')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->year . ' · ' . $record->make . ' ' . $record->model),

                TextColumn::make('category')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('condition')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Demo', 'Dealer Demo' => 'warning',
                        default              => 'gray',
                    }),

                TextColumn::make('kms')
                    ->label('Odometer')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state) . ' km' : '—')
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Price')
                    ->formatStateUsing(fn ($state) => $state ? '$' . number_format($state, 0) : 'POA')
                    ->sortable()
                    ->color(fn ($state) => $state ? 'danger' : 'gray'),

                IconColumn::make('active')
                    ->label('Live')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->filters([
                TernaryFilter::make('active')->label('Show on website'),
                SelectFilter::make('category')
                    ->label('Vehicle Type')
                    ->options([
                        'Motorcycle' => 'Motorcycle',
                        'Watercraft' => 'Watercraft',
                        'ATV'        => 'ATV',
                        'ROV'        => 'ROV',
                        'Golf Car'   => 'Golf Car',
                        'Other'      => 'Other',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('toggleActive')
                    ->label(fn ($record) => $record->active ? 'Deactivate' : 'Activate')
                    ->icon(fn ($record) => $record->active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn ($record) => $record->active ? 'warning' : 'success')
                    ->action(fn ($record) => $record->update(['active' => ! $record->active]))
                    ->requiresConfirmation(fn ($record) => $record->active)
                    ->modalHeading('Deactivate listing?')
                    ->modalDescription('This will hide the listing from the website.')
                    ->modalSubmitActionLabel('Deactivate'),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No pre-owned listings yet')
            ->emptyStateDescription('Add your first listing using the button above.')
            ->emptyStateIcon('heroicon-o-tag');
    }
}
