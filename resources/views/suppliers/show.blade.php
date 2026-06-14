@extends('layouts.app')

@section('title', $supplier->name)
@section('page-title', 'Supplier Ledger')

@section('content')
    <div class="flex items-center justify-between">
        <a href="{{ route('suppliers.index') }}" class="inline-flex items-center gap-xs text-primary text-label-md hover:underline">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span> Back to Suppliers
        </a>
        <div class="flex gap-sm">
            <a href="{{ route('ledger.index') }}" class="px-md py-2 border border-outline-variant rounded-lg text-label-md hover:bg-surface-container-low flex items-center gap-xs"><span class="material-symbols-outlined text-[18px]">add_card</span> Record Payment</a>
            <a href="{{ route('purchases.create') }}" class="px-md py-2 bg-primary text-on-primary rounded-lg text-label-md hover:opacity-90 flex items-center gap-xs"><span class="material-symbols-outlined text-[18px]">add_shopping_cart</span> New Purchase</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-lg">
        <div class="bg-inverse-surface text-surface-bright rounded-xl p-lg lg:col-span-1 space-y-md">
            <div>
                <h3 class="text-label-sm text-primary-fixed-dim uppercase tracking-wider">Supplier</h3>
                <p class="text-headline-md font-bold">{{ $supplier->name }}</p>
                <p class="text-body-sm opacity-70">{{ $supplier->contact_person ?? '' }} {{ $supplier->phone ? '• '.$supplier->phone : '' }}</p>
            </div>
            <div class="space-y-sm pt-md border-t border-surface-variant/20">
                <div class="flex justify-between text-body-sm"><span class="opacity-70">Opening Balance</span><span class="font-bold">Rs. {{ number_format($supplier->opening_balance, 2) }}</span></div>
                <div class="flex justify-between text-body-sm"><span class="opacity-70">Total Paid (debit)</span><span class="font-bold text-primary-fixed-dim">Rs. {{ number_format($totalDebit, 2) }}</span></div>
                <div class="flex justify-between text-body-sm"><span class="opacity-70">Total Purchased (credit)</span><span class="font-bold text-error-container">Rs. {{ number_format($totalCredit, 2) }}</span></div>
                <div class="flex justify-between pt-sm border-t border-surface-variant/20"><span class="text-label-md font-bold">Payable</span><span class="text-headline-md font-bold text-primary-fixed-dim">Rs. {{ number_format($supplier->current_balance, 2) }}</span></div>
            </div>
            <div class="flex justify-between text-body-sm pt-md border-t border-surface-variant/20"><span class="opacity-70">Payment Terms</span><span class="font-bold">{{ $supplier->payment_terms ?? '—' }}</span></div>
        </div>

        <div class="lg:col-span-3 bg-white rounded-xl border border-outline-variant custom-shadow overflow-hidden">
            <div class="px-lg py-md border-b border-outline-variant"><h4 class="text-headline-md font-semibold">Ledger Entries</h4></div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-surface-container-low text-on-surface-variant text-label-sm uppercase">
                        <tr><th class="px-md py-3">Date</th><th class="px-md py-3">Voucher</th><th class="px-md py-3">Description</th><th class="px-md py-3 text-right">Debit (Paid)</th><th class="px-md py-3 text-right">Credit (Purchased)</th><th class="px-md py-3 text-right">Balance</th></tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        @forelse ($entries as $e)
                            <tr class="hover:bg-surface-container-low transition-colors">
                                <td class="px-md py-md text-body-sm">{{ $e->transaction_date?->format('d M Y') }}</td>
                                <td class="px-md py-md"><span class="font-bold text-body-sm">{{ $e->voucher_no ?? '—' }}</span><div class="text-[10px] uppercase text-outline">{{ $e->voucher_type }}</div></td>
                                <td class="px-md py-md text-body-sm">{{ $e->description }}</td>
                                <td class="px-md py-md text-right font-bold text-primary">{{ $e->debit > 0 ? 'Rs. '.number_format($e->debit, 2) : '—' }}</td>
                                <td class="px-md py-md text-right font-bold text-error">{{ $e->credit > 0 ? 'Rs. '.number_format($e->credit, 2) : '—' }}</td>
                                <td class="px-md py-md text-right font-bold">Rs. {{ number_format($e->balance, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-md py-10 text-center text-outline">No ledger activity yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-md py-md border-t border-outline-variant">{{ $entries->links() }}</div>
        </div>
    </div>
@endsection
