@extends('layouts.app')

@section('title', 'Sales')
@section('page-title', 'Sales Management')

@section('content')
    <div class="grid grid-cols-2 md:grid-cols-4 gap-md">
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Total Sales</p><h3 class="text-headline-md font-bold text-primary">Rs. {{ number_format($stats['total'], 0) }}</h3></div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Invoices</p><h3 class="text-headline-md font-bold">{{ number_format($stats['count']) }}</h3></div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Today's Sales</p><h3 class="text-headline-md font-bold">Rs. {{ number_format($stats['today'], 0) }}</h3></div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Outstanding Due</p><h3 class="text-headline-md font-bold text-error">Rs. {{ number_format($stats['due'], 0) }}</h3></div>
    </div>

    <div class="bg-white rounded-xl border border-outline-variant custom-shadow">
        <form method="GET" class="p-md flex flex-col lg:flex-row lg:items-center gap-md border-b border-outline-variant">
            <div class="relative flex-1">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[20px]">search</span>
                <input name="search" value="{{ $filters['search'] ?? '' }}" type="text" placeholder="Search invoice no or customer..." class="w-full pl-10 pr-4 py-2 bg-surface-container-low border-none rounded-lg text-body-sm focus:ring-1 focus:ring-primary">
            </div>
            <select name="payment_method" class="bg-surface-container-low border-none rounded-lg text-label-md px-3 py-2 focus:ring-1 focus:ring-primary">
                <option value="">All Methods</option>
                @foreach (['cash', 'card', 'bank', 'credit'] as $m)<option value="{{ $m }}" @selected(($filters['payment_method'] ?? '') === $m)>{{ ucfirst($m) }}</option>@endforeach
            </select>
            <select name="payment_status" class="bg-surface-container-low border-none rounded-lg text-label-md px-3 py-2 focus:ring-1 focus:ring-primary">
                <option value="">All Status</option>
                @foreach (['paid', 'partial', 'unpaid', 'refunded'] as $s)<option value="{{ $s }}" @selected(($filters['payment_status'] ?? '') === $s)>{{ ucfirst($s) }}</option>@endforeach
            </select>
            <input name="from" value="{{ $filters['from'] ?? '' }}" type="date" class="bg-surface-container-low border-none rounded-lg text-label-md px-3 py-2 focus:ring-1 focus:ring-primary">
            <input name="to" value="{{ $filters['to'] ?? '' }}" type="date" class="bg-surface-container-low border-none rounded-lg text-label-md px-3 py-2 focus:ring-1 focus:ring-primary">
            <button type="submit" class="bg-surface-container-highest text-on-surface-variant px-4 py-2 rounded-lg text-label-md hover:bg-outline-variant">Filter</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-low text-on-surface-variant text-label-sm uppercase">
                    <tr><th class="px-md py-3">Date / Time</th><th class="px-md py-3">Invoice</th><th class="px-md py-3">Customer</th><th class="px-md py-3 text-center">Items</th><th class="px-md py-3 text-right">Total</th><th class="px-md py-3 text-right">Paid / Due</th><th class="px-md py-3 text-center">Payment</th><th class="px-md py-3 text-center">Status</th><th class="px-md py-3 text-right">Action</th></tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse ($sales as $sale)
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="px-md py-md text-body-sm">{{ $sale->sale_date?->format('d M Y') }}<div class="text-[11px] text-outline">{{ $sale->sale_date?->format('h:i A') }}</div></td>
                            <td class="px-md py-md font-bold">{{ $sale->sale_no }}</td>
                            <td class="px-md py-md text-body-sm">{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                            <td class="px-md py-md text-center">{{ $sale->items_count }}</td>
                            <td class="px-md py-md text-right font-bold">Rs. {{ number_format($sale->grand_total, 2) }}</td>
                            <td class="px-md py-md text-right text-body-sm">Rs. {{ number_format($sale->paid_amount, 0) }}@if ($sale->due_amount > 0) <span class="text-error">/ {{ number_format($sale->due_amount, 0) }}</span>@endif</td>
                            <td class="px-md py-md text-center"><span class="text-label-sm uppercase">{{ $sale->payment_method }}</span></td>
                            <td class="px-md py-md text-center">
                                @php $b = match ($sale->payment_status) { 'paid' => 'bg-green-100 text-green-700', 'partial' => 'bg-amber-100 text-amber-700', 'refunded' => 'bg-blue-100 text-blue-700', default => 'bg-red-100 text-red-700' }; @endphp
                                <span class="px-2 py-0.5 {{ $b }} rounded-full text-[10px] font-bold uppercase">{{ $sale->payment_status }}</span>
                            </td>
                            <td class="px-md py-md text-right">
                                <a href="{{ route('sales.show', $sale) }}" class="p-1.5 text-outline hover:text-primary inline-flex" title="View invoice"><span class="material-symbols-outlined text-[18px]">visibility</span></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-md py-10 text-center text-outline">No sales found. Sales created at the POS appear here.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-md border-t border-outline-variant">{{ $sales->links() }}</div>
    </div>
@endsection
