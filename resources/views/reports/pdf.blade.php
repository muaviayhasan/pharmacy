<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { color: #151c27; font-size: 12px; }
        h1 { color: #0f766e; font-size: 20px; margin: 0; }
        .muted { color: #6e7977; font-size: 11px; }
        .kpis { width: 100%; border-collapse: collapse; margin: 16px 0; }
        .kpis td { border: 1px solid #e5e7eb; padding: 8px; width: 25%; }
        .kpis .label { color: #6e7977; font-size: 10px; text-transform: uppercase; }
        .kpis .value { font-size: 14px; font-weight: bold; }
        h2 { font-size: 13px; color: #0f766e; margin: 18px 0 6px; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th { background: #f0f3ff; text-align: left; padding: 6px; font-size: 10px; text-transform: uppercase; color: #3e4947; }
        table.data td { padding: 6px; border-bottom: 1px solid #eee; }
        .right { text-align: right; }
        .center { text-align: center; }
    </style>
</head>
<body>
    <h1>{{ config('app.name', 'PharmaCore') }} — Business Report</h1>
    <p class="muted">Period: {{ $from }} to {{ $to }} &nbsp;•&nbsp; Generated: {{ $generatedAt->format('d M Y, h:i A') }}</p>

    <table class="kpis">
        <tr>
            <td><div class="label">Sales</div><div class="value">Rs. {{ number_format($kpis['sales'], 0) }}</div></td>
            <td><div class="label">Purchases</div><div class="value">Rs. {{ number_format($kpis['purchases'], 0) }}</div></td>
            <td><div class="label">Gross Profit</div><div class="value">Rs. {{ number_format($kpis['gross_profit'], 0) }}</div></td>
            <td><div class="label">Expenses</div><div class="value">Rs. {{ number_format($kpis['expenses'], 0) }}</div></td>
        </tr>
        <tr>
            <td><div class="label">Net Profit</div><div class="value">Rs. {{ number_format($kpis['net_profit'], 0) }}</div></td>
            <td><div class="label">Stock Value</div><div class="value">Rs. {{ number_format($kpis['stock_value'], 0) }}</div></td>
            <td><div class="label">Receivable</div><div class="value">Rs. {{ number_format($kpis['receivable'], 0) }}</div></td>
            <td><div class="label">Payable</div><div class="value">Rs. {{ number_format($kpis['payable'], 0) }}</div></td>
        </tr>
    </table>

    <h2>Sales by Payment Method</h2>
    <table class="data">
        <thead><tr><th>Method</th><th class="center">Count</th><th class="right">Total</th></tr></thead>
        <tbody>
            @forelse ($byMethod as $m)
                <tr><td>{{ strtoupper($m->payment_method) }}</td><td class="center">{{ $m->c }}</td><td class="right">Rs. {{ number_format($m->total, 2) }}</td></tr>
            @empty
                <tr><td colspan="3">No sales in range.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Top Selling Medicines</h2>
    <table class="data">
        <thead><tr><th>Medicine</th><th class="center">Qty</th><th class="right">Revenue</th></tr></thead>
        <tbody>
            @forelse ($topMedicines as $m)
                <tr><td>{{ $m->name }}</td><td class="center">{{ number_format($m->qty) }}</td><td class="right">Rs. {{ number_format($m->revenue, 2) }}</td></tr>
            @empty
                <tr><td colspan="3">No sales in range.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Expenses by Category</h2>
    <table class="data">
        <thead><tr><th>Category</th><th class="right">Total</th></tr></thead>
        <tbody>
            @forelse ($byCategory as $c)
                <tr><td>{{ $c->category?->name ?? 'Uncategorised' }}</td><td class="right">Rs. {{ number_format($c->total, 2) }}</td></tr>
            @empty
                <tr><td colspan="2">No approved expenses in range.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
