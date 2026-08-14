<?php

namespace App\Filament\Resources\StockEntries;

use App\Filament\Resources\StockEntries\Pages\CreateStockEntry;
use App\Filament\Resources\StockEntries\Pages\EditStockEntry;
use App\Filament\Resources\StockEntries\Pages\ListStockEntries;
use App\Filament\Resources\StockEntries\Schemas\StockEntryForm;
use App\Filament\Resources\StockEntries\Tables\StockEntriesTable;
use App\Models\StockEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockEntryResource extends Resource
{
    protected static ?string $model = StockEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $navigationLabel = 'Stock Register';

    protected static ?string $modelLabel = 'stock entry';

    protected static ?string $pluralModelLabel = 'Stock Register';

    protected static ?string $recordTitleAttribute = 'stock_no';

    public static function form(Schema $schema): Schema
    {
        return StockEntryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockEntriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockEntries::route('/'),
            'create' => CreateStockEntry::route('/create'),
            'edit' => EditStockEntry::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['stock_no', 'description', 'vin_no', 'rego_no', 'sold_to', 'inv_no', 'salesman'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Stock No' => $record->stock_no,
            'Status' => $record->isSold() ? 'Sold' : 'In Stock',
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) StockEntry::inStock()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['Admin', 'Manager', 'Sales']) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasAnyRole(['Admin', 'Manager', 'Sales']) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->hasAnyRole(['Admin', 'Manager', 'Sales']) ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->hasAnyRole(['Admin', 'Manager']) ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->hasAnyRole(['Admin', 'Manager']) ?? false;
    }

    public static function canForceDelete(Model $record): bool
    {
        return auth()->user()?->hasRole('Admin') ?? false;
    }

    public static function canForceDeleteAny(): bool
    {
        return auth()->user()?->hasRole('Admin') ?? false;
    }

    public static function canRestore(Model $record): bool
    {
        return auth()->user()?->hasAnyRole(['Admin', 'Manager']) ?? false;
    }

    public static function canRestoreAny(): bool
    {
        return auth()->user()?->hasAnyRole(['Admin', 'Manager']) ?? false;
    }
}
