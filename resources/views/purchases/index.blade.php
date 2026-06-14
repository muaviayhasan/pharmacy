@extends('layouts.app')

@section('title', 'Purchases')
@section('page-title', 'Purchase Management')

@section('content')
    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-md">
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow">
            <div class="p-2 bg-primary-container rounded-lg text-on-primary-container w-fit mb-2"><span class="material-symbols-outlined">shopping_cart</span></div>
            <p class="text-outline text-label-sm uppercase tracking-tight">Total Invoices</p>
            <h3 class="text-headline-md font-bold">{{ $stats['count'] }}</h3>
        </div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow">
            <div class="p-2 bg-secondary-fixed rounded-lg text-secondary w-fit mb-2"><span class="material-symbols-outlined">payments</span></div>
            <p class="text-outline text-label-sm uppercase tracking-tight">Total Purchased</p>
            <h3 class="text-headline-md font-bold">Rs. {{ number_format($stats['total'], 0) }}</h3>
        </div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow">
            <div class="p-2 bg-error-container rounded-lg text-error w-fit mb-2"><span class="material-symbols-outlined">outbox</span></div>
            <p class="text-outline text-label-sm uppercase tracking-tight">Outstanding Due</p>
            <h3 class="text-headline-md font-bold">Rs. {{ number_format($stats['due'], 0) }}</h3>
        </div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow">
            <div class="p-2 bg-primary-fixed rounded-lg text-primary w-fit mb-2"><span class="material-symbols-outlined">today</span></div>
            <p class="text-outline text-label-sm uppercase tracking-tight">Today's Purchases</p>
            <h3 class="text-headline-md font-bold">Rs. {{ number_format($stats['today'], 0) }}</h3>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-outline-variant custom-shadow">
        <form method="GET" class="p-md flex flex-col lg:flex-row lg:items-center gap-md border-b border-outline-variant">
            <div class="relative flex-1">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[20px]">search</span>
                <input name="search" value="{{ $filters['search'] ?? '' }}" type="text" placeholder="Search invoice no, supplier..."
                       class="w-full pl-10 pr-4 py-2 bg-surface-container-low border-none rounded-lg text-body-sm focus:ring-1 focus:ring-primary">
            </div>
            <select name="supplier" class="bg-surface-container-low border-none rounded-lg text-label-md px-3 py-2 focus:ring-1 focus:ring-primary">
                <option value="">All Suppliers</option>
                @foreach ($suppliers as $s)
                    <option value="{{ $s->id }}" @selected((string) ($filters['supplier'] ?? '') === (string) $s->id)>{{ $s->name }}</option>
                @endforeach
            </select>
            <select name="status" class="bg-surface-container-low border-none rounded-lg text-label-md px-3 py-2 focus:ring-1 focus:ring-primary">
                <option value="">All Status</option>
                @foreach (['paid', 'partial', 'unpaid'] as $st)
                    <option value="{{ $st }}" @selected(($filters['status'] ?? '') === $st)>{{ ucfirst($st) }}</option>
                @endforeach
            </select>
            <input name="from" value="{{ $filters['from'] ?? '' }}" type="date" class="bg-surface-container-low border-none rounded-lg text-label-md px-3 py-2 focus:ring-1 focus:ring-primary">
            <input name="to" value="{{ $filters['to'] ?? '' }}" type="date" class="bg-surface-container-low border-none rounded-lg text-label-md px-3 py-2 focus:ring-1 focus:ring-primary">
            <div class="flex gap-2">
                <button type="submit" class="bg-surface-container-highest text-on-surface-variant px-4 py-2 rounded-lg text-label-md hover:bg-outline-variant transition-all">Filter</button>
                <a href="{{ route('purchases.create') }}" wire:navigate class="bg-primary text-white px-4 py-2 rounded-lg text-label-md flex items-center gap-2 hover:opacity-90 transition-all">
                    <span class="material-symbols-outlined text-[18px]">add</span> New Purchase
                </a>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-low text-on-surface-variant text-label-sm uppercase">
                    <tr>
                        <th class="px-md py-3">Date</th>
                        <th class="px-md py-3">Invoice No</th>
                        <th class="px-md py-3">Supplier</th>
                        <th class="px-md py-3">Branch</th>
                        <th class="px-md py-3 text-right">Grand Total</th>
                        <th class="px-md py-3 text-right">Paid</th>
                        <th class="px-md py-3 text-right">Due</th>
                        <th class="px-md py-3 text-center">Status</th>
                        <th class="px-md py-3 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse ($purchases as $p)
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="px-md py-md whitespace-nowrap text-body-sm">{{ $p->invoice_date?->format('d M Y') }}</td>
                            <td class="px-md py-md font-bold">{{ $p->purchase_no }}<div class="text-[10px] text-outline">{{ $p->supplier_invoice_no }}</div></td>
                            <td class="px-md py-md">{{ $p->supplier?->name }}</td>
                            <td class="px-md py-md text-label-sm">{{ $p->branch?->name }}</td>
                            <td class="px-md py-md text-right font-bold">Rs. {{ number_format($p->grand_total, 2) }}</td>
                            <td class="px-md py-md text-right">Rs. {{ number_format($p->paid_amount, 2) }}</td>
                            <td class="px-md py-md text-right {{ $p->due_amount > 0 ? 'text-error font-bold' : '' }}">Rs. {{ number_format($p->due_amount, 2) }}</td>
                            <td class="px-md py-md text-center">
                                @php
                                    $badge = match ($p->payment_status) {
                                        'paid' => 'bg-green-100 text-green-700',
                                        'partial' => 'bg-amber-100 text-amber-700',
                                        default => 'bg-red-100 text-red-700',
                                    };
                                @endphp
                                <span class="px-2 py-0.5 {{ $badge }} rounded-full text-[10px] font-bold uppercase">{{ $p->payment_status }}</span>
                            </td>
                            <td class="px-md py-md text-right">
                                <a href="{{ route('purchases.show', $p) }}" class="inline-flex items-center gap-xs text-primary text-label-sm hover:underline" title="View invoice">
                                    <span class="material-symbols-outlined text-[18px]">visibility</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-md py-10 text-center text-outline">No purchase invoices yet. Click "New Purchase" to record one.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-md border-t border-outline-variant">{{ $purchases->links() }}</div>
    </div>
@endsection
