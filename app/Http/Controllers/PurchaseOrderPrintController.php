<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use Illuminate\Contracts\View\View;

class PurchaseOrderPrintController extends Controller
{
    public function show(PurchaseOrder $purchaseOrder): View
    {
        abort_unless(auth()->user()?->hasAnyRole(['Admin', 'Manager']) ?? false, 404);

        $purchaseOrder->load(['lines', 'supplier']);

        return view('purchase-orders.print', ['order' => $purchaseOrder]);
    }
}
