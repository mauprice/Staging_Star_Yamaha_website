<?php

namespace App\Filament\Resources\PurchaseOrders\Tables;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PurchaseOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withSum('lines', 'line_total'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('po_number')
                    ->label('PO #')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('supplier.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (PurchaseOrderStatus $state) => $state->label())
                    ->color(fn (PurchaseOrderStatus $state) => $state->color()),

                TextColumn::make('lines_sum_line_total')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => '$'.number_format((float) $state, 2))
                    ->sortable(),

                TextColumn::make('ordered_at')
                    ->label('Sent')
                    ->dateTime('d/m/Y')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(PurchaseOrderStatus::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])),
            ])
            ->recordActions([
                Action::make('markAsSent')
                    ->label('Mark as Sent')
                    ->icon('heroicon-o-paper-airplane')
                    ->visible(fn (PurchaseOrder $record) => $record->status === PurchaseOrderStatus::Draft)
                    ->requiresConfirmation()
                    ->action(function (PurchaseOrder $record) {
                        $record->update(['status' => PurchaseOrderStatus::Sent, 'ordered_at' => now()]);
                        Notification::make()->title('Marked as sent')->success()->send();
                    }),
                Action::make('receive')
                    ->label('Receive')
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->color('success')
                    ->visible(fn (PurchaseOrder $record) => in_array($record->status, [PurchaseOrderStatus::Sent, PurchaseOrderStatus::PartiallyReceived], true))
                    ->url(fn (PurchaseOrder $record) => route('filament.admin.resources.purchase-orders.receive', $record)),
                Action::make('print')
                    ->label('Print')
                    ->icon('heroicon-o-printer')
                    ->url(fn (PurchaseOrder $record) => route('purchase-orders.print', $record))
                    ->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No purchase orders yet')
            ->emptyStateDescription('Create one to order stock from a supplier.')
            ->emptyStateIcon('heroicon-o-clipboard-document-list');
    }
}
