<?php

namespace App\Filament\Resources\ServiceBookings\Pages;

use App\Filament\Resources\ServiceBookings\ServiceBookingResource;
use App\Mail\ServiceBookingReplyMail;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class EditServiceBooking extends EditRecord
{
    protected static string $resource = ServiceBookingResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if ($this->record->read_at === null) {
            $this->record->update(['read_at' => Carbon::now()]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sendReply')
                ->label('Send Reply to Customer')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Send reply to ' . $this->record->name . '?')
                ->modalDescription('This will email the customer their original booking details, your message, and any alternative dates you have added.')
                ->modalSubmitActionLabel('Send Reply')
                ->before(function () {
                    $this->save();
                })
                ->action(function () {
                    $booking = $this->record->fresh();

                    if (blank($booking->staff_reply)) {
                        Notification::make()
                            ->title('No message entered')
                            ->body('Please write a message to the customer before sending.')
                            ->danger()
                            ->send();

                        $this->halt();
                        return;
                    }

                    Mail::to($booking->email, $booking->name)
                        ->send(new ServiceBookingReplyMail($booking));

                    $booking->update(['replied_at' => Carbon::now()]);

                    Notification::make()
                        ->title('Reply sent to ' . $booking->name)
                        ->success()
                        ->send();
                }),

            DeleteAction::make(),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return null;
    }
}
