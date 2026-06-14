@extends('layouts.app')

@section('title', 'Purchase '.$purchase->purchase_no)
@section('page-title', 'Purchase Invoice')

@push('styles')
<style>
    @media print {
        aside, header, footer, .no-print { display: none !important; }
        main { padding-left: 0 !important; }
        section { overflow: visible !important; padding: 0 !important; }
        .print-card { border: none !important; box-shadow: none !important; }
    }
</style>
@endpush

@section('content')
    <div class="flex items-center justify-between no-print">
        <a href="{{ route('purchases.index') }}" class="inline-flex items-center gap-xs text-primary text-label-md hover:underline">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span> Back to Purchases
        </a>
        <div class="flex gap-sm">
            <a href="{{ route('purchases.create') }}" wire:navigate class="px-md py-2 border border-outline-variant rounded-lg text-label-md hover:bg-surface-container-low flex items-center gap-xs">
                <span class="material-symbols-outlined text-[18px]">add</span> New Purchase
            </a>
            <button onclick="window.print()" class="px-md py-2 bg-primary text-on-primary rounded-lg text-label-md hover:opacity-90 flex items-center gap-xs">
                <span class="material-symbols-outlined text-[18px]">print</span> Print
            </button>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-outline-variant custom-shadow overflow-hidden print-card max-w-5xl">
        {{-- Invoice header --}}
        <div class="p-lg border-b border-outline-variant flex flex-col md:flex-row md:items-start md:justify-between gap-lg">
            <div class="flex items-start gap-md">
                <div class="w-12 h-12 bg-primary-container rounded-lg flex items-center justify-center text-on-primary-container">
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">shopping_cart</span>
                </div>
                <div>
                    <h2 class="text-headline-md font-bold text-on-surface">{{ $purchase->purchase_no }}</h2>
                    <p class="text-body-sm text-outline">Supplier invoice: {{ $purchase->supplier_invoice_no ?? '—' }}</p>
                    @php
                        $badge = match ($purchase->payment_status) {
                            'paid' => 'bg-green-100 text-green-700',
                            'partial' => 'bg-amber-100 text-amber-700',
                            default => 'bg-red-100 text-red-700',
                        };
                    @endphp
                    <span class="mt-1 inline-block px-2 py-0.5 {{ $badge }} rounded-full text-[11px] font-bold uppercase">{{ $purchase->payment_status }}</span>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-x-lg gap-y-xs text-body-sm">
                <span class="text-outline">Invoice Date</span><span class="font-medium text-right">{{ $purchase->invoice_date?->format('d M Y') }}</span>
                <span class="text-outline">Due Date</span><span class="font-medium text-right">{{ $purchase->due_date?->format('d M Y') ?? '—' }}</span>
                <span class="text-outline">Branch</span><span class="font-medium text-right">{{ $purchase->branch?->name }}</span>
                <span class="text-outline">Created By</span><span class="font-medium text-right">{{ $purchase->creator?->name ?? '—' }}</span>
            </div>
        </div>

        {{-- Supplier --}}
        <div class="p-lg border-b border-outline-variant grid grid-cols-1 md:grid-cols-2 gap-lg">
            <div>
                <p class="text-label-sm text-outline uppercase tracking-wider mb-xs">Supplier</p>
                <p class="text-label-md font-bold text-on-surface">{{ $purchase->supplier?->name }}</p>
                <p class="text-body-sm text-on-surface-variant">{{ $purchase->supplier?->phone ?? '' }} {{ $purchase->supplier?->city ? '• '.$purchase->supplier->city : '' }}</p>
                <p class="text-body-sm text-error font-bold mt-1">Current payable: Rs. {{ number_format($purchase->supplier?->current_balance ?? 0, 2) }}</p>
            </div>
            @if ($purchase->notes)
                <div>
                    <p class="text-label-sm text-outline uppercase tracking-wider mb-xs">Notes</p>
                    <p class="text-body-sm text-on-surface-variant">{{ $purchase->notes }}</p>
                </div>
            @endif
        </div>

        {{-- Items --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-low text-on-surface-variant text-label-sm uppercase tracking-wider">
                    <tr>
                        <th class="px-lg py-sm">Medicine</th>
                        <th class="px-lg py-sm">Batch</th>
                        <th class="px-lg py-sm">Expiry</th>
                        <th class="px-lg py-sm text-right">Qty</th>
                        <th class="px-lg py-sm text-right">Bonus</th>
                        <th class="px-lg py-sm text-right">Cost</th>
                        <th class="px-lg py-sm text-right">Sale</th>
                        <th class="px-lg py-sm text-right">Tax</th>
                        <th class="px-lg py-sm text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant text-body-sm">
                    @foreach ($purchase->items as $item)
                        <tr>
                            <td class="px-lg py-md">
                                <div class="font-label-md text-on-surface">{{ $item->medicine?->name }}</div>
                                <div class="text-[11px] text-outline">{{ $item->medicine?->generic_name }}</div>
                            </td>
                            <td class="px-lg py-md">{{ $item->batch_no }}</td>
                            <td class="px-lg py-md">{{ $item->expiry_date?->format('m/Y') }}</td>
                            <td class="px-lg py-md text-right">{{ $item->quantity }}</td>
                            <td class="px-lg py-md text-right">{{ $item->bonus_quantity }}</td>
                            <td class="px-lg py-md text-right">Rs. {{ number_format($item->purchase_price, 2) }}</td>
                            <td class="px-lg py-md text-right">Rs. {{ number_format($item->sale_price, 2) }}</td>
                            <td class="px-lg py-md text-right">Rs. {{ number_format($item->tax, 2) }}</td>
                            <td class="px-lg py-md text-right font-bold text-primary">Rs. {{ number_format($item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Totals + payments --}}
        <div class="p-lg grid grid-cols-1 md:grid-cols-2 gap-lg border-t border-outline-variant">
            <div>
                @if ($payments->isNotEmpty())
                    <p class="text-label-sm text-outline uppercase tracking-wider mb-sm">Payments Made</p>
                    <div class="space-y-xs">
                        @foreach ($payments as $pay)
                            <div class="flex justify-between items-center text-body-sm p-sm bg-surface-container-low rounded-lg">
                                <span class="flex items-center gap-xs"><span class="material-symbols-outlined text-[18px] text-primary">payments</span> {{ ucfirst($pay->method) }} • {{ $pay->payment_date?->format('d M Y') }}</span>
                                <span class="font-bold">Rs. {{ number_format($pay->amount, 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-body-sm text-outline">No payments recorded against this invoice.</p>
                @endif
            </div>
            <div class="space-y-sm">
                <div class="flex justify-between text-body-sm"><span class="text-on-surface-variant">Subtotal</span><span class="font-medium">Rs. {{ number_format($purchase->subtotal, 2) }}</span></div>
                <div class="flex justify-between text-body-sm"><span class="text-on-surface-variant">Discount</span><span class="font-medium text-error">- Rs. {{ number_format($purchase->discount, 2) }}</span></div>
                <div class="flex justify-between text-body-sm"><span class="text-on-surface-variant">Tax</span><span class="font-medium">Rs. {{ number_format($purchase->tax, 2) }}</span></div>
                <div class="flex justify-between items-end pt-sm border-t border-dashed border-outline-variant">
                    <span class="font-headline-md text-on-surface">Grand Total</span>
                    <span class="font-headline-md font-bold text-primary">Rs. {{ number_format($purchase->grand_total, 2) }}</span>
                </div>
                <div class="flex justify-between text-body-sm pt-sm"><span class="text-on-surface-variant">Paid</span><span class="font-medium text-primary">Rs. {{ number_format($purchase->paid_amount, 2) }}</span></div>
                <div class="flex justify-between text-body-sm"><span class="text-on-surface-variant">Balance Due</span><span class="font-bold {{ $purchase->due_amount > 0 ? 'text-error' : '' }}">Rs. {{ number_format($purchase->due_amount, 2) }}</span></div>
            </div>
        </div>
    </div>
@endsection
