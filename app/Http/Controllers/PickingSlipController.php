<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Contracts\View\View;

class PickingSlipController extends Controller
{
    public function show(Order $order): View
    {
        abort_unless(auth()->user()?->hasAnyRole(['Admin', 'Manager', 'Sales']) ?? false, 404);

        $order->load(['items', 'customer']);

        return view('orders.picking-slip', ['order' => $order]);
    }
}
