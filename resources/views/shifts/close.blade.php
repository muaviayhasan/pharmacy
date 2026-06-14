@extends('layouts.app')

@section('title', 'Close Shift')
@section('page-title', 'Close POS Shift')

@section('content')
    <a href="{{ route('shifts.index') }}" class="inline-flex items-center gap-xs text-primary text-label-md hover:underline">
        <span class="material-symbols-outlined text-[20px]">arrow_back</span> Back to Shifts
    </a>
    @include('partials.flash')

    <form method="POST" action="{{ route('shifts.close.store', $shift) }}" class="grid grid-cols-1 lg:grid-cols-3 gap-lg items-start"
          x-data="{ counted: 0, expected: {{ $expected }} }">
        @csrf
        <div class="lg:col-span-2 bg-white rounded-xl border border-outline-variant custom-shadow overflow-hidden">
            <div class="bg-surface-container-low px-md py-sm border-b border-outline-variant">
                <span class="text-label-md font-bold text-primary">{{ $shift->shift_no }} — Cash Reconciliation</span>
            </div>
            <div class="p-lg space-y-md">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-md">
                    <div class="bg-surface-container-low p-md rounded-lg"><p class="text-[10px] uppercase text-outline font-bold">Opening</p><p class="text-headline-md font-bold">Rs. {{ number_format($shift->opening_cash, 0) }}</p></div>
                    <div class="bg-surface-container-low p-md rounded-lg"><p class="text-[10px] uppercase text-outline font-bold">Cash Sales</p><p class="text-headline-md font-bold text-primary">Rs. {{ number_format($shift->cash_sales, 0) }}</p></div>
                    <div class="bg-surface-container-low p-md rounded-lg"><p class="text-[10px] uppercase text-outline font-bold">Refunds</p><p class="text-headline-md font-bold text-error">Rs. {{ number_format($shift->refunds, 0) }}</p></div>
                    <div class="bg-surface-container-low p-md rounded-lg"><p class="text-[10px] uppercase text-outline font-bold">Expenses</p><p class="text-headline-md font-bold">Rs. {{ number_format($shift->expenses, 0) }}</p></div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-md text-body-sm">
                    <div class="flex justify-between p-md bg-surface-container-low rounded-lg"><span class="text-on-surface-variant">Card Sales</span><span class="font-bold">Rs. {{ number_format($shift->card_sales, 0) }}</span></div>
                    <div class="flex justify-between p-md bg-surface-container-low rounded-lg"><span class="text-on-surface-variant">Bank Sales</span><span class="font-bold">Rs. {{ number_format($shift->bank_sales, 0) }}</span></div>
                    <div class="flex justify-between p-md bg-surface-container-low rounded-lg"><span class="text-on-surface-variant">Credit Sales</span><span class="font-bold">Rs. {{ number_format($shift->credit_sales, 0) }}</span></div>
                </div>
                <div>
                    <label class="block text-label-sm font-bold text-on-surface-variant mb-sm">Total Counted Cash (Rs.) <span class="text-error">*</span></label>
                    <input name="counted_cash" x-model.number="counted" type="number" step="0.01" min="0" value="{{ old('counted_cash', 0) }}" required
                           class="w-full border-2 border-primary/20 rounded-lg p-md text-headline-md font-bold focus:border-primary outline-none">
                </div>
                <div>
                    <label class="block text-label-sm font-bold text-on-surface-variant mb-sm">Closing Note</label>
                    <textarea name="notes" rows="2" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border-2 border-primary/20 shadow-lg overflow-hidden">
            <div class="bg-primary text-on-primary px-md py-sm flex justify-between items-center"><span class="text-label-md font-bold">Shift Final Preview</span><span class="material-symbols-outlined">savings</span></div>
            <div class="p-md space-y-md">
                <div class="flex justify-between text-body-sm"><span class="text-on-surface-variant">Expected Cash</span><span class="font-bold">Rs. {{ number_format($expected, 2) }}</span></div>
                <div class="flex justify-between text-body-sm"><span class="text-on-surface-variant">Counted Cash</span><span class="font-bold" x-text="'Rs. ' + counted.toLocaleString()"></span></div>
                <div class="flex justify-between items-end pt-sm border-t border-dashed border-outline-variant">
                    <span class="text-label-md font-bold">Difference</span>
                    <span class="text-headline-md font-bold" :class="(counted - expected) < 0 ? 'text-error' : ((counted - expected) > 0 ? 'text-secondary' : 'text-primary')"
                          x-text="((counted - expected) >= 0 ? '+' : '') + 'Rs. ' + (counted - expected).toFixed(2)"></span>
                </div>
                <p class="text-[11px] text-outline" x-show="(counted - expected) < 0" x-cloak>⚠ Cash shortage will be flagged for manager approval.</p>
                <button type="submit" class="w-full py-3 bg-primary-container text-white rounded-lg font-bold hover:brightness-110 active:scale-95 transition-all flex items-center justify-center gap-sm">
                    <span class="material-symbols-outlined">lock</span> Close & Submit
                </button>
            </div>
        </div>
    </form>
@endsection
