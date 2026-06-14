@extends('layouts.app')

@section('title', 'Reports')
@section('page-title', 'Reports & Analytics')

@section('content')
    {{-- Filter bar --}}
    <form method="GET" class="bg-white rounded-xl border border-outline-variant custom-shadow p-md flex flex-col lg:flex-row lg:items-end gap-md">
        <div class="flex flex-col gap-xs">
            <label class="text-label-sm text-outline">From</label>
            <input name="from" value="{{ $filters['from'] }}" type="date" class="bg-surface-container-low border-none rounded-lg text-body-sm px-3 py-2 focus:ring-1 focus:ring-primary">
        </div>
        <div class="flex flex-col gap-xs">
            <label class="text-label-sm text-outline">To</label>
            <input name="to" value="{{ $filters['to'] }}" type="date" class="bg-surface-container-low border-none rounded-lg text-body-sm px-3 py-2 focus:ring-1 focus:ring-primary">
        </div>
        <div class="flex flex-col gap-xs">
            <label class="text-label-sm text-outline">Branch</label>
            <select name="branch" class="bg-surface-container-low border-none rounded-lg text-body-sm px-3 py-2 focus:ring-1 focus:ring-primary">
                <option value="">All Branches</option>
                @foreach ($branches as $b)<option value="{{ $b->id }}" @selected((string) $filters['branch'] === (string) $b->id)>{{ $b->name }}</option>@endforeach
            </select>
        </div>
        <div class="flex gap-2 lg:ml-auto">
            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg text-label-md hover:opacity-90 flex items-center gap-xs"><span class="material-symbols-outlined text-[18px]">filter_alt</span> Apply</button>
            <a href="{{ route('reports.export', $filters) }}" class="bg-surface-container-highest text-primary px-4 py-2 rounded-lg text-label-md hover:bg-surface-variant flex items-center gap-xs"><span class="material-symbols-outlined text-[18px]">picture_as_pdf</span> Export PDF</a>
        </div>
    </form>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-md">
        @php
            $cards = [
                ['Sales', $kpis['sales'], 'payments', 'text-primary'],
                ['Purchases', $kpis['purchases'], 'shopping_cart', 'text-secondary'],
                ['Gross Profit', $kpis['gross_profit'], 'trending_up', 'text-green-600'],
                ['Expenses', $kpis['expenses'], 'receipt_long', 'text-error'],
                ['Net Profit', $kpis['net_profit'], 'savings', $kpis['net_profit'] >= 0 ? 'text-green-600' : 'text-error'],
                ['Stock Value', $kpis['stock_value'], 'inventory_2', 'text-on-surface'],
                ['Receivable', $kpis['receivable'], 'account_balance', 'text-primary'],
                ['Payable', $kpis['payable'], 'outbox', 'text-error'],
            ];
        @endphp
        @foreach ($cards as [$label, $value, $icon, $color])
            <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow">
                <div class="flex justify-between items-start mb-1"><span class="text-label-sm text-on-surface-variant">{{ $label }}</span><span class="material-symbols-outlined {{ $color }} text-[18px]">{{ $icon }}</span></div>
                <h3 class="text-headline-md font-bold {{ $color }}">Rs. {{ number_format($value, 0) }}</h3>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-lg">
        {{-- Sales by method --}}
        <div class="bg-white rounded-xl border border-outline-variant custom-shadow overflow-hidden">
            <div class="px-lg py-md border-b border-outline-variant"><h4 class="text-label-md font-bold">Sales by Payment Method</h4></div>
            <table class="w-full text-left text-body-sm">
                <thead class="bg-surface-container-low text-on-surface-variant text-label-sm uppercase"><tr><th class="px-lg py-sm">Method</th><th class="px-lg py-sm text-center">Count</th><th class="px-lg py-sm text-right">Total</th></tr></thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse ($byMethod as $m)
                        <tr><td class="px-lg py-sm uppercase">{{ $m->payment_method }}</td><td class="px-lg py-sm text-center">{{ $m->c }}</td><td class="px-lg py-sm text-right font-bold">Rs. {{ number_format($m->total, 2) }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="px-lg py-lg text-center text-outline">No sales in range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Sales by branch --}}
        <div class="bg-white rounded-xl border border-outline-variant custom-shadow overflow-hidden">
            <div class="px-lg py-md border-b border-outline-variant"><h4 class="text-label-md font-bold">Sales by Branch</h4></div>
            <table class="w-full text-left text-body-sm">
                <thead class="bg-surface-container-low text-on-surface-variant text-label-sm uppercase"><tr><th class="px-lg py-sm">Branch</th><th class="px-lg py-sm text-center">Invoices</th><th class="px-lg py-sm text-right">Total</th></tr></thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse ($byBranch as $b)
                        <tr><td class="px-lg py-sm">{{ $b->branch?->name }}</td><td class="px-lg py-sm text-center">{{ $b->c }}</td><td class="px-lg py-sm text-right font-bold">Rs. {{ number_format($b->total, 2) }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="px-lg py-lg text-center text-outline">No sales in range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Top medicines --}}
        <div class="bg-white rounded-xl border border-outline-variant custom-shadow overflow-hidden">
            <div class="px-lg py-md border-b border-outline-variant"><h4 class="text-label-md font-bold">Top Selling Medicines</h4></div>
            <table class="w-full text-left text-body-sm">
                <thead class="bg-surface-container-low text-on-surface-variant text-label-sm uppercase"><tr><th class="px-lg py-sm">Medicine</th><th class="px-lg py-sm text-center">Qty</th><th class="px-lg py-sm text-right">Revenue</th></tr></thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse ($topMedicines as $m)
                        <tr><td class="px-lg py-sm">{{ $m->name }}</td><td class="px-lg py-sm text-center">{{ number_format($m->qty) }}</td><td class="px-lg py-sm text-right font-bold text-primary">Rs. {{ number_format($m->revenue, 2) }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="px-lg py-lg text-center text-outline">No sales in range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Expenses by category --}}
        <div class="bg-white rounded-xl border border-outline-variant custom-shadow overflow-hidden">
            <div class="px-lg py-md border-b border-outline-variant"><h4 class="text-label-md font-bold">Expenses by Category</h4></div>
            <table class="w-full text-left text-body-sm">
                <thead class="bg-surface-container-low text-on-surface-variant text-label-sm uppercase"><tr><th class="px-lg py-sm">Category</th><th class="px-lg py-sm text-right">Total</th></tr></thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse ($byCategory as $c)
                        <tr><td class="px-lg py-sm">{{ $c->category?->name ?? 'Uncategorised' }}</td><td class="px-lg py-sm text-right font-bold text-error">Rs. {{ number_format($c->total, 2) }}</td></tr>
                    @empty
                        <tr><td colspan="2" class="px-lg py-lg text-center text-outline">No approved expenses in range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
