<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Http\Controllers\CheckoutController;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('shippingAddress'))
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->description(fn ($record) => $record->customer_email),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => $state->color()),

                TextColumn::make('payment_method')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn ($state) => $state?->label() ?? '—'),

                TextColumn::make('total')
                    ->money('AUD')
                    ->sortable(),

                TextColumn::make('shippingAddress.state')
                    ->label('State')
                    ->placeholder('—'),

                TextColumn::make('shippingAddress.postcode')
                    ->label('Postcode')
                    ->placeholder('—'),

                TextColumn::make('placed_at')
                    ->dateTime('d M Y, g:ia')
                    ->sortable(),

                TextColumn::make('paid_at')
                    ->dateTime('d M Y, g:ia')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->defaultSort('placed_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(OrderStatus::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])),
                SelectFilter::make('payment_method')
                    ->options(collect(PaymentMethod::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])),
                SelectFilter::make('shippingAddress.state')
                    ->label('State')
                    ->options(array_combine(CheckoutController::AU_STATES, CheckoutController::AU_STATES))
                    ->query(fn ($query, $data) => $query->when(
                        $data['value'] ?? null,
                        fn ($q, $state) => $q->whereHas('shippingAddress', fn ($q) => $q->where('state', $state)),
                    )),
            ])
            ->recordActions([
                Action::make('markDepositReceived')
                    ->label('Mark Deposit Received')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === OrderStatus::AwaitingBankDeposit)
                    ->requiresConfirmation()
                    ->modalHeading('Confirm bank deposit received?')
                    ->modalDescription('This marks the order as paid and clears it for shipping. Only confirm after verifying the deposit in your bank account.')
                    ->action(function ($record) {
                        $record->update(['status' => OrderStatus::Paid, 'paid_at' => now()]);

                        $record->payments()
                            ->where('provider', 'bank_transfer')
                            ->where('status', PaymentStatus::Pending)
                            ->update(['status' => PaymentStatus::Succeeded, 'paid_at' => now()]);

                        Notification::make()
                            ->title('Order marked as paid')
                            ->success()
                            ->send();
                    }),
                ViewAction::make(),
            ])
            ->emptyStateHeading('No orders yet')
            ->emptyStateDescription('Orders placed through checkout will appear here.')
            ->emptyStateIcon('heroicon-o-shopping-cart');
    }
}
