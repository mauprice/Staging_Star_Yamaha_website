<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            color: #1f2937;
            margin: 0;
            padding: 12px 16px;
        }

        h1 {
            text-align: center;
            font-size: 15pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 0 0 4px 0;
            text-decoration: underline;
        }

        .date-range {
            text-align: center;
            font-size: 8pt;
            color: #6b7280;
            margin-bottom: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        thead th {
            background-color: #1d4ed8;
            color: #ffffff;
            padding: 5px 4px;
            text-align: center;
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border: 1px solid #1e40af;
        }

        tbody td {
            padding: 3px 4px;
            border: 1px solid #d1d5db;
            vertical-align: middle;
        }

        tbody tr:nth-child(even) td {
            background-color: #f9fafb;
        }

        .subtotal-row td {
            background-color: #e5e7eb !important;
            font-weight: bold;
            border-top: 1.5px solid #9ca3af;
            border-bottom: 1.5px solid #9ca3af;
        }

        .total-row td {
            background-color: #d1d5db !important;
            font-weight: bold;
            font-size: 9pt;
            border-top: 2px solid #374151;
        }

        .text-right  { text-align: right; }
        .text-center { text-align: center; }
        .text-left   { text-align: left; }

        .profit-pos { color: #15803d; }
        .profit-neg { color: #dc2626; }

        /* Column widths for landscape A4 */
        .col-date    { width: 7%; }
        .col-type    { width: 6%; }
        .col-bike    { width: 16%; }
        .col-cust    { width: 22%; }
        .col-comms   { width: 8%; }
        .col-paydate { width: 8%; }
        .col-profit  { width: 9%; }
        .col-sales   { width: 5%; }
        .col-yearly  { width: 5%; }
        .col-ymf     { width: 7%; }
    </style>
</head>
<body>

    <h1>
        @if($start->format('M Y') === $end->format('M Y'))
            {{ strtoupper($start->format('M Y')) }}
        @else
            {{ strtoupper($start->format('M Y')) }} &ndash; {{ strtoupper($end->format('M Y')) }}
        @endif
    </h1>
    <p class="date-range">{{ $start->format('d/m/Y') }} to {{ $end->format('d/m/Y') }}</p>

    <table>
        <colgroup>
            <col class="col-date">
            <col class="col-type">
            <col class="col-bike">
            <col class="col-cust">
            <col class="col-comms">
            <col class="col-paydate">
            <col class="col-profit">
            <col class="col-sales">
            <col class="col-yearly">
            <col class="col-ymf">
        </colgroup>
        <thead>
            <tr>
                <th class="col-date text-center">DATE</th>
                <th class="col-type text-center">NEW/USED</th>
                <th class="col-bike text-left">BIKE</th>
                <th class="col-cust text-left">CUSTOMER</th>
                <th class="col-comms text-right">COMMS</th>
                <th class="col-paydate text-center">PAY DATE</th>
                <th class="col-profit text-right">PROFIT</th>
                <th class="col-sales text-center">SALES</th>
                <th class="col-yearly text-center">YEARLY</th>
                <th class="col-ymf text-center">YMF</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groups as $group)

                @foreach($group['entries'] as $item)
                    @php
                        $entry  = $item['entry'];
                        $profit = (float) $entry->profit;
                    @endphp
                    <tr>
                        <td class="text-center">
                            {{ $entry->sold_date ? $entry->sold_date->format('d/m/Y') : '' }}
                        </td>
                        <td class="text-center">{{ $entry->new_or_used ?? '' }}</td>
                        <td class="text-left">{{ $entry->description }}</td>
                        <td class="text-left">{{ $entry->sold_to }}</td>
                        <td class="text-right">$ {{ number_format($item['commission'], 2) }}</td>
                        <td></td>
                        <td class="text-right {{ $profit < 0 ? 'profit-neg' : 'profit-pos' }}">
                            $ {{ number_format($profit, 2) }}
                        </td>
                        <td class="text-center">{{ $item['sales_no'] }}</td>
                        <td class="text-center">{{ $item['yearly_no'] }}</td>
                        <td class="text-center">{{ $entry->ymaf_unit_no ?? '' }}</td>
                    </tr>
                @endforeach

                {{-- Pay-period subtotal --}}
                <tr class="subtotal-row">
                    <td colspan="4"></td>
                    <td class="text-right">$ {{ number_format($group['group_comm'], 2) }}</td>
                    <td class="text-center">
                        {{ $group['pay_date'] ? $group['pay_date']->format('j-M') : '' }}
                    </td>
                    <td colspan="4"></td>
                </tr>

            @endforeach

            {{-- Grand totals --}}
            <tr class="total-row">
                <td colspan="3" class="text-right">TOTAL</td>
                <td></td>
                <td class="text-right">$ {{ number_format($totals['commission'], 2) }}</td>
                <td></td>
                <td class="text-right {{ $totals['profit'] < 0 ? 'profit-neg' : 'profit-pos' }}">
                    $ {{ number_format($totals['profit'], 2) }}
                </td>
                <td class="text-center">{{ $totals['sales'] }}</td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>

</body>
</html>
