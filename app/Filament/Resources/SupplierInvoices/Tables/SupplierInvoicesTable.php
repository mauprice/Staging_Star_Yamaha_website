<?php

namespace App\Filament\Resources\SupplierInvoices\Tables;

use App\Enums\SupplierInvoiceType;
use App\Models\SupplierInvoice;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SupplierInvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withSum('lines', 'line_total'))
            ->defaultSort('invoice_date', 'desc')
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Invoice #')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('supplier.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (SupplierInvoiceType $state) => $state->label())
                    ->color(fn (SupplierInvoiceType $state) => $state->color()),

                TextColumn::make('lines_sum_line_total')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => '$'.number_format((float) $state, 2))
                    ->sortable(),

                TextColumn::make('invoice_date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Entered By')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(collect(SupplierInvoiceType::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])),
                SelectFilter::make('supplier_id')
                    ->label('Supplier')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->modalDescription('This reverses the stock change this document made and deletes it. This cannot be undone.')
                    ->before(fn (SupplierInvoice $record) => $record->reverseStock()),
            ])
            ->emptyStateHeading('No supplier invoices yet')
            ->emptyStateDescription('Enter one when stock arrives with a supplier invoice.')
            ->emptyStateIcon('heroicon-o-document-text');
    }
}
