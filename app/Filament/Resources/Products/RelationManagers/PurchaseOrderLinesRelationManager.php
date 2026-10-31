<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Enums\PurchaseOrderStatus;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PurchaseOrderLinesRelationManager extends RelationManager
{
    protected static string $relationship = 'purchaseOrderLines';

    protected static ?string $title = 'Purchase History';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->modifyQueryUsing(fn ($query) => $query->with('purchaseOrder.supplier'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('purchaseOrder.po_number')
                    ->label('PO #'),

                TextColumn::make('purchaseOrder.supplier.name')
                    ->label('Supplier'),

                TextColumn::make('purchaseOrder.status')
                    ->badge()
                    ->formatStateUsing(fn (PurchaseOrderStatus $state) => $state->label())
                    ->color(fn (PurchaseOrderStatus $state) => $state->color()),

                TextColumn::make('quantity_ordered')
                    ->label('Ordered'),

                TextColumn::make('quantity_received')
                    ->label('Received'),

                TextColumn::make('unit_cost')
                    ->money('AUD'),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([])
            ->emptyStateHeading('No purchase order history yet');
    }
}
