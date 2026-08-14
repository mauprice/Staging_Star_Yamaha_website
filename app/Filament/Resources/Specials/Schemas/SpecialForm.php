<?php

namespace App\Filament\Resources\Specials\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SpecialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Listing Status')
                    ->description('Control whether this special appears on the website.')
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Show on website')
                            ->helperText('Turn off to hide this special without deleting it.')
                            ->default(true)
                            ->columnSpan(1),
                        TextInput::make('sort_order')
                            ->label('Display order')
                            ->helperText('Lower numbers appear first. Drag rows in the list to reorder.')
                            ->numeric()
                            ->default(0)
                            ->columnSpan(1),
                    ]),

                Section::make('Title & Pricing')
                    ->description('Set the listing title and pricing to display on the website.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Listing Title')
                            ->required()
                            ->columnSpanFull()
                            ->placeholder('2025 Yamaha MT-07 Clear-out'),
                        TextInput::make('regular_price')
                            ->label('Regular Price')
                            ->helperText('The original/crossed-out price.')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('20299'),
                        TextInput::make('special_price')
                            ->label('Special Price')
                            ->helperText('The discounted price shown in red.')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('17499'),
                    ]),

                Section::make('Photos')
                    ->description('Upload the main photo first, then any additional gallery shots.')
                    ->schema([
                        FileUpload::make('featured_image')
                            ->label('Main Photo')
                            ->helperText('Shown on the listing card and at the top of the detail page.')
                            ->image()
                            ->disk('public')
                            ->directory('specials')
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
                            ->directory('specials/gallery')
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
                            ->helperText('Include details about the offer, conditions, and expiry date if applicable.')
                            ->rows(8)
                            ->columnSpanFull(),
                    ]),

            ]);
    }
}
