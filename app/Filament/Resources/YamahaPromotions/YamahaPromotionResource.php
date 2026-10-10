<?php

namespace App\Filament\Resources\YamahaPromotions;

use App\Filament\Resources\YamahaPromotions\Pages\EditYamahaPromotion;
use App\Filament\Resources\YamahaPromotions\Pages\ListYamahaPromotions;
use App\Filament\Resources\YamahaPromotions\Schemas\YamahaPromotionForm;
use App\Filament\Resources\YamahaPromotions\Tables\YamahaPromotionsTable;
use App\Models\YamahaPromotion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class YamahaPromotionResource extends Resource
{
    protected static ?string $model = YamahaPromotion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $navigationLabel = 'Offers & Promotions';

    protected static ?string $modelLabel = 'Offer';

    protected static ?string $pluralModelLabel = 'Offers & Promotions';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return 'Website';
    }

    public static function form(Schema $schema): Schema
    {
        return YamahaPromotionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return YamahaPromotionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListYamahaPromotions::route('/'),
            'edit'  => EditYamahaPromotion::route('/{record}/edit'),
        ];
    }

    // Offers only originate from `yamaha:sync` - staff can hide or reorder
    // them here, but there's no manual "create" flow.
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['Admin', 'Manager', 'Sales']) ?? false;
    }
}
