<?php

namespace App\Filament\Resources\PreOwnedListings;

use App\Filament\Resources\PreOwnedListings\Pages\CreatePreOwnedListing;
use App\Filament\Resources\PreOwnedListings\Pages\EditPreOwnedListing;
use App\Filament\Resources\PreOwnedListings\Pages\ListPreOwnedListings;
use App\Filament\Resources\PreOwnedListings\Schemas\PreOwnedListingForm;
use App\Filament\Resources\PreOwnedListings\Tables\PreOwnedListingsTable;
use App\Models\PreOwnedListing;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PreOwnedListingResource extends Resource
{
    protected static ?string $model = PreOwnedListing::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $navigationLabel = 'Pre-Owned Listings';

    protected static ?string $modelLabel = 'Pre-Owned Listing';

    protected static ?string $pluralModelLabel = 'Pre-Owned Listings';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Website';
    }

    public static function form(Schema $schema): Schema
    {
        return PreOwnedListingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PreOwnedListingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPreOwnedListings::route('/'),
            'create' => CreatePreOwnedListing::route('/create'),
            'edit'   => EditPreOwnedListing::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['Admin', 'Manager', 'Sales']) ?? false;
    }
}
