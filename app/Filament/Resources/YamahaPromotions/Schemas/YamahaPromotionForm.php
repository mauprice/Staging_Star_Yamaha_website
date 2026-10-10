<?php

namespace App\Filament\Resources\YamahaPromotions\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class YamahaPromotionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Listing Status')
                    ->description('Controls how this offer appears on the website — independent of the Yamaha API sync, so these survive the next `yamaha:sync` run.')
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_visible')
                            ->label('Show on website')
                            ->helperText('Turn off to hide this offer from the homepage slider, homepage offers section, and Specials page.')
                            ->columnSpan(1),
                        TextInput::make('display_order')
                            ->label('Display order')
                            ->helperText('Lower numbers appear first, in the slider and in the offers grid. Drag rows in the list to reorder.')
                            ->numeric()
                            ->columnSpan(1),
                    ]),

                Section::make('Synced Content')
                    ->description('Content below is synced automatically from the Yamaha Dealers API and will be overwritten on the next sync — only the status above is editable here.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('head')->label('Title')->disabled()->dehydrated(false),
                        TextInput::make('type')->disabled()->dehydrated(false),
                        TextInput::make('country')->disabled()->dehydrated(false),
                        TextInput::make('full_content_url')->label('Offer URL')->disabled()->dehydrated(false)->columnSpanFull(),
                        Textarea::make('brief')->disabled()->dehydrated(false)->rows(3)->columnSpanFull(),
                    ]),

            ]);
    }
}
