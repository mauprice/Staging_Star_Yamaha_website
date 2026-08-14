<?php

namespace App\Filament\Exports;

use App\Models\StockEntry;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class StockEntryExporter extends Exporter
{
    protected static ?string $model = StockEntry::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('status')->label('Status'),
            ExportColumn::make('stock_no')->label('Stock No'),
            ExportColumn::make('description')->label('Description'),
            ExportColumn::make('vin_no')->label('VIN No'),
            ExportColumn::make('rego_no')->label('Rego No'),
            ExportColumn::make('ymaf_unit_no')->label('YMF Unit No'),
            ExportColumn::make('purchase_date')->label('Purchase Date'),
            ExportColumn::make('purchase_price')->label('Purchase Price'),
            ExportColumn::make('price_before_writeback')->label('Price Before Writeback'),
            ExportColumn::make('writeback')->label('Writeback'),
            ExportColumn::make('revs_transfer_fee')->label('Revs Transfer Fee'),
            ExportColumn::make('rego_fee')->label('Rego Fee'),
            ExportColumn::make('stamp_duty')->label('Stamp Duty'),
            ExportColumn::make('workshop_parts')->label('Workshop Parts'),
            ExportColumn::make('workshop_labour')->label('Workshop Labour'),
            ExportColumn::make('transport')->label('Transport'),
            ExportColumn::make('misc')->label('Misc'),
            ExportColumn::make('accessories_purchased')->label('Accessories Purchased'),
            ExportColumn::make('insurance')->label('Insurance'),
            ExportColumn::make('ymaf_rebate')->label('YMF Rebate'),
            ExportColumn::make('gst_paid')->label('GST Paid'),
            ExportColumn::make('gst_collected')->label('GST Collected'),
            ExportColumn::make('gst_payable')->label('GST Payable'),
            ExportColumn::make('trade_details')->label('Trade Details'),
            ExportColumn::make('trade_in_price')->label('Trade In Price'),
            ExportColumn::make('sold_date')->label('Sold Date'),
            ExportColumn::make('sold_to')->label('Sold To'),
            ExportColumn::make('inv_no')->label('Invoice No'),
            ExportColumn::make('salesman')->label('Salesperson'),
            ExportColumn::make('bal_paid')->label('Balance Paid'),
            ExportColumn::make('sold_price')->label('Sold Price'),
            ExportColumn::make('profit')->label('Profit'),
            ExportColumn::make('comm_paid_date')->label('Commission Paid Date'),
            ExportColumn::make('bal_banked')->label('Balance Banked'),
            ExportColumn::make('date_banked')->label('Date Banked'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your stock entry export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
