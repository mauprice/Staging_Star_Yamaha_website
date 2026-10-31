<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Receipt {{ $order->order_number }}</title>
<style>
    @page { size: 80mm auto; margin: 0; }
    * { box-sizing: border-box; }
    body {
        font-family: 'Courier New', Courier, monospace;
        font-size: 12px;
        color: #000;
        width: 72mm;
        margin: 0 auto;
        padding: 4mm;
    }
    .center { text-align: center; }
    .right { text-align: right; }
    .brand { font-size: 15px; font-weight: 700; }
    .muted { color: #333; }
    hr { border: none; border-top: 1px dashed #000; margin: 6px 0; }
    table { width: 100%; border-collapse: collapse; }
    .items td { padding: 2px 0; vertical-align: top; }
    .items td.name { padding-right: 4px; }
    .items .meta { font-size: 11px; color: #333; }
    .totals td { padding: 1px 0; }
    .totals .grand td { font-weight: 700; font-size: 14px; border-top: 1px dashed #000; padding-top: 4px; }
    .footer { margin-top: 10px; font-size: 11px; }
    .print-bar { text-align: center; margin-bottom: 10px; }
    .print-bar button { font-size: 14px; padding: 6px 16px; }
    @media print {
        .print-bar { display: none; }
        body { width: auto; padding: 0; margin: 0; }
    }
</style>
</head>
<body>
    <div class="print-bar">
        <button onclick="window.print()">Print</button>
    </div>

    <div class="center">
        <div class="brand">{{ config('dealership.name') }}</div>
        <div class="muted">{{ config('dealership.address.full') }}</div>
        <div class="muted">{{ config('dealership.phone.display') }}</div>
    </div>

    <hr>

    <div>
        Receipt: {{ $order->order_number }}<br>
        Date: {{ ($order->paid_at ?? $order->placed_at)?->format('d/m/Y g:i A') }}<br>
        Served by: {{ $order->cashier?->name ?? '—' }}
    </div>

    <hr>

    <table class="items">
        @foreach($order->items as $item)
        <tr>
            <td class="name" colspan="2">
                {{ $item->product_name }}{{ $item->variant_label ? " ({$item->variant_label})" : '' }}
                @if($item->part_number)
                    <div class="meta">{{ $item->part_number }}</div>
                @endif
            </td>
        </tr>
        <tr>
            <td class="meta">{{ $item->quantity }} x ${{ number_format($item->unit_price, 2) }}</td>
            <td class="right">${{ number_format($item->line_total, 2) }}</td>
        </tr>
        @endforeach
    </table>

    <hr>

    <table class="totals">
        <tr><td>Subtotal</td><td class="right">${{ number_format($order->subtotal, 2) }}</td></tr>
        @if($order->tax_total !== null)
        <tr><td class="muted">Includes GST</td><td class="right muted">${{ number_format($order->tax_total, 2) }}</td></tr>
        @endif
        <tr class="grand"><td>Total</td><td class="right">${{ number_format($order->total, 2) }}</td></tr>
    </table>

    <hr>

    <div>Payment: {{ $order->payment_method?->label() ?? '—' }}</div>

    <div class="footer center">
        Thank you for shopping with us!
    </div>

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
