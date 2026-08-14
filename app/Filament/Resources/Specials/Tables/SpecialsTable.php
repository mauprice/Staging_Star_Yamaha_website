<?php

namespace App\Filament\Resources\Specials\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SpecialsTable
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
                    ->label('Special')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->regular_price
                        ? '$' . number_format($record->regular_price, 0) . ' → $' . number_format($record->special_price ?? $record->regular_price, 0)
                        : null
                    ),

                TextColumn::make('regular_price')
                    ->label('Regular')
                    ->formatStateUsing(fn ($state) => $state ? '$' . number_format($state, 0) : '—')
                    ->sortable(),

                TextColumn::make('special_price')
                    ->label('Special')
                    ->formatStateUsing(fn ($state) => $state ? '$' . number_format($state, 0) : '—')
                    ->sortable()
                    ->color('danger'),

                IconColumn::make('is_active')
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
                    ->modalHeading('Deactivate special?')
                    ->modalDescription('This will hide the special from the website.')
                    ->modalSubmitActionLabel('Deactivate'),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No specials yet')
            ->emptyStateDescription('Add your first special using the button above.')
            ->emptyStateIcon('heroicon-o-star');
    }
}
