<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class PosReceiptController extends Controller
{
    public function show(Order $order): Response
    {
        abort_unless(auth()->user()?->hasAnyRole(['Admin', 'Manager', 'Sales']), 404);

        $order->load(['items', 'shippingAddress', 'billingAddress']);

        $pdf = Pdf::loadView('invoices.order', compact('order'))->setPaper('a4', 'portrait');

        return $pdf->stream("receipt-{$order->order_number}.pdf");
    }
}
