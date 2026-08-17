<?php

namespace App\Filament\Resources\HondaOffers\Tables;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class HondaOffersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    ->getStateUsing(fn ($record) => $record->image?->url())
                    ->square()
                    ->size(56),

                TextColumn::make('title')
                    ->label('Offer')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->price_label ?? $record->subtitle),

                TextColumn::make('parent.title')
                    ->label('Campaign')
                    ->placeholder('— Top level —')
                    ->badge()
                    ->color('gray'),

                IconColumn::make('is_active')
                    ->label('Live')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                TextColumn::make('last_scraped_at')
                    ->label('Last Synced')
                    ->dateTime('d M Y, g:ia')
                    ->sortable(),

                TextColumn::make('sort')
                    ->label('Order')
                    ->sortable(),
            ])
            ->defaultSort('sort')
            ->filters([
                TernaryFilter::make('is_active')->label('Show on website'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('toggleActive')
                    ->label(fn ($record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn ($record) => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn ($record) => $record->is_active ? 'warning' : 'success')
                    ->action(fn ($record) => $record->update(['is_active' => ! $record->is_active]))
                    ->requiresConfirmation(fn ($record) => $record->is_active)
                    ->modalHeading('Deactivate offer?')
                    ->modalDescription('This will hide the offer from the website until the next sync sees it live again.')
                    ->modalSubmitActionLabel('Deactivate'),
                Action::make('viewSource')
                    ->label('View Source')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record) => $record->source_url)
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('No offers synced yet')
            ->emptyStateDescription('Run `php artisan honda-catalog:sync-offers` to pull current offers from honda.com.au.')
            ->emptyStateIcon('heroicon-o-tag');
    }
}
