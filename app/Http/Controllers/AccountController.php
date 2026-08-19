<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function dashboard(): View
    {
        $recentOrders = auth()->user()->orders()->latest('placed_at')->limit(5)->get();

        return view('yamaha.account.dashboard', compact('recentOrders'));
    }

    public function orders(): View
    {
        $orders = auth()->user()->orders()->latest('placed_at')->paginate(15);

        return view('yamaha.account.orders.index', compact('orders'));
    }

    public function orderShow(Order $order): View
    {
        $this->authorizeOwnership($order);

        $order->load(['items', 'shippingAddress', 'billingAddress', 'payments']);

        return view('yamaha.account.orders.show', compact('order'));
    }

    public function invoice(Order $order): Response
    {
        $this->authorizeOwnership($order);

        $order->load(['items', 'shippingAddress', 'billingAddress']);

        $pdf = Pdf::loadView('invoices.order', compact('order'))->setPaper('a4', 'portrait');

        return $pdf->stream("invoice-{$order->order_number}.pdf");
    }

    private function authorizeOwnership(Order $order): void
    {
        abort_unless($order->user_id === auth()->id(), 404);
    }
}
