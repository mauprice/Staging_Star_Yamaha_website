<?php

namespace App\Filament\Resources\ServiceBookings\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceBookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer Details')
                    ->columns(3)
                    ->schema([
                        TextInput::make('name')->disabled(),
                        TextInput::make('phone')->disabled(),
                        TextInput::make('email')->disabled(),
                    ]),

                Section::make('Vehicle')
                    ->columns(4)
                    ->schema([
                        TextInput::make('vehicle_year')->label('Year')->disabled(),
                        TextInput::make('vehicle_model')->label('Model')->disabled()->columnSpan(2),
                        TextInput::make('rego')->label('Rego')->disabled(),
                        TextInput::make('odometer')->label('Odometer (km)')->disabled(),
                    ]),

                Section::make('Booking Details')
                    ->columns(3)
                    ->schema([
                        TextInput::make('service_type')->label('Service Type')->disabled(),
                        DatePicker::make('preferred_date')->label('Preferred Date')->disabled(),
                        TextInput::make('preferred_time')->label('Preferred Time')->disabled(),
                    ]),

                Section::make('Customer Notes')
                    ->schema([
                        Textarea::make('notes')->disabled()->rows(4)->columnSpanFull(),
                    ]),

                Section::make('Reply to Customer')
                    ->description('Fill in your reply and hit "Send Reply to Customer" above. The customer will receive their original booking details along with your message.')
                    ->schema([
                        Textarea::make('staff_reply')
                            ->label('Message to Customer')
                            ->placeholder('e.g. Hi [Name], thanks for getting in touch. We can confirm your booking or suggest the alternative dates below...')
                            ->rows(5)
                            ->columnSpanFull(),

                        DatePicker::make('alt_date_1')
                            ->label('Alternative Date 1')
                            ->native(false)
                            ->displayFormat('d M Y'),

                        DatePicker::make('alt_date_2')
                            ->label('Alternative Date 2')
                            ->native(false)
                            ->displayFormat('d M Y'),

                        DatePicker::make('alt_date_3')
                            ->label('Alternative Date 3')
                            ->native(false)
                            ->displayFormat('d M Y'),
                    ])
                    ->columns(3),
            ]);
    }
}
