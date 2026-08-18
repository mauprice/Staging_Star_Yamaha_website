<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
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

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('heroImage'))
            ->columns([
                ImageColumn::make('heroImage.path')
                    ->label('')
                    ->disk('public')
                    ->visibility('public')
                    ->square()
                    ->size(56),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->brand),

                TextColumn::make('category')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('price')
                    ->formatStateUsing(fn ($state) => '$' . number_format($state, 2))
                    ->sortable(),

                TextColumn::make('total_stock')
                    ->label('Stock')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),

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
                    ->options(array_combine(Product::CATEGORIES, Product::CATEGORIES)),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('toggleActive')
                    ->label(fn ($record) => $record->active ? 'Deactivate' : 'Activate')
                    ->icon(fn ($record) => $record->active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn ($record) => $record->active ? 'warning' : 'success')
                    ->action(fn ($record) => $record->update(['active' => ! $record->active]))
                    ->requiresConfirmation(fn ($record) => $record->active)
                    ->modalHeading('Deactivate product?')
                    ->modalDescription('This will hide the product from the shop.')
                    ->modalSubmitActionLabel('Deactivate'),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No products yet')
            ->emptyStateDescription('Add your first product using the button above.')
            ->emptyStateIcon('heroicon-o-shopping-bag');
    }
}
