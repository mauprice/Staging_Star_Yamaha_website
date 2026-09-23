<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Ready for Pickup</title>
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
    .notice { background: rgba(245,158,11,.1); border-left: 4px solid #f59e0b; padding: 12px 16px; margin: 20px 0; font-size: 13px; color: #92400e; }
    .footer { padding: 16px 32px; border-top: 1px solid #e5e7eb; background: #f9fafb; }
    .footer p { margin: 0; font-size: 12px; color: #9ca3af; text-align: center; }
</style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>Your order is ready for pickup</h1>
        <p>Order {{ $order->order_number }}</p>
    </div>
    <div class="body">
        <p>Hi {{ $order->customer_name }},</p>
        <p>Your order is ready to collect in-store.</p>
        <table class="items">
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->product_name }}{{ $item->variant_label ? " ({$item->variant_label})" : '' }} &times; {{ $item->quantity }}</td>
                <td>${{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
        </table>
        <div class="notice">
            Collect from {{ config('dealership.name') }}, {{ config('dealership.address.street') }}, {{ config('dealership.address.suburb') }} {{ config('dealership.address.state') }}. Please bring your order number and a form of ID.
        </div>
    </div>
    <div class="footer">
        <p>{{ config('dealership.name') }} &bull; {{ config('dealership.phone.display') }}</p>
    </div>
</div>
</body>
</html>
