@extends('layouts.app')

@section('title', 'Add Expense')
@section('page-title', 'Add New Expense')

@section('content')
    <a href="{{ route('expenses.index') }}" class="inline-flex items-center gap-xs text-primary text-label-md hover:underline">
        <span class="material-symbols-outlined text-[20px]">arrow_back</span> Back to Expenses
    </a>
    @include('partials.flash')

    <form method="POST" action="{{ route('expenses.store') }}" class="max-w-3xl">
        @csrf
        <div class="bg-white rounded-xl border border-outline-variant custom-shadow overflow-hidden">
            <div class="bg-surface-container-low px-md py-sm border-b border-outline-variant">
                <span class="text-label-md font-bold text-primary flex items-center gap-xs"><span class="material-symbols-outlined text-[18px]">receipt_long</span> Expense Voucher</span>
            </div>
            <div class="p-lg grid grid-cols-1 md:grid-cols-2 gap-lg">
                <div class="space-y-sm">
                    <label class="block text-label-sm font-bold text-on-surface-variant">Branch <span class="text-error">*</span></label>
                    <select name="branch_id" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                        @foreach ($branches as $b)<option value="{{ $b->id }}" @selected((int) old('branch_id', $activeBranchId) === $b->id)>{{ $b->name }}</option>@endforeach
                    </select>
                </div>
                <div class="space-y-sm">
                    <label class="block text-label-sm font-bold text-on-surface-variant">Category</label>
                    <select name="category_id" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                        <option value="">Select category</option>
                        @foreach ($categories as $c)<option value="{{ $c->id }}" @selected((string) old('category_id') === (string) $c->id)>{{ $c->name }}</option>@endforeach
                    </select>
                </div>
                <div class="space-y-sm">
                    <label class="block text-label-sm font-bold text-on-surface-variant">Date <span class="text-error">*</span></label>
                    <input name="expense_date" type="date" value="{{ old('expense_date', now()->toDateString()) }}" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                </div>
                <div class="space-y-sm">
                    <label class="block text-label-sm font-bold text-on-surface-variant">Title <span class="text-error">*</span></label>
                    <input name="title" type="text" value="{{ old('title') }}" placeholder="e.g. October electricity bill" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                </div>
                <div class="space-y-sm">
                    <label class="block text-label-sm font-bold text-on-surface-variant">Amount (Rs.) <span class="text-error">*</span></label>
                    <input name="amount" type="number" step="0.01" min="0" value="{{ old('amount') }}" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                </div>
                <div class="space-y-sm">
                    <label class="block text-label-sm font-bold text-on-surface-variant">Tax (Rs.)</label>
                    <input name="tax" type="number" step="0.01" min="0" value="{{ old('tax', 0) }}" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                </div>
                <div class="space-y-sm">
                    <label class="block text-label-sm font-bold text-on-surface-variant">Payment Method</label>
                    <select name="payment_method" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                        @foreach (['cash', 'card', 'bank', 'cheque'] as $m)<option value="{{ $m }}" @selected(old('payment_method', 'cash') === $m)>{{ ucfirst($m) }}</option>@endforeach
                    </select>
                </div>
                <div class="space-y-sm">
                    <label class="block text-label-sm font-bold text-on-surface-variant">Paid From Account</label>
                    <select name="payment_account_id" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                        <option value="">None</option>
                        @foreach ($accounts as $a)<option value="{{ $a->id }}" @selected((string) old('payment_account_id') === (string) $a->id)>{{ $a->name }} ({{ ucfirst($a->type) }})</option>@endforeach
                    </select>
                </div>
                <div class="space-y-sm md:col-span-2">
                    <label class="block text-label-sm font-bold text-on-surface-variant">Description</label>
                    <textarea name="description" rows="2" placeholder="Brief details about the expense..." class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">{{ old('description') }}</textarea>
                </div>
            </div>
            <div class="px-lg py-md bg-surface-container-low flex justify-end gap-sm">
                <a href="{{ route('expenses.index') }}" class="px-lg py-2.5 rounded-lg text-label-md border border-outline-variant text-on-surface-variant hover:bg-surface-container">Cancel</a>
                <button type="submit" class="px-lg py-2.5 rounded-lg text-label-md bg-primary text-on-primary hover:opacity-90 flex items-center gap-sm"><span class="material-symbols-outlined text-[18px]">send</span> Save &amp; Submit Voucher</button>
            </div>
        </div>
    </form>
@endsection
