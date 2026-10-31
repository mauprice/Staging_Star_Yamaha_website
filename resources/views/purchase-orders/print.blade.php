<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Purchase Order {{ $order->po_number }}</title>
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
    .parties { display: flex; justify-content: space-between; margin-bottom: 24px; gap: 24px; }
    .parties .block { flex: 1; }
    .parties h3 { margin: 0 0 4px; font-size: 11px; text-transform: uppercase; color: #777; letter-spacing: .04em; }
    table.lines { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    table.lines th { text-align: left; font-size: 11px; text-transform: uppercase; color: #777; border-bottom: 2px solid #ddd; padding: 6px 4px; }
    table.lines td { padding: 8px 4px; border-bottom: 1px solid #eee; vertical-align: top; }
    table.lines td.right, table.lines th.right { text-align: right; }
    .totals { width: 260px; margin-left: auto; }
    .totals td { padding: 4px; }
    .totals .grand td { font-weight: 700; font-size: 15px; border-top: 2px solid #ddd; padding-top: 8px; }
    .notes { margin-top: 24px; font-size: 12px; }
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
            <h1>Purchase Order</h1>
            <div class="muted">{{ $order->po_number }}</div>
        </div>
    </div>

    <div class="parties">
        <div class="block">
            <h3>Supplier</h3>
            <div>{{ $order->supplier->name }}</div>
            @if($order->supplier->account_number)
            <div class="muted">Account: {{ $order->supplier->account_number }}</div>
            @endif
            @if($order->supplier->phone)
            <div class="muted">{{ $order->supplier->phone }}</div>
            @endif
            @if($order->supplier->email)
            <div class="muted">{{ $order->supplier->email }}</div>
            @endif
        </div>
        <div class="block">
            <h3>Order Details</h3>
            <div>Status: {{ $order->status->label() }}</div>
            <div class="muted">Created: {{ $order->created_at->format('d/m/Y') }}</div>
            @if($order->ordered_at)
            <div class="muted">Sent: {{ $order->ordered_at->format('d/m/Y') }}</div>
            @endif
        </div>
    </div>

    <table class="lines">
        <thead>
            <tr>
                <th>Product</th>
                <th>Part Number</th>
                <th class="right">Qty</th>
                <th class="right">Unit Cost</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->lines as $line)
            <tr>
                <td>{{ $line->product_name }}</td>
                <td>{{ $line->part_number ?? '—' }}</td>
                <td class="right">{{ $line->quantity_ordered }}</td>
                <td class="right">${{ number_format($line->unit_cost, 2) }}</td>
                <td class="right">${{ number_format($line->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr class="grand"><td>Total</td><td class="right">${{ number_format($order->lines->sum('line_total'), 2) }}</td></tr>
    </table>

    @if($order->notes)
    <div class="notes">
        <h3 style="font-size: 11px; text-transform: uppercase; color: #777;">Notes</h3>
        <div>{{ $order->notes }}</div>
    </div>
    @endif
</body>
</html>
