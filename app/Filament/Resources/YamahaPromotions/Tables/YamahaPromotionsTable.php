<?php

namespace App\Filament\Resources\YamahaPromotions\Tables;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class YamahaPromotionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('brief_image')
                    ->label('')
                    ->square()
                    ->size(56),

                TextColumn::make('head')
                    ->label('Offer')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->type),

                IconColumn::make('active')
                    ->label('Live on Yamaha')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn ($record) => $record->active
                        ? 'Currently returned by the Yamaha API'
                        : 'No longer returned by the Yamaha API — hidden automatically regardless of the toggle below'),

                IconColumn::make('is_visible')
                    ->label('Show on Website')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('success')
                    ->falseColor('gray'),

                TextColumn::make('display_order')
                    ->label('Order')
                    ->sortable(),
            ])
            ->reorderable('display_order')
            ->defaultSort('display_order')
            ->filters([
                TernaryFilter::make('is_visible')->label('Show on website'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('toggleVisible')
                    ->label(fn ($record) => $record->is_visible ? 'Hide' : 'Show')
                    ->icon(fn ($record) => $record->is_visible ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn ($record) => $record->is_visible ? 'warning' : 'success')
                    ->action(fn ($record) => $record->update(['is_visible' => ! $record->is_visible]))
                    ->requiresConfirmation(fn ($record) => $record->is_visible)
                    ->modalHeading('Hide offer?')
                    ->modalDescription('This will remove the offer from the homepage slider, homepage offers section, and the Specials page.')
                    ->modalSubmitActionLabel('Hide'),
            ])
            ->emptyStateHeading('No offers synced yet')
            ->emptyStateDescription('Run `php artisan yamaha:sync` to pull current offers from the Yamaha Dealers API.')
            ->emptyStateIcon('heroicon-o-tag');
    }
}
