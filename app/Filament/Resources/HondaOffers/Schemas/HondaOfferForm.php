<?php

namespace App\Filament\Resources\HondaOffers\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HondaOfferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Listing Status')
                    ->description('Content below is synced automatically from honda.com.au and will be overwritten on the next sync - only visibility is editable here.')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Show on website')
                            ->helperText('Turn off to hide this offer without deleting it. Re-appears automatically if the sync sees it live again.'),
                    ]),

                Section::make('Synced Content')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')->disabled()->dehydrated(false),
                        TextInput::make('subtitle')->disabled()->dehydrated(false),
                        TextInput::make('price_label')->disabled()->dehydrated(false),
                        TextInput::make('cta_label')->label('CTA Label')->disabled()->dehydrated(false),
                        TextInput::make('cta_url')->label('CTA URL')->disabled()->dehydrated(false)->columnSpanFull(),
                        TextInput::make('source_url')->label('Source Page')->disabled()->dehydrated(false)->columnSpanFull(),
                        Textarea::make('body')->disabled()->dehydrated(false)->rows(4)->columnSpanFull(),
                        TextInput::make('last_scraped_at')->label('Last Synced')->disabled()->dehydrated(false),
                    ]),

            ]);
    }
}
