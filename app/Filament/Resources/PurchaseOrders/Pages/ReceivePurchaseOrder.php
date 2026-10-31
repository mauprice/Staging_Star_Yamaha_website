<?php

namespace App\Filament\Resources\PurchaseOrders\Pages;

use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use App\Models\Product;
use App\Models\StockReceipt;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;

class ReceivePurchaseOrder extends Page
{
    use InteractsWithRecord;

    protected static string $resource = PurchaseOrderResource::class;

    protected string $view = 'filament.resources.purchase-orders.pages.receive-purchase-order';

    /** @var array<int, int> keyed by purchase_order_line id => quantity to receive now */
    public array $receiveQty = [];

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        abort_unless(auth()->user()?->hasAnyRole(['Admin', 'Manager']) ?? false, 403);

        foreach ($this->record->lines as $line) {
            $this->receiveQty[$line->id] = $line->quantityOutstanding();
        }
    }

    public function getTitle(): string
    {
        return "Receive {$this->getRecord()->po_number}";
    }

    public function receive(): void
    {
        $order = $this->getRecord();
        $receivedAny = false;

        DB::transaction(function () use ($order, &$receivedAny) {
            foreach ($order->lines as $line) {
                $qty = (int) ($this->receiveQty[$line->id] ?? 0);
                $qty = max(0, min($qty, $line->quantityOutstanding()));

                if ($qty < 1) {
                    continue;
                }

                $receivedAny = true;

                $line->update(['quantity_received' => $line->quantity_received + $qty]);

                if ($line->product_id) {
                    Product::where('id', $line->product_id)->increment('stock_quantity', $qty);
                }

                StockReceipt::create([
                    'purchase_order_line_id' => $line->id,
                    'product_id' => $line->product_id,
                    'received_by' => auth()->id(),
                    'quantity' => $qty,
                    'received_at' => now(),
                ]);
            }
        });

        if (! $receivedAny) {
            Notification::make()->title('Nothing to receive')->warning()->send();

            return;
        }

        $order->refreshReceivingStatus();

        Notification::make()->title('Stock received')->success()->send();

        $this->redirect(PurchaseOrderResource::getUrl('index'));
    }
}
