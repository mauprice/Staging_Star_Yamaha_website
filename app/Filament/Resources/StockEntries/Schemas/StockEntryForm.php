<?php

namespace App\Filament\Resources\StockEntries\Schemas;

use App\Models\StockEntry;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;

class StockEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        $recalculate = self::recalculate();

        return $schema
            ->columns(1)
            ->components([
            Section::make('Vehicle')
                ->description('Identification details for this unit.')
                ->columns(3)
                ->schema([
                    TextInput::make('stock_no')
                        ->label('Stock No')
                        ->required()
                        ->maxLength(50),
                    TextInput::make('description')
                        ->label('Description')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(2),
                    TextInput::make('vin_no')
                        ->label('VIN No')
                        ->maxLength(100),
                    TextInput::make('rego_no')
                        ->label('Rego No')
                        ->maxLength(50),
                    TextInput::make('ymaf_unit_no')
                        ->label('YMF Unit No')
                        ->maxLength(50),
                ]),

            Section::make('Purchase')
                ->columns(3)
                ->schema([
                    DatePicker::make('purchase_date')
                        ->required()
                        ->default(now())
                        ->native(false),
                    TextInput::make('purchase_price')
                        ->label('Purchase Price')
                        ->numeric()
                        ->minValue(0)
                        ->prefix('$')
                        ->default(0)
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated($recalculate),
                    TextInput::make('price_before_writeback')
                        ->label('Price Before Writeback')
                        ->numeric()
                        ->prefix('$')
                        ->default(0),
                ]),

            Section::make('Acquisition Costs')
                ->description('Everything it cost to get this unit ready for sale.')
                ->collapsible()
                ->columns(['default' => 2, 'md' => 3, 'xl' => 4])
                ->schema([
                    TextInput::make('revs_transfer_fee')
                        ->label('Revs / Transfer Fee')
                        ->numeric()
                        ->prefix('$')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated($recalculate),
                    TextInput::make('rego_fee')
                        ->label('Rego')
                        ->numeric()
                        ->prefix('$')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated($recalculate),
                    TextInput::make('stamp_duty')
                        ->label('Stamp Duty')
                        ->numeric()
                        ->prefix('$')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated($recalculate),
                    TextInput::make('workshop_parts')
                        ->label('Workshop Parts')
                        ->numeric()
                        ->prefix('$')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated($recalculate),
                    TextInput::make('workshop_labour')
                        ->label('Workshop Labour')
                        ->numeric()
                        ->prefix('$')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated($recalculate),
                    TextInput::make('transport')
                        ->numeric()
                        ->prefix('$')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated($recalculate),
                    TextInput::make('misc')
                        ->label('Misc')
                        ->numeric()
                        ->prefix('$')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated($recalculate),
                    TextInput::make('writeback')
                        ->label('Write Back')
                        ->numeric()
                        ->prefix('$')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated($recalculate),
                    TextInput::make('accessories_purchased')
                        ->label('Accessories Purchased')
                        ->numeric()
                        ->prefix('$')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated($recalculate),
                    TextInput::make('insurance')
                        ->numeric()
                        ->prefix('$')
                        ->default(0),
                    TextInput::make('ymaf_rebate')
                        ->label('YMF Rebate')
                        ->numeric()
                        ->prefix('$')
                        ->default(0),
                ]),

            Section::make('GST')
                ->columns(3)
                ->schema([
                    TextInput::make('gst_collected')
                        ->label('GST Collected')
                        ->numeric()
                        ->prefix('$')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated($recalculate),
                    TextInput::make('gst_paid')
                        ->label('GST Paid')
                        ->helperText('Auto: purchase price ÷ 11')
                        ->numeric()
                        ->prefix('$')
                        ->disabled()
                        ->dehydrated(),
                    TextInput::make('gst_payable')
                        ->label('GST Payable')
                        ->helperText('Auto: GST collected − GST paid')
                        ->numeric()
                        ->prefix('$')
                        ->disabled()
                        ->dehydrated(),
                ]),

            Section::make('Trade-In')
                ->columns(2)
                ->schema([
                    TextInput::make('trade_details')
                        ->label('Trade Details')
                        ->maxLength(255),
                    TextInput::make('trade_in_price')
                        ->label('Trade-In Price')
                        ->numeric()
                        ->prefix('$')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated($recalculate),
                ]),

            Section::make('Sale')
                ->columns(3)
                ->schema([
                    DatePicker::make('sold_date')
                        ->native(false),
                    TextInput::make('sold_to')
                        ->label('Sold To')
                        ->maxLength(255),
                    TextInput::make('inv_no')
                        ->label('Invoice No')
                        ->maxLength(50),
                    TextInput::make('salesman')
                        ->label('Salesperson')
                        ->maxLength(50)
                        ->datalist(fn () => StockEntry::query()
                            ->whereNotNull('salesman')
                            ->distinct()
                            ->orderBy('salesman')
                            ->pluck('salesman')
                        ),
                    Select::make('new_or_used')
                        ->label('New / Used')
                        ->options([
                            'NEW' => 'New',
                            'USED' => 'Used',
                            'DEMO' => 'Demo',
                            'SKI' => 'Ski',
                            'SKI+TRAILER' => 'Ski + Trailer',
                            'BUY' => 'Buy',
                        ])
                        ->searchable()
                        ->native(false),
                    TextInput::make('bal_paid')
                        ->label('Balance Paid')
                        ->numeric()
                        ->prefix('$')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated($recalculate),
                    TextInput::make('sold_price')
                        ->label('Sold Price')
                        ->helperText('Auto: balance paid + trade-in price')
                        ->numeric()
                        ->prefix('$')
                        ->disabled()
                        ->dehydrated(),
                    DatePicker::make('comm_paid_date')
                        ->label('Commission Paid Date')
                        ->native(false),
                    TextInput::make('commission')
                        ->label('Salesperson Commission')
                        ->rule('numeric')
                        ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 2, '.', '') : '')
                        ->dehydrateStateUsing(fn ($state) => $state !== null && $state !== '' ? (float) $state : null)
                        ->prefix('$')
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Get $get, Set $set, $state) {
                            $profit = (float) ($get('profit') ?? 0);
                            if ($state === null || $state === '' || trim((string) $state) === '') {
                                $set('commission', number_format($profit / 11, 2, '.', ''));
                            }
                        })
                        ->suffixAction(
                            Action::make('calc_commission')
                                ->label('Reset to 10%')
                                ->icon(Heroicon::OutlinedCalculator)
                                ->action(function (Get $get, Set $set) {
                                    $profit = (float) ($get('profit') ?? 0);
                                    $set('commission', number_format($profit / 11, 2, '.', ''));
                                })
                        ),
                    Placeholder::make('profit_display')
                        ->label('Gross Profit')
                        ->content(function (Get $get) {
                            $profit = (float) ($get('profit') ?? 0);
                            $formatted = '$'.number_format($profit, 2);
                            $color = $profit < 0 ? '#dc2626' : '#16a34a';

                            return new HtmlString(
                                '<span style="font-size: 1.125rem; font-weight: 700; color: '.$color.'">'.$formatted.'</span>'
                            );
                        }),
                    Placeholder::make('net_profit_display')
                        ->label('Net Profit')
                        ->content(function (Get $get) {
                            $profit     = (float) ($get('profit') ?? 0);
                            $rawComm    = $get('commission');
                            $commission = ($rawComm !== null && $rawComm !== '')
                                ? (float) $rawComm
                                : round($profit / 11, 2);
                            $net       = $profit - $commission;
                            $formatted = '$'.number_format($net, 2);
                            $color     = $net < 0 ? '#dc2626' : '#16a34a';

                            return new HtmlString(
                                '<span style="font-size: 1.125rem; font-weight: 700; color: '.$color.'">'.$formatted.'</span>'
                            );
                        }),
                    Hidden::make('profit')
                        ->dehydrated()
                        ->afterStateHydrated(function (Set $set, $state) {
                            $set('commission', number_format((float) ($state ?? 0) / 11, 2, '.', ''));
                        }),
                ]),

            Section::make('Banking')
                ->columns(2)
                ->schema([
                    TextInput::make('bal_banked')
                        ->label('Balance Banked')
                        ->numeric()
                        ->prefix('$')
                        ->default(0),
                    DatePicker::make('date_banked')
                        ->label('Date Banked')
                        ->native(false),
                ]),
        ]);
    }

    /**
     * Mirrors StockEntry::booted()'s saving-hook formulas so the form preview
     * matches what will actually be persisted, without waiting for a save
     * round-trip — same UX the legacy jQuery cost()/sold() helpers gave.
     */
    private static function recalculate(): Closure
    {
        return function (Get $get, Set $set) {
            $purchasePrice = (float) ($get('purchase_price') ?? 0);
            $gstCollected = (float) ($get('gst_collected') ?? 0);
            $balPaid = (float) ($get('bal_paid') ?? 0);
            $tradeInPrice = (float) ($get('trade_in_price') ?? 0);

            $gstPaid = round($purchasePrice / 11, 2);
            $gstPayable = round($gstCollected - $gstPaid, 2);
            $soldPrice = round($balPaid + $tradeInPrice, 2);

            $cost = $purchasePrice
                + (float) ($get('rego_fee') ?? 0)
                + (float) ($get('revs_transfer_fee') ?? 0)
                + (float) ($get('stamp_duty') ?? 0)
                + (float) ($get('workshop_parts') ?? 0)
                + (float) ($get('workshop_labour') ?? 0)
                + (float) ($get('transport') ?? 0)
                + (float) ($get('misc') ?? 0)
                + (float) ($get('writeback') ?? 0)
                + (float) ($get('accessories_purchased') ?? 0)
                + $gstPayable;

            $profit = round($soldPrice - $cost, 2);

            $set('gst_paid', $gstPaid);
            $set('gst_payable', $gstPayable);
            $set('sold_price', $soldPrice);
            $set('profit', $profit);
            $set('commission', number_format($profit / 11, 2, '.', ''));
        };
    }
}
