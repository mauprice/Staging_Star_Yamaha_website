<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order')
                    ->columns(4)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('order_number')->label('Order #'),
                        TextEntry::make('status')->badge()->color(fn ($state) => $state->color()),
                        TextEntry::make('payment_method')->badge()->formatStateUsing(fn ($state) => $state?->label() ?? '—'),
                        TextEntry::make('total')->money('AUD'),
                    ]),

                Section::make('Customer')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('customer_name')->label('Name'),
                        TextEntry::make('customer_email')->label('Email'),
                        TextEntry::make('customer_phone')->label('Phone')->placeholder('—'),
                        TextEntry::make('user.email')->label('Linked Account')->placeholder('Guest (no matching account)'),
                        TextEntry::make('placed_as_guest')->label('Placed As')->formatStateUsing(fn ($state) => $state ? 'Guest' : 'Signed-in Customer'),
                    ]),

                Section::make('Items')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('items')
                            ->hiddenLabel()
                            ->columns(4)
                            ->schema([
                                TextEntry::make('product_name')->label('Product')->columnSpan(2),
                                TextEntry::make('quantity'),
                                TextEntry::make('line_total')->money('AUD'),
                            ]),
                    ]),

                Section::make('Shipping Address')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('shippingAddress.line1')->label('Address'),
                        TextEntry::make('shippingAddress.suburb')->label('Suburb'),
                        TextEntry::make('shippingAddress.state')->label('State'),
                        TextEntry::make('shippingAddress.postcode')->label('Postcode'),
                        TextEntry::make('shippingAddress.country')->label('Country'),
                    ]),

                Section::make('Payment Log')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('payments')
                            ->hiddenLabel()
                            ->columns(4)
                            ->schema([
                                TextEntry::make('provider'),
                                TextEntry::make('provider_reference')->label('Reference'),
                                TextEntry::make('status')->badge()->color(fn ($state) => $state->color()),
                                TextEntry::make('paid_at')->dateTime()->placeholder('—'),
                            ]),
                    ]),

                Section::make('Internal Notes')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('notes')->hiddenLabel()->placeholder('No notes.')->columnSpanFull(),
                    ]),
            ]);
    }
}
