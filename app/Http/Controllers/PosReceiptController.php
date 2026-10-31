<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Contracts\View\View;

class PosReceiptController extends Controller
{
    public function show(Order $order): View
    {
        abort_unless(auth()->user()?->can_access_pos, 404);

        $order->load(['items', 'cashier']);

        return view('pos.receipt', compact('order'));
    }
}
