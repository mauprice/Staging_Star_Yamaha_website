<?php

namespace App\Filament\Widgets;

use App\Models\StockEntry;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StockOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 2;
    protected function getStats(): array
    {
        $inStock = StockEntry::inStock();
        $soldThisMonth = StockEntry::sold()->whereMonth('sold_date', now()->month)->whereYear('sold_date', now()->year);
        $soldThisYear = StockEntry::sold()->whereYear('sold_date', now()->year);

        $monthlyProfit = collect(range(5, 0))->map(function (int $monthsAgo) {
            $month = now()->subMonths($monthsAgo);

            return (float) StockEntry::sold()
                ->whereMonth('sold_date', $month->month)
                ->whereYear('sold_date', $month->year)
                ->sum('profit');
        });

        return [
            Stat::make('Units In Stock', $inStock->count())
                ->description('Current inventory: $'.number_format($inStock->sum('purchase_price'), 2).' invested')
                ->color('warning'),

            Stat::make('Sold This Month', $soldThisMonth->count())
                ->description('$'.number_format($soldThisMonth->sum('profit'), 2).' profit this month')
                ->chart($monthlyProfit->all())
                ->color('success'),

            Stat::make('Sold This Year', $soldThisYear->count())
                ->description('$'.number_format($soldThisYear->sum('profit'), 2).' profit this year')
                ->color('success'),

        ];
    }
}
