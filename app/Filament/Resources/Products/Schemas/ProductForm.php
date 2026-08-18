<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Listing Status')
                    ->description('Control whether this product appears on the website.')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Toggle::make('active')
                            ->label('Show on website')
                            ->helperText('Turn off to hide this product without deleting it.')
                            ->default(true)
                            ->columnSpan(1),
                        TextInput::make('sort_order')
                            ->label('Display order')
                            ->helperText('Lower numbers appear first.')
                            ->numeric()
                            ->default(0)
                            ->columnSpan(1),
                    ]),

                Section::make('Basic Details')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->columnSpanFull(),
                        Select::make('category')
                            ->options(array_combine(Product::CATEGORIES, Product::CATEGORIES))
                            ->required()
                            ->live()
                            ->native(false),
                        TextInput::make('brand')
                            ->placeholder('Alpinestars'),
                        TextInput::make('part_number')
                            ->label('Generic Part Number'),
                        TextInput::make('barcode')
                            ->label('Barcode')
                            ->unique(ignoreRecord: true)
                            ->visible(fn (Get $get) => ! in_array($get('category'), Product::CLOTHING_CATEGORIES, true))
                            ->helperText('Leave blank if this product has size/colour variants below — each variant gets its own barcode.'),
                        Textarea::make('description')
                            ->rows(6)
                            ->columnSpanFull(),
                    ]),

                Section::make('Pricing & Stock')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('price')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        TextInput::make('stock_quantity')
                            ->label('In-Stock Quantity')
                            ->numeric()
                            ->default(0)
                            ->visible(fn (Get $get) => ! in_array($get('category'), Product::CLOTHING_CATEGORIES, true))
                            ->helperText('Not used for clothing/helmets — stock is tracked per size/colour variant below.'),
                    ]),

                Section::make('Shipping')
                    ->description('Used to calculate shipping cost at checkout.')
                    ->columns(4)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('weight_kg')
                            ->label('Weight')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('kg'),
                        TextInput::make('length_mm')
                            ->label('Length')
                            ->numeric()
                            ->suffix('mm'),
                        TextInput::make('width_mm')
                            ->label('Width')
                            ->numeric()
                            ->suffix('mm'),
                        TextInput::make('height_mm')
                            ->label('Height')
                            ->numeric()
                            ->suffix('mm'),
                    ]),

                Section::make('Photos')
                    ->description('Drag & drop or click to upload. Then choose which photo is the hero image shown on the shop listing and product page.')
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('images')
                            ->label('Product Photos')
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->appendFiles()
                            ->panelLayout('grid')
                            ->disk('public')
                            ->directory('shop/products')
                            ->imageResizeTargetWidth(1400)
                            ->imageResizeTargetHeight(1400)
                            ->imageResizeMode('contain')
                            ->imageResizeUpscale(false)
                            ->maxFiles(20)
                            ->maxSize(5120)
                            ->live()
                            ->columnSpanFull(),
                        Select::make('hero_image')
                            ->label('Hero Image')
                            ->helperText('Shown on the shop listing card and at the top of the product page.')
                            ->native(false)
                            ->options(function (Get $get) {
                                return collect($get('images') ?? [])
                                    ->filter(fn ($path) => is_string($path))
                                    ->mapWithKeys(fn ($path) => [$path => basename((string) $path)])
                                    ->all();
                            })
                            ->required(fn (Get $get) => filled($get('images')))
                            ->columnSpanFull(),
                    ]),

                Section::make('Size & Colour Variants')
                    ->description('Add one row per size/colour combination you stock. Each combination is its own stock-tracked unit with its own barcode.')
                    ->visible(fn (Get $get) => in_array($get('category'), Product::CLOTHING_CATEGORIES, true))
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('variants')
                            ->relationship()
                            ->columns(5)
                            ->schema([
                                TextInput::make('size')
                                    ->required()
                                    ->placeholder('M'),
                                TextInput::make('colour')
                                    ->required()
                                    ->placeholder('Black'),
                                TextInput::make('barcode')
                                    ->required()
                                    ->distinct()
                                    ->unique(ignoreRecord: true),
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),
                                TextInput::make('price_override')
                                    ->label('Price Override')
                                    ->numeric()
                                    ->prefix('$')
                                    ->helperText('Blank = base price'),
                            ])
                            ->defaultItems(0)
                            ->addActionLabel('Add Size/Colour Combination')
                            ->reorderable(false)
                            ->collapsible()
                            ->itemLabel(fn (array $state) => trim(($state['size'] ?? '') . ' / ' . ($state['colour'] ?? ''), ' /') ?: 'New Variant')
                            ->columnSpanFull(),
                    ]),

            ]);
    }
}
