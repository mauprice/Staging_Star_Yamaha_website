<?php

namespace App\Filament\Resources\PurchaseOrders\Schemas;

use App\Enums\PurchaseOrderStatus;
use App\Models\Product;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PurchaseOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order Details')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('supplier_id')
                            ->label('Supplier')
                            ->relationship('supplier', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false),
                        Select::make('status')
                            ->options(collect(PurchaseOrderStatus::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()]))
                            ->default(PurchaseOrderStatus::Draft->value)
                            ->required()
                            ->native(false)
                            ->helperText('Normally changed via the Mark as Sent / Receive actions, not edited directly.'),
                        Textarea::make('notes')
                            ->columnSpanFull(),
                    ]),

                Section::make('Lines')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('lines')
                            ->hiddenLabel()
                            ->relationship()
                            ->columns(4)
                            ->disabled(fn (Get $get) => $get('status') !== PurchaseOrderStatus::Draft->value)
                            ->schema([
                                Select::make('product_id')
                                    ->label('Product')
                                    ->options(fn () => Product::query()->orderBy('name')->limit(200)->pluck('name', 'id'))
                                    ->searchable()
                                    ->getSearchResultsUsing(fn (string $search) => Product::query()
                                        ->where('name', 'like', "%{$search}%")
                                        ->orWhere('part_number', 'like', "%{$search}%")
                                        ->limit(50)
                                        ->pluck('name', 'id'))
                                    ->required()
                                    ->native(false)
                                    ->columnSpan(2),
                                TextInput::make('quantity_ordered')
                                    ->label('Qty')
                                    ->numeric()
                                    ->required()
                                    ->default(1),
                                TextInput::make('unit_cost')
                                    ->label('Unit Cost')
                                    ->numeric()
                                    ->prefix('$')
                                    ->required(),
                            ])
                            ->defaultItems(0)
                            ->addActionLabel('Add Line')
                            ->reorderable(false)
                            ->itemLabel(fn (array $state) => $state['product_id'] ? (Product::find($state['product_id'])?->name ?? 'Line') : 'New Line')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
