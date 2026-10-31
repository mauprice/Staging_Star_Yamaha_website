<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Picking Slip {{ $order->order_number }}</title>
<style>
    @page { size: A4; margin: 15mm; }
    * { box-sizing: border-box; }
    body { font-family: Arial, Helvetica, sans-serif; font-size: 13px; color: #111; }
    .print-bar { text-align: center; margin-bottom: 16px; }
    .print-bar button { font-size: 14px; padding: 6px 16px; }
    .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; }
    .brand { font-size: 18px; font-weight: 700; }
    .muted { color: #555; }
    .title { text-align: right; }
    .title h1 { margin: 0; font-size: 22px; }
    .meta { margin-bottom: 24px; }
    .meta h3 { margin: 0 0 4px; font-size: 11px; text-transform: uppercase; color: #777; letter-spacing: .04em; }
    table.lines { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    table.lines th { text-align: left; font-size: 11px; text-transform: uppercase; color: #777; border-bottom: 2px solid #ddd; padding: 6px 4px; }
    table.lines td { padding: 10px 4px; border-bottom: 1px solid #eee; vertical-align: top; }
    table.lines td.right, table.lines th.right { text-align: right; }
    .checkbox { display: inline-block; width: 14px; height: 14px; border: 1.5px solid #555; margin-right: 8px; vertical-align: middle; }
    .badge { display: inline-block; font-size: 10px; text-transform: uppercase; padding: 2px 6px; border-radius: 4px; font-weight: 700; }
    .badge-backorder { background: #fef3c7; color: #92400e; }
    .badge-fulfilled { background: #dcfce7; color: #166534; }
    @media print { .print-bar { display: none; } }
</style>
</head>
<body>
    <div class="print-bar">
        <button onclick="window.print()">Print</button>
    </div>

    <div class="header">
        <div>
            <div class="brand">{{ config('dealership.name') }}</div>
            <div class="muted">{{ config('dealership.address.full') }}</div>
            <div class="muted">{{ config('dealership.phone.display') }}</div>
        </div>
        <div class="title">
            <h1>Picking Slip</h1>
            <div class="muted">{{ $order->order_number }}</div>
        </div>
    </div>

    <div class="meta">
        <h3>Customer</h3>
        <div>{{ $order->customer_name }}</div>
        @if($order->customer_phone)
        <div class="muted">{{ $order->customer_phone }}</div>
        @endif
        <div class="muted">{{ ($order->placed_at ?? $order->created_at)->format('d/m/Y') }}</div>
    </div>

    <table class="lines">
        <thead>
            <tr>
                <th></th>
                <th>Product</th>
                <th>Part Number</th>
                <th class="right">Qty</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td><span class="checkbox"></span></td>
                <td>{{ $item->product_name }}{{ $item->variant_label ? " ({$item->variant_label})" : '' }}</td>
                <td>{{ $item->part_number ?? '—' }}</td>
                <td class="right">{{ $item->quantity }}</td>
                <td>
                    @if($item->fulfillment_status->value === 'back_ordered')
                    <span class="badge badge-backorder">Back Ordered</span>
                    @if($item->expected_date)
                    <div class="muted" style="font-size:11px; margin-top:2px;">Expected {{ $item->expected_date->format('d/m/Y') }}</div>
                    @endif
                    @else
                    <span class="badge badge-fulfilled">Pick</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
