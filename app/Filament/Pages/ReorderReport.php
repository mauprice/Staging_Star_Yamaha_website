<?php

namespace App\Filament\Pages;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReorderReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.reorder-report';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowTrendingDown;

    protected static ?string $navigationLabel = 'Reorder Report';

    protected static ?int $navigationSort = 8;

    public static function getNavigationGroup(): ?string
    {
        return 'Shop';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['Admin', 'Manager']) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->whereNotNull('min_stock')
                    ->whereColumn('stock_quantity', '<=', 'min_stock')
                    ->with('supplier')
            )
            ->defaultSort('stock_quantity')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn (Product $record) => $record->part_number),

                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->placeholder('No supplier set'),

                TextColumn::make('stock_quantity')
                    ->label('On Hand')
                    ->badge()
                    ->color('danger'),

                TextColumn::make('on_order')
                    ->label('On Order')
                    ->state(fn (Product $record) => $record->onOrderQuantity())
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'info' : 'gray'),

                TextColumn::make('min_stock')
                    ->label('Min'),

                TextColumn::make('max_stock')
                    ->label('Max')
                    ->placeholder('—'),

                TextColumn::make('reorder_qty')
                    ->label('Suggested Qty')
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('supplier_id')
                    ->label('Supplier')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('editProduct')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->url(fn (Product $record) => route('filament.admin.resources.products.edit', $record)),
            ])
            ->headerActions([
                Action::make('newPurchaseOrder')
                    ->label('New Purchase Order')
                    ->icon('heroicon-o-plus')
                    ->url(route('filament.admin.resources.purchase-orders.create')),
            ])
            ->emptyStateHeading('Nothing needs reordering')
            ->emptyStateDescription('Products appear here once on-hand stock falls to or below their minimum reorder level.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
