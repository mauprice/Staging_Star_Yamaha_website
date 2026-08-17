<?php

namespace App\Filament\Resources\HondaOffers;

use App\Filament\Resources\HondaOffers\Pages\EditHondaOffer;
use App\Filament\Resources\HondaOffers\Pages\ListHondaOffers;
use App\Filament\Resources\HondaOffers\Schemas\HondaOfferForm;
use App\Filament\Resources\HondaOffers\Tables\HondaOffersTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Honda\Catalog\Models\HondaOffer;

class HondaOfferResource extends Resource
{
    protected static ?string $model = HondaOffer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $navigationLabel = 'Honda Offers';

    protected static ?string $modelLabel = 'Honda Offer';

    protected static ?string $pluralModelLabel = 'Honda Offers';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return 'Website';
    }

    public static function form(Schema $schema): Schema
    {
        return HondaOfferForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HondaOffersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHondaOffers::route('/'),
            'edit'  => EditHondaOffer::route('/{record}/edit'),
        ];
    }

    // Offers only originate from `honda-catalog:sync-offers` - staff can hide
    // or reorder them here, but there's no manual "create" flow.
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['Admin', 'Manager', 'Sales']) ?? false;
    }
}
