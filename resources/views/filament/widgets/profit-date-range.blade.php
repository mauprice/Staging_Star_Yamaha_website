<x-filament-widgets::widget class="ns-profit-widget">

    <style>
        .ns-profit-widget .ns-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        .ns-profit-widget .ns-stat-label {
            font-size: 0.6rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #6b7280;
            margin-bottom: 0.4rem;
        }
        .ns-profit-widget .ns-stat-value {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.03em;
            color: #fff;
        }
        .ns-profit-widget .ns-stat-value.is-profit { color: #4ade80; }
        .ns-profit-widget .ns-chart-label {
            font-size: 0.6rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #6b7280;
            margin-bottom: 1rem;
        }
        .ns-filter-group {
            display: flex;
            align-items: center;
            gap: 0.125rem;
            background: rgba(255,255,255,0.05);
            border-radius: 0.5rem;
            padding: 0.2rem;
        }
        .ns-filter-group button {
            padding: 0.2rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.8rem;
            font-weight: 500;
            color: #9ca3af;
            transition: background 0.15s, color 0.15s;
            border: none;
            cursor: pointer;
            background: transparent;
        }
        .ns-filter-group button:hover { color: #fff; }
        .ns-filter-group button.is-active {
            background: #16a34a;
            color: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.3);
        }
        .ns-date-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            color: #9ca3af;
        }
        .ns-date-row input[type="date"] {
            background: #111827;
            border: 1px solid #374151;
            border-radius: 0.375rem;
            padding: 0.2rem 0.4rem;
            font-size: 0.8rem;
            color: #d1d5db;
            color-scheme: dark;
        }
        .ns-date-row input[type="date"]:focus {
            outline: none;
            border-color: #16a34a;
        }
    </style>

    <x-filament::section>
        <x-slot name="heading">Profit by Date Range</x-slot>
        <x-slot name="description">Financial performance across the selected period</x-slot>

        <x-slot name="afterHeader">
            <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;">

                <div class="ns-filter-group">
                    @foreach(['all' => 'All time', 'year' => 'This year', 'month' => 'This month'] as $key => $label)
                        <button
                            wire:click="setFilter('{{ $key }}')"
                            @class(['is-active' => $activeFilter === $key])
                        >{{ $label }}</button>
                    @endforeach
                </div>

                <div class="ns-date-row">
                    <span>From</span>
                    <input type="date" wire:model.live="startDate" />
                    <span>To</span>
                    <input type="date" wire:model.live="endDate" />
                </div>

            </div>
        </x-slot>

        @php
            $profit = $this->getProfit();
            $units  = $this->getUnitCount();
            $avg    = $units > 0 ? $profit / $units : 0;
        @endphp

        <div class="ns-stats">
            <div>
                <p class="ns-stat-label">Total Profit</p>
                <p class="ns-stat-value is-profit">${{ number_format($profit, 2) }}</p>
            </div>
            <div>
                <p class="ns-stat-label">Units Sold</p>
                <p class="ns-stat-value">{{ number_format($units) }}</p>
            </div>
            <div>
                <p class="ns-stat-label">Avg. per Unit</p>
                <p class="ns-stat-value">${{ number_format($avg, 2) }}</p>
            </div>
        </div>

        @php $chartData = $this->getChartData(); @endphp

        <p class="ns-chart-label">Monthly Profit &mdash;
            @if($activeFilter === 'year') {{ now()->year }}
            @elseif($activeFilter === 'custom' && ($startDate || $endDate)) Selected Range
            @else Trailing 12 Months
            @endif
        </p>

        <div
            x-load
            x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('chart', 'filament/widgets') }}"
            wire:ignore
            x-data="chart({
                cachedData: @js([
                    'datasets' => [[
                        'label'           => 'Monthly Profit',
                        'data'            => $chartData['data'],
                        'fill'            => true,
                        'tension'         => 0.4,
                        'backgroundColor' => 'rgba(74,222,128,0.12)',
                        'borderColor'     => '#4ade80',
                    ]],
                    'labels' => $chartData['labels'],
                ]),
                options: @js([
                    'backgroundColor' => 'rgba(74,222,128,0.12)',
                    'borderColor'     => '#4ade80',
                    'scales'          => ['y' => ['beginAtZero' => true]],
                    'plugins'         => ['legend' => ['display' => false]],
                    'elements'        => ['line' => ['tension' => 0.4]],
                ]),
                type: 'line',
            })"
            class="fi-wi-chart-canvas-ctn"
        >
            <canvas x-ref="canvas" style="width:100%;max-height:220px;"></canvas>
            <span x-ref="backgroundColorElement" class="fi-wi-chart-bg-color"></span>
            <span x-ref="borderColorElement"     class="fi-wi-chart-border-color"></span>
            <span x-ref="gridColorElement"       class="fi-wi-chart-grid-color"></span>
            <span x-ref="textColorElement"       class="fi-wi-chart-text-color"></span>
        </div>

    </x-filament::section>

</x-filament-widgets::widget>
