<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Direct Deposit Details</title>
<style>
    body { font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #1f2937; margin: 0; padding: 0; background: #f9fafb; }
    .wrapper { max-width: 560px; margin: 32px auto; background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb; }
    .header { background: #1f2937; padding: 24px 32px; }
    .header h1 { margin: 0; color: #ffffff; font-size: 20px; font-weight: 700; }
    .header p { margin: 4px 0 0; color: #d1d5db; font-size: 13px; }
    .body { padding: 28px 32px; }
    .body p { margin: 0 0 12px; line-height: 1.6; color: #374151; }
    table.items { width: 100%; border-collapse: collapse; margin: 16px 0; }
    table.items td { padding: 6px 0; font-size: 13px; border-bottom: 1px solid #f3f4f6; }
    table.items td:last-child { text-align: right; font-weight: 600; }
    .total-row td { border-top: 1px solid #d1d5db; padding-top: 10px; font-size: 15px; font-weight: 700; }
    .bank-details { background: #f3f4f6; border-radius: 6px; padding: 16px 20px; margin: 20px 0; }
    .bank-details table { width: 100%; border-collapse: collapse; }
    .bank-details td { padding: 4px 0; font-size: 13px; }
    .bank-details td:first-child { color: #6b7280; width: 40%; }
    .bank-details td:last-child { font-weight: 600; }
    .notice { background: rgba(245,158,11,.1); border-left: 4px solid #f59e0b; padding: 12px 16px; margin: 20px 0; font-size: 13px; color: #92400e; }
    .footer { padding: 16px 32px; border-top: 1px solid #e5e7eb; background: #f9fafb; }
    .footer p { margin: 0; font-size: 12px; color: #9ca3af; text-align: center; }
</style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>Almost there — deposit required</h1>
        <p>Order {{ $order->order_number }}</p>
    </div>
    <div class="body">
        <p>Hi {{ $order->customer_name }},</p>
        <p>Thanks for your order. To complete it, please transfer the total below using direct deposit, quoting your order number as the payment reference.</p>

        <table class="items">
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->product_name }}{{ $item->variant_label ? " ({$item->variant_label})" : '' }} &times; {{ $item->quantity }}</td>
                <td>${{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td>Total Due</td>
                <td>${{ number_format($order->total, 2) }}</td>
            </tr>
        </table>

        <div class="bank-details">
            <table>
                <tr><td>Bank</td><td>{{ $bankName ?: '—' }}</td></tr>
                <tr><td>Account Name</td><td>{{ $accountName ?: '—' }}</td></tr>
                <tr><td>BSB</td><td>{{ $bsb ?: '—' }}</td></tr>
                <tr><td>Account Number</td><td>{{ $accountNumber ?: '—' }}</td></tr>
                <tr><td>Reference</td><td>{{ $order->order_number }}</td></tr>
            </table>
        </div>

        <div class="notice">
            Your order will not be shipped until we've confirmed your deposit has been received. We'll send a confirmation once that's done.
        </div>
    </div>
    <div class="footer">
        <p>{{ config('dealership.name') }} &bull; {{ config('dealership.phone.display') }}</p>
    </div>
</div>
</body>
</html>
