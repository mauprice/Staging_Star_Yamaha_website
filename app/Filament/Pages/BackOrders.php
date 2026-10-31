<?php

namespace App\Filament\Pages;

use App\Enums\OrderItemFulfillmentStatus;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class BackOrders extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.back-orders';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $navigationLabel = 'Back Orders';

    protected static ?int $navigationSort = 7;

    public static function getNavigationGroup(): ?string
    {
        return 'Shop';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['Admin', 'Manager', 'Sales']) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(OrderItem::query()->where('fulfillment_status', OrderItemFulfillmentStatus::BackOrdered)->with('order.customer'))
            ->defaultSort('created_at')
            ->columns([
                TextColumn::make('order.order_number')
                    ->label('Order #')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('order.customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->description(fn (OrderItem $record) => $record->order->customer_phone),

                TextColumn::make('product_name')
                    ->label('Product')
                    ->searchable()
                    ->description(fn (OrderItem $record) => $record->part_number),

                TextColumn::make('quantity'),

                TextColumn::make('expected_date')
                    ->label('Expected')
                    ->date('d/m/Y')
                    ->placeholder('Not set')
                    ->sortable(),

                TextColumn::make('fulfillment_notes')
                    ->label('Notes')
                    ->limit(40)
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Back-ordered Since')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('setExpected')
                    ->label('Expected Date')
                    ->icon('heroicon-o-calendar')
                    ->fillForm(fn (OrderItem $record) => [
                        'expected_date' => $record->expected_date,
                        'fulfillment_notes' => $record->fulfillment_notes,
                    ])
                    ->schema([
                        DatePicker::make('expected_date')->native(false),
                        Textarea::make('fulfillment_notes')->label('Notes'),
                    ])
                    ->action(function (OrderItem $record, array $data) {
                        $record->update($data);
                        Notification::make()->title('Updated')->success()->send();
                    }),
                Action::make('pickingSlip')
                    ->label('Picking Slip')
                    ->icon('heroicon-o-printer')
                    ->url(fn (OrderItem $record) => route('orders.picking-slip', $record->order_id))
                    ->openUrlInNewTab(),
                Action::make('markFulfilled')
                    ->label('Mark Fulfilled')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('Confirms this item has arrived and is being given/shipped to the customer. Decrements stock.')
                    ->action(function (OrderItem $record) {
                        if ($record->product_variant_id) {
                            ProductVariant::where('id', $record->product_variant_id)
                                ->update(['quantity' => max(0, $record->productVariant->quantity - $record->quantity)]);
                        } elseif ($record->product_id) {
                            Product::where('id', $record->product_id)
                                ->update(['stock_quantity' => max(0, $record->product->stock_quantity - $record->quantity)]);
                        }

                        $record->update([
                            'fulfillment_status' => OrderItemFulfillmentStatus::Fulfilled,
                            'fulfilled_at' => now(),
                        ]);

                        Notification::make()->title('Marked fulfilled')->success()->send();
                    }),
            ])
            ->emptyStateHeading('No back orders')
            ->emptyStateDescription('Items get here automatically when a POS sale exceeds available stock.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
