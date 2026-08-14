<?php

namespace App\Filament\Widgets;

use App\Models\StockEntry;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Widgets\Widget;

class ProfitDateRange extends Widget
{
    protected string $view = 'filament.widgets.profit-date-range';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public string $activeFilter = 'all';

    public ?string $startDate = null;

    public ?string $endDate = null;

    public function setFilter(string $filter): void
    {
        $this->activeFilter = $filter;
        $this->startDate = null;
        $this->endDate = null;

        $this->dispatchChartUpdate();
    }

    public function updatedStartDate(): void
    {
        $this->activeFilter = 'custom';
        $this->dispatchChartUpdate();
    }

    public function updatedEndDate(): void
    {
        $this->activeFilter = 'custom';
        $this->dispatchChartUpdate();
    }

    private function buildQuery()
    {
        $query = StockEntry::sold();

        if ($this->activeFilter === 'year') {
            $query->whereYear('sold_date', now()->year);
        } elseif ($this->activeFilter === 'month') {
            $query->whereMonth('sold_date', now()->month)
                  ->whereYear('sold_date', now()->year);
        } elseif ($this->activeFilter === 'custom') {
            if ($this->startDate) {
                $query->whereDate('sold_date', '>=', $this->startDate);
            }
            if ($this->endDate) {
                $query->whereDate('sold_date', '<=', $this->endDate);
            }
        }

        return $query;
    }

    public function getProfit(): float
    {
        return (float) $this->buildQuery()->sum('profit');
    }

    public function getUnitCount(): int
    {
        return $this->buildQuery()->count();
    }

    public function getChartData(): array
    {
        [$start, $end, $format] = $this->chartPeriodBounds();

        $months = collect(CarbonPeriod::create($start->copy()->startOfMonth(), '1 month', $end->copy()->startOfMonth()))
            ->map(fn (Carbon $month) => [
                'label' => $month->format($format),
                'value' => (float) StockEntry::sold()
                    ->whereMonth('sold_date', $month->month)
                    ->whereYear('sold_date', $month->year)
                    ->sum('profit'),
            ]);

        return [
            'labels' => $months->pluck('label')->all(),
            'data'   => $months->pluck('value')->all(),
        ];
    }

    private function chartPeriodBounds(): array
    {
        if ($this->activeFilter === 'year') {
            return [
                Carbon::create(now()->year, 1, 1),
                Carbon::create(now()->year, 12, 1),
                'M',
            ];
        }

        if ($this->activeFilter === 'custom' && $this->startDate && $this->endDate) {
            return [
                Carbon::parse($this->startDate),
                Carbon::parse($this->endDate),
                'M y',
            ];
        }

        if ($this->activeFilter === 'custom' && $this->startDate) {
            return [
                Carbon::parse($this->startDate),
                now(),
                'M y',
            ];
        }

        // 'all' and 'month' both default to trailing 12 months
        return [
            now()->subMonths(11),
            now(),
            'M y',
        ];
    }

    private function dispatchChartUpdate(): void
    {
        $data = $this->getChartData();

        $this->dispatch('updateChartData', data: [
            'datasets' => [[
                'label'           => 'Monthly Profit',
                'data'            => $data['data'],
                'fill'            => true,
                'tension'         => 0.4,
                'backgroundColor' => 'rgba(74,222,128,0.12)',
                'borderColor'     => '#4ade80',
            ]],
            'labels' => $data['labels'],
        ]);
    }
}
