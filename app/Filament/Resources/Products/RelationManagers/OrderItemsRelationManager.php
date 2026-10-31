<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Enums\OrderItemFulfillmentStatus;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderItems';

    protected static ?string $title = 'Sales History';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->modifyQueryUsing(fn ($query) => $query->with('order'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('order.order_number')
                    ->label('Order #'),

                TextColumn::make('order.customer_name')
                    ->label('Customer'),

                TextColumn::make('quantity'),

                TextColumn::make('unit_price')
                    ->money('AUD'),

                TextColumn::make('line_total')
                    ->money('AUD'),

                TextColumn::make('fulfillment_status')
                    ->badge()
                    ->formatStateUsing(fn (OrderItemFulfillmentStatus $state) => $state->label())
                    ->color(fn (OrderItemFulfillmentStatus $state) => $state->color()),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([])
            ->emptyStateHeading('No sales yet');
    }
}
