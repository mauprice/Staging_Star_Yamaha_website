<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            color: #1f2937;
            margin: 0;
            padding: 14px 18px;
        }

        h1 {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 0 0 3px 0;
            text-decoration: underline;
        }

        .sub {
            text-align: center;
            font-size: 8pt;
            color: #6b7280;
            margin-bottom: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        thead th {
            background-color: #1d4ed8;
            color: #ffffff;
            padding: 5px 6px;
            text-align: left;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border: 1px solid #1e40af;
        }

        tbody td {
            padding: 4px 6px;
            border: 1px solid #d1d5db;
            vertical-align: middle;
        }

        tbody tr:nth-child(even) td {
            background-color: #f9fafb;
        }

        .total-row td {
            background-color: #e5e7eb !important;
            font-weight: bold;
            font-size: 9.5pt;
            border-top: 2px solid #374151;
        }

        .text-right  { text-align: right; }
        .text-center { text-align: center; }

        .col-date   { width: 12%; }
        .col-type   { width: 10%; }
        .col-model  { width: 28%; }
        .col-cust   { width: 38%; }
        .col-comm   { width: 12%; }
    </style>
</head>
<body>

    <h1>Salesman Commission Report</h1>
    <p class="sub">
        {{ $start->format('d/m/Y') }} to {{ $end->format('d/m/Y') }}
        @if ($salesman)
            &mdash; {{ $salesman }}
        @endif
    </p>

    <table>
        <colgroup>
            <col class="col-date">
            <col class="col-type">
            <col class="col-model">
            <col class="col-cust">
            <col class="col-comm">
        </colgroup>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Model</th>
                <th>Customer</th>
                <th class="text-right">Commission</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row->date }}</td>
                    <td>{{ $row->type }}</td>
                    <td>{{ $row->model }}</td>
                    <td>{{ $row->customer }}</td>
                    <td class="text-right">$ {{ number_format($row->commission, 2) }}</td>
                </tr>
            @endforeach

            <tr class="total-row">
                <td colspan="4" class="text-right">TOTAL</td>
                <td class="text-right">$ {{ number_format($total, 2) }}</td>
            </tr>
        </tbody>
    </table>

</body>
</html>
