<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Placed On Your Account</title>
<style>
    body { font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #1f2937; margin: 0; padding: 0; background: #f9fafb; }
    .wrapper { max-width: 560px; margin: 32px auto; background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb; }
    .header { background: #1f2937; padding: 24px 32px; }
    .header h1 { margin: 0; color: #ffffff; font-size: 20px; font-weight: 700; }
    .body { padding: 28px 32px; }
    .body p { margin: 0 0 12px; line-height: 1.6; color: #374151; }
    .btn { display: inline-block; background: #dc2626; color: #ffffff !important; text-decoration: none; font-weight: 700; padding: 12px 24px; border-radius: 6px; margin: 12px 0; }
    .footer { padding: 16px 32px; border-top: 1px solid #e5e7eb; background: #f9fafb; }
    .footer p { margin: 0; font-size: 12px; color: #9ca3af; text-align: center; }
</style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>An order was placed using your details</h1>
    </div>
    <div class="body">
        <p>Hi,</p>
        <p>An order ({{ $order->order_number }}) was recently placed as a guest using the email or phone number on this account. If this was you, log in to view the order, track it, and access your invoice any time.</p>
        <p style="text-align:center;">
            <a href="{{ route('yamaha.account.orders.show', $order) }}" class="btn">View Order</a>
        </p>
        <p>If this wasn't you, no action is needed — this order isn't linked to your account unless you log in.</p>
    </div>
    <div class="footer">
        <p>{{ config('dealership.name') }} &bull; {{ config('dealership.phone.display') }}</p>
    </div>
</div>
</body>
</html>
