<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Commission Report</title>
<style>
    body { font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #1f2937; margin: 0; padding: 0; background: #f9fafb; }
    .wrapper { max-width: 560px; margin: 32px auto; background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb; }
    .header { background: #1d4ed8; padding: 24px 32px; }
    .header h1 { margin: 0; color: #ffffff; font-size: 20px; font-weight: 700; }
    .header p { margin: 4px 0 0; color: #bfdbfe; font-size: 13px; }
    .body { padding: 28px 32px; }
    .body p { margin: 0 0 12px; line-height: 1.6; color: #374151; }
    .summary { background: #f3f4f6; border-radius: 6px; padding: 16px 20px; margin: 20px 0; }
    .summary table { width: 100%; border-collapse: collapse; }
    .summary td { padding: 4px 0; font-size: 13px; }
    .summary td:last-child { text-align: right; font-weight: 600; }
    .total-row td { border-top: 1px solid #d1d5db; padding-top: 10px; margin-top: 6px; font-size: 15px; color: #1d4ed8; }
    .footer { padding: 16px 32px; border-top: 1px solid #e5e7eb; background: #f9fafb; }
    .footer p { margin: 0; font-size: 12px; color: #9ca3af; text-align: center; }
</style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>Commission Report</h1>
        <p>Star Yamaha &mdash; {{ $start->format('d M Y') }} to {{ $end->format('d M Y') }}{{ $salesman ? ' &mdash; '.$salesman : '' }}</p>
    </div>
    <div class="body">
        <p>Please find the commission report attached as a PDF.</p>
        <div class="summary">
            <table>
                <tr>
                    <td style="color:#6b7280;">Period</td>
                    <td>{{ $start->format('d M Y') }} &ndash; {{ $end->format('d M Y') }}</td>
                </tr>
                @if($salesman)
                <tr>
                    <td style="color:#6b7280;">Salesperson</td>
                    <td>{{ $salesman }}</td>
                </tr>
                @endif
                <tr>
                    <td style="color:#6b7280;">Entries</td>
                    <td>{{ $count }}</td>
                </tr>
                <tr class="total-row">
                    <td>Total Commission</td>
                    <td>${{ number_format($total, 2) }}</td>
                </tr>
            </table>
        </div>
        <p>The full breakdown is in the attached PDF.</p>
    </div>
    <div class="footer">
        <p>Star Yamaha &bull; Generated automatically</p>
    </div>
</div>
</body>
</html>
