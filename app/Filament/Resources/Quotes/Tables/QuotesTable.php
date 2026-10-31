<?php

namespace App\Filament\Resources\Quotes\Tables;

use App\Models\Quote;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class QuotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withSum('lines', 'line_total'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('quote_number')
                    ->label('Quote #')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->description(fn (Quote $record) => $record->customer_phone),

                TextColumn::make('lines_sum_line_total')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => '$'.number_format((float) $state, 2))
                    ->sortable(),

                IconColumn::make('converted')
                    ->label('Converted')
                    ->state(fn (Quote $record) => $record->isConverted())
                    ->boolean(),

                TextColumn::make('order.order_number')
                    ->label('Order #')
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('converted_at')
                    ->label('Status')
                    ->nullable()
                    ->trueLabel('Converted')
                    ->falseLabel('Open')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('converted_at'),
                        false: fn ($query) => $query->whereNull('converted_at'),
                    ),
            ])
            ->recordActions([
                Action::make('print')
                    ->label('Print')
                    ->icon('heroicon-o-printer')
                    ->url(fn (Quote $record) => route('quotes.print', $record))
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('No quotes yet')
            ->emptyStateDescription('Quotes are saved from the POS terminal.')
            ->emptyStateIcon('heroicon-o-document-currency-dollar');
    }
}
