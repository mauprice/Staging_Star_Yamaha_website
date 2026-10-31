<?php

namespace App\Filament\Resources\SupplierInvoices;

use App\Filament\Resources\SupplierInvoices\Pages\CreateSupplierInvoice;
use App\Filament\Resources\SupplierInvoices\Pages\EditSupplierInvoice;
use App\Filament\Resources\SupplierInvoices\Pages\ListSupplierInvoices;
use App\Filament\Resources\SupplierInvoices\Schemas\SupplierInvoiceForm;
use App\Filament\Resources\SupplierInvoices\Tables\SupplierInvoicesTable;
use App\Models\SupplierInvoice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SupplierInvoiceResource extends Resource
{
    protected static ?string $model = SupplierInvoice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Supplier Invoices';

    protected static ?int $navigationSort = 6;

    public static function getNavigationGroup(): ?string
    {
        return 'Shop';
    }

    public static function form(Schema $schema): Schema
    {
        return SupplierInvoiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SupplierInvoicesTable::configure($table);
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
            'index' => ListSupplierInvoices::route('/'),
            'create' => CreateSupplierInvoice::route('/create'),
            'edit' => EditSupplierInvoice::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['Admin', 'Manager']) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasAnyRole(['Admin', 'Manager']) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->hasAnyRole(['Admin', 'Manager']) ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->hasRole('Admin') ?? false;
    }
}
