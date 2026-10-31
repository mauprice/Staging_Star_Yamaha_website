<?php

namespace App\Filament\Resources\SupplierInvoices\Schemas;

use App\Enums\SupplierInvoiceType;
use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupplierInvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Invoice Details')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('supplier_id')
                            ->label('Supplier')
                            ->relationship('supplier', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false),
                        Select::make('type')
                            ->options(collect(SupplierInvoiceType::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()]))
                            ->default(SupplierInvoiceType::Invoice->value)
                            ->required()
                            ->native(false)
                            ->disabled(fn (string $operation) => $operation === 'edit')
                            ->helperText('Invoice = stock received. Credit = stock returned to the supplier.'),
                        TextInput::make('invoice_number')
                            ->label('Invoice / Credit Note #')
                            ->required()
                            ->maxLength(255),
                        DatePicker::make('invoice_date')
                            ->required()
                            ->default(now())
                            ->native(false),
                        Textarea::make('notes')
                            ->columnSpan(2),
                    ]),

                Section::make('Lines')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('lines')
                            ->hiddenLabel()
                            ->relationship()
                            ->columns(4)
                            ->disabled(fn (string $operation) => $operation === 'edit')
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
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->required()
                                    ->default(1),
                                TextInput::make('unit_cost')
                                    ->label('Unit Cost')
                                    ->numeric()
                                    ->prefix('$')
                                    ->required(),
                            ])
                            ->defaultItems(1)
                            ->addActionLabel('Add Line')
                            ->reorderable(false)
                            ->itemLabel(fn (array $state) => $state['product_id'] ? (Product::find($state['product_id'])?->name ?? 'Line') : 'New Line')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
