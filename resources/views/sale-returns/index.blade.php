@extends('layouts.app')

@section('title', 'Sale Returns')
@section('page-title', 'Sale Return Management')

@section('content')
    <div class="grid grid-cols-3 gap-md">
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Total Returns</p><h3 class="text-headline-md font-bold">{{ number_format($stats['count']) }}</h3></div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Refunded</p><h3 class="text-headline-md font-bold text-error">Rs. {{ number_format($stats['amount'], 0) }}</h3></div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Today</p><h3 class="text-headline-md font-bold">Rs. {{ number_format($stats['today'], 0) }}</h3></div>
    </div>

    <div class="bg-white rounded-xl border border-outline-variant custom-shadow">
        <form method="GET" class="p-md flex items-center gap-md border-b border-outline-variant">
            <div class="relative flex-1">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[20px]">search</span>
                <input name="search" value="{{ $filters['search'] ?? '' }}" type="text" placeholder="Search return or sale no..." class="w-full pl-10 pr-4 py-2 bg-surface-container-low border-none rounded-lg text-body-sm focus:ring-1 focus:ring-primary">
            </div>
            <button type="submit" class="bg-surface-container-highest text-on-surface-variant px-4 py-2 rounded-lg text-label-md hover:bg-outline-variant">Filter</button>
            <a href="{{ route('sale-returns.create') }}" wire:navigate class="bg-primary text-white px-4 py-2 rounded-lg text-label-md flex items-center gap-2 hover:opacity-90"><span class="material-symbols-outlined text-[18px]">add</span> New Return</a>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-low text-on-surface-variant text-label-sm uppercase">
                    <tr><th class="px-md py-3">Return ID</th><th class="px-md py-3">Date</th><th class="px-md py-3">Customer</th><th class="px-md py-3">Original Sale</th><th class="px-md py-3 text-center">Items</th><th class="px-md py-3 text-center">Refund</th><th class="px-md py-3 text-right">Amount</th></tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse ($returns as $r)
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="px-md py-md font-bold">{{ $r->return_no }}</td>
                            <td class="px-md py-md text-body-sm">{{ $r->return_date?->format('d M Y') }}</td>
                            <td class="px-md py-md text-body-sm">{{ $r->customer?->name ?? 'Walk-in' }}</td>
                            <td class="px-md py-md text-body-sm">{{ $r->sale?->sale_no }}</td>
                            <td class="px-md py-md text-center">{{ $r->items_count }}</td>
                            <td class="px-md py-md text-center"><span class="px-2 py-0.5 bg-surface-container-highest rounded-full text-[10px] font-bold uppercase">{{ str_replace('_', ' ', $r->refund_method) }}</span></td>
                            <td class="px-md py-md text-right font-bold text-error">Rs. {{ number_format($r->refund_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-md py-10 text-center text-outline">No sale returns yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-md border-t border-outline-variant">{{ $returns->links() }}</div>
    </div>
@endsection
