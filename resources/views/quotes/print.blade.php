<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Quote {{ $quote->quote_number }}</title>
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
    .parties { margin-bottom: 24px; }
    .parties h3 { margin: 0 0 4px; font-size: 11px; text-transform: uppercase; color: #777; letter-spacing: .04em; }
    table.lines { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    table.lines th { text-align: left; font-size: 11px; text-transform: uppercase; color: #777; border-bottom: 2px solid #ddd; padding: 6px 4px; }
    table.lines td { padding: 8px 4px; border-bottom: 1px solid #eee; vertical-align: top; }
    table.lines td.right, table.lines th.right { text-align: right; }
    .totals { width: 260px; margin-left: auto; }
    .totals td { padding: 4px; }
    .totals .grand td { font-weight: 700; font-size: 15px; border-top: 2px solid #ddd; padding-top: 8px; }
    .footer { margin-top: 32px; font-size: 11px; color: #777; }
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
            <h1>Quote</h1>
            <div class="muted">{{ $quote->quote_number }}</div>
        </div>
    </div>

    <div class="parties">
        <h3>Prepared For</h3>
        <div>{{ $quote->customer_name }}</div>
        @if($quote->customer_phone)
        <div class="muted">{{ $quote->customer_phone }}</div>
        @endif
        <div class="muted">{{ $quote->created_at->format('d/m/Y') }}</div>
    </div>

    <table class="lines">
        <thead>
            <tr>
                <th>Product</th>
                <th>Part Number</th>
                <th class="right">Qty</th>
                <th class="right">Unit Price</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quote->lines as $line)
            <tr>
                <td>{{ $line->product_name }}{{ $line->variant_label ? " ({$line->variant_label})" : '' }}</td>
                <td>{{ $line->part_number ?? '—' }}</td>
                <td class="right">{{ $line->quantity }}</td>
                <td class="right">${{ number_format($line->unit_price, 2) }}</td>
                <td class="right">${{ number_format($line->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr class="grand"><td>Total</td><td class="right">${{ number_format($quote->lines->sum('line_total'), 2) }}</td></tr>
    </table>

    <div class="footer">
        Prices are current at the time of quoting and may change. This quote is not a tax invoice.
    </div>
</body>
</html>
