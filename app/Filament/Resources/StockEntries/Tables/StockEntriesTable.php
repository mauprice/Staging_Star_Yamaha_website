<?php

namespace App\Filament\Resources\StockEntries\Tables;

use App\Filament\Exports\StockEntryExporter;
use App\Models\StockEntry;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->striped()
            ->columnToggleFormColumns(4)
            ->columnToggleFormWidth(Width::ThreeExtraLarge)
            ->filtersFormColumns(2)
            ->columns([
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'sold' ? 'Sold' : 'In Stock')
                    ->color(fn (string $state): string => $state === 'sold' ? 'success' : 'warning')
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderByRaw('sold_date IS NULL ' . ($direction === 'asc' ? 'DESC' : 'ASC'))->orderBy('sold_date', $direction)),
                TextColumn::make('stock_no')
                    ->label('Stock No')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('purchase_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('purchase_price')
                    ->money('aud')
                    ->sortable()
                    ->summarize(Sum::make()->money('aud')),
                TextColumn::make('sold_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('sold_to')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sold_price')
                    ->money('aud')
                    ->sortable()
                    ->summarize(Sum::make()->money('aud')),
                TextColumn::make('profit')
                    ->money('aud')
                    ->sortable()
                    ->weight('bold')
                    ->color(fn ($state): string => (float) $state < 0 ? 'danger' : 'success')
                    ->summarize(Sum::make()->money('aud')),
                TextColumn::make('salesman')
                    ->label('Salesperson')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('new_or_used')
                    ->label('New/Used')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'NEW' => 'success',
                        'USED' => 'warning',
                        'DEMO' => 'info',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('commission')
                    ->label('Commission')
                    ->money('aud')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('vin_no')
                    ->label('VIN No')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('rego_no')
                    ->label('Rego No')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ymaf_unit_no')
                    ->label('YMF Unit No')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('inv_no')
                    ->label('Invoice No')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('price_before_writeback')
                    ->label('Price Before Writeback')
                    ->money('aud')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('writeback')
                    ->money('aud')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('revs_transfer_fee')
                    ->label('Revs/Transfer Fee')
                    ->money('aud')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('rego_fee')
                    ->label('Rego Fee')
                    ->money('aud')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('stamp_duty')
                    ->money('aud')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('workshop_parts')
                    ->label('Workshop Parts')
                    ->money('aud')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('workshop_labour')
                    ->label('Workshop Labour')
                    ->money('aud')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('transport')
                    ->money('aud')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('misc')
                    ->money('aud')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('accessories_purchased')
                    ->label('Accessories Purchased')
                    ->money('aud')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('insurance')
                    ->money('aud')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ymaf_rebate')
                    ->label('YMF Rebate')
                    ->money('aud')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('gst_paid')
                    ->label('GST Paid')
                    ->money('aud')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('gst_collected')
                    ->label('GST Collected')
                    ->money('aud')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('gst_payable')
                    ->label('GST Payable')
                    ->money('aud')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('trade_details')
                    ->label('Trade Details')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('trade_in_price')
                    ->label('Trade-In Price')
                    ->money('aud')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('bal_paid')
                    ->label('Balance Paid')
                    ->money('aud')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('comm_paid_date')
                    ->label('Commission Paid')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('bal_banked')
                    ->label('Balance Banked')
                    ->money('aud')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('date_banked')
                    ->label('Date Banked')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('status')
                    ->schema([
                        Select::make('value')
                            ->label('Status')
                            ->options([
                                'in_stock' => 'In Stock',
                                'sold' => 'Sold',
                            ]),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => match ($data['value'] ?? null) {
                        'sold' => $query->whereNotNull('sold_date'),
                        'in_stock' => $query->whereNull('sold_date'),
                        default => $query,
                    })
                    ->indicateUsing(fn (array $data): ?string => match ($data['value'] ?? null) {
                        'sold' => 'Sold',
                        'in_stock' => 'In Stock',
                        default => null,
                    }),
                SelectFilter::make('salesman')
                    ->label('Salesperson')
                    ->options(fn () => StockEntry::query()
                        ->whereNotNull('salesman')
                        ->distinct()
                        ->orderBy('salesman')
                        ->pluck('salesman', 'salesman')
                        ->all()
                    )
                    ->searchable(),
                Filter::make('purchase_date')
                    ->schema([
                        DatePicker::make('from')->native(false),
                        DatePicker::make('until')->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('purchase_date', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('purchase_date', '<=', $date));
                    }),
                Filter::make('sold_date')
                    ->schema([
                        DatePicker::make('from')->native(false),
                        DatePicker::make('until')->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('sold_date', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('sold_date', '<=', $date));
                    }),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(StockEntryExporter::class)
                    ->columnMappingColumns(4)
                    ->modalWidth(Width::FourExtraLarge),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->exporter(StockEntryExporter::class)
                        ->columnMappingColumns(4)
                        ->modalWidth(Width::FourExtraLarge),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
