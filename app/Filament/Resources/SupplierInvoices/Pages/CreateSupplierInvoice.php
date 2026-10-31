<?php

namespace App\Filament\Resources\SupplierInvoices\Pages;

use App\Filament\Resources\SupplierInvoices\SupplierInvoiceResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateSupplierInvoice extends CreateRecord
{
    protected static string $resource = SupplierInvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }

    /**
     * Entering an invoice/credit IS receiving it - stock moves immediately
     * once the lines are saved, no separate draft/receive step like a PO.
     */
    protected function afterCreate(): void
    {
        $this->record->applyStock();

        Notification::make()->title('Stock updated')->success()->send();
    }
}
