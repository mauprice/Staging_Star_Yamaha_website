<?php

namespace App\Filament\Resources\PreOwnedListings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PreOwnedListingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Listing Status')
                    ->description('Control whether this listing appears on the website.')
                    ->columns(2)
                    ->schema([
                        Toggle::make('active')
                            ->label('Show on website')
                            ->helperText('Turn off to hide this listing without deleting it.')
                            ->default(true)
                            ->columnSpan(1),
                        TextInput::make('sort_order')
                            ->label('Display order')
                            ->helperText('Lower numbers appear first. Use 0 for newest first.')
                            ->numeric()
                            ->default(0)
                            ->columnSpan(1),
                    ]),

                Section::make('Listing Title & Price')
                    ->description('This is what customers see at the top of the listing.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Listing Title')
                            ->helperText('e.g. 2022 Yamaha MT-07 — Low Kms')
                            ->required()
                            ->columnSpanFull()
                            ->placeholder('2022 Yamaha MT-07'),
                        TextInput::make('price')
                            ->label('Sale Price')
                            ->helperText('Leave blank to show "Contact for pricing".')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('12990'),
                        Select::make('condition')
                            ->label('Condition')
                            ->required()
                            ->options([
                                'Used'        => 'Used',
                                'Demo'        => 'Demo',
                                'Dealer Demo' => 'Dealer Demo',
                            ])
                            ->default('Used'),
                    ]),

                Section::make('Photos')
                    ->description('Upload the main photo first, then any additional gallery shots.')
                    ->schema([
                        FileUpload::make('featured_image')
                            ->label('Main Photo')
                            ->helperText('This is the photo shown on the listing card and at the top of the detail page.')
                            ->image()
                            ->disk('public')
                            ->directory('pre-owned')
                            ->imageResizeTargetWidth(1400)
                            ->imageResizeTargetHeight(1050)
                            ->imageResizeMode('contain')
                            ->imageResizeUpscale(false)
                            ->maxSize(5120)
                            ->columnSpanFull(),
                        FileUpload::make('images')
                            ->label('Additional Photos')
                            ->helperText('Drag thumbnails to reorder — order saves automatically.')
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->appendFiles()
                            ->panelLayout('grid')
                            ->disk('public')
                            ->directory('pre-owned/gallery')
                            ->imageResizeTargetWidth(1400)
                            ->imageResizeTargetHeight(1050)
                            ->imageResizeMode('contain')
                            ->imageResizeUpscale(false)
                            ->maxFiles(20)
                            ->maxSize(5120)
                            ->columnSpanFull(),
                    ]),

                Section::make('Description')
                    ->schema([
                        Textarea::make('description')
                            ->label('Listing Description')
                            ->helperText('Include service history, modifications, reason for sale, and anything else a buyer should know.')
                            ->rows(8)
                            ->columnSpanFull(),
                    ]),

                Section::make('Vehicle Details')
                    ->description('Used for the spec table on the detail page.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('make')
                            ->label('Make')
                            ->required()
                            ->placeholder('Yamaha'),
                        TextInput::make('model')
                            ->label('Model')
                            ->required()
                            ->placeholder('MT-07'),
                        TextInput::make('year')
                            ->label('Year')
                            ->required()
                            ->numeric()
                            ->minValue(1970)
                            ->maxValue(date('Y') + 1)
                            ->placeholder(date('Y')),
                        Select::make('category')
                            ->label('Vehicle Type')
                            ->required()
                            ->options([
                                'Motorcycle' => 'Motorcycle',
                                'Watercraft' => 'Watercraft',
                                'ATV'        => 'ATV',
                                'ROV'        => 'ROV',
                                'Golf Car'   => 'Golf Car',
                                'Other'      => 'Other',
                            ])
                            ->default('Motorcycle'),
                        TextInput::make('kms')
                            ->label('Odometer (km)')
                            ->numeric()
                            ->placeholder('15000'),
                        TextInput::make('colour')
                            ->label('Colour')
                            ->placeholder('Midnight Black'),
                        TextInput::make('rego')
                            ->label('Registration')
                            ->placeholder('123 ABC'),
                    ]),

            ]);
    }
}
