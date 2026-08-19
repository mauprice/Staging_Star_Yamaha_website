<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Invoice {{ $order->order_number }}</title>
<style>
    body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #1f2937; margin: 0; padding: 0; }
    .wrapper { padding: 32px; }
    .header { display: table; width: 100%; margin-bottom: 24px; }
    .header .col { display: table-cell; vertical-align: top; }
    .header .col.right { text-align: right; }
    .brand { font-size: 20px; font-weight: 700; color: #1f2937; }
    .muted { color: #6b7280; }
    h2 { font-size: 15px; margin: 0 0 6px; }
    table.items { width: 100%; border-collapse: collapse; margin-top: 16px; }
    table.items th { text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; border-bottom: 2px solid #e5e7eb; padding: 8px 6px; }
    table.items td { padding: 8px 6px; border-bottom: 1px solid #f3f4f6; }
    table.items td.right, table.items th.right { text-align: right; }
    .totals { width: 260px; margin-left: auto; margin-top: 12px; }
    .totals table { width: 100%; }
    .totals td { padding: 3px 0; font-size: 12px; }
    .totals td.right { text-align: right; }
    .totals .grand td { border-top: 2px solid #e5e7eb; padding-top: 8px; font-weight: 700; font-size: 14px; }
    .addresses { display: table; width: 100%; margin-top: 24px; }
    .addresses .col { display: table-cell; width: 50%; vertical-align: top; }
    .footer { margin-top: 32px; font-size: 10px; color: #9ca3af; }
</style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <div class="col">
            <div class="brand">{{ config('dealership.name') }}</div>
            <p class="muted">{{ config('dealership.address.full') }}<br>{{ config('dealership.phone.display') }}<br>{{ config('dealership.email.sales') }}</p>
        </div>
        <div class="col right">
            <h2>Invoice</h2>
            <p class="muted">Order {{ $order->order_number }}<br>{{ $order->paid_at?->format('d M Y') ?? $order->placed_at?->format('d M Y') }}</p>
        </div>
    </div>

    <div class="addresses">
        <div class="col">
            <h2>Billed To</h2>
            <p>
                {{ $order->customer_name }}<br>
                {{ $order->customer_email }}<br>
                @if($order->customer_phone){{ $order->customer_phone }}<br>@endif
                @if($order->effectiveBillingAddress())
                    {{ $order->effectiveBillingAddress()->line1 }}<br>
                    @if($order->effectiveBillingAddress()->line2){{ $order->effectiveBillingAddress()->line2 }}<br>@endif
                    {{ $order->effectiveBillingAddress()->suburb }} {{ $order->effectiveBillingAddress()->state }} {{ $order->effectiveBillingAddress()->postcode }}
                @endif
            </p>
        </div>
        <div class="col">
            <h2>Shipping To</h2>
            @if($order->shippingAddress)
            <p>
                {{ $order->shippingAddress->full_name }}<br>
                {{ $order->shippingAddress->line1 }}<br>
                @if($order->shippingAddress->line2){{ $order->shippingAddress->line2 }}<br>@endif
                {{ $order->shippingAddress->suburb }} {{ $order->shippingAddress->state }} {{ $order->shippingAddress->postcode }}
            </p>
            @endif
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Item</th>
                <th class="right">Unit Price</th>
                <th class="right">Qty</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->product_name }}{{ $item->variant_label ? " ({$item->variant_label})" : '' }}</td>
                <td class="right">${{ number_format($item->unit_price, 2) }}</td>
                <td class="right">{{ $item->quantity }}</td>
                <td class="right">${{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr><td>Subtotal</td><td class="right">${{ number_format($order->subtotal, 2) }}</td></tr>
            <tr><td>Shipping</td><td class="right">${{ number_format($order->shipping_total, 2) }}</td></tr>
            <tr class="grand"><td>Total ({{ $order->currency }})</td><td class="right">${{ number_format($order->total, 2) }}</td></tr>
        </table>
    </div>

    <p class="footer">Payment method: {{ $order->payment_method?->label() ?? '—' }} &bull; Status: {{ $order->status->label() }}</p>
</div>
</body>
</html>
