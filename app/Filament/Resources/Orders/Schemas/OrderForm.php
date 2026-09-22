<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order Status')
                    ->description('Orders are created by the checkout flow — this only lets staff override status and record internal notes. Marking status as Paid, Shipped, or Completed emails the customer automatically.')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('status')
                            ->options(collect(OrderStatus::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()]))
                            ->required()
                            ->native(false),
                        Placeholder::make('customer_notes_display')
                            ->label('Customer Notes')
                            ->visible(fn (Get $get) => filled($get('customer_notes')))
                            ->content(fn (Get $get) => $get('customer_notes')),
                        Textarea::make('notes')
                            ->label('Internal Notes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
