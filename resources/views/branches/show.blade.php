@extends('layouts.app')

@section('title', $branch->name)
@section('page-title', 'Branch Detail')

@section('content')
    <div class="flex items-center justify-between">
        <a href="{{ route('branches.index') }}" class="inline-flex items-center gap-xs text-primary text-label-md hover:underline">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span> Back to Branches
        </a>
        <a href="{{ route('branches.edit', $branch) }}" class="px-md py-2 bg-primary text-on-primary rounded-lg text-label-md hover:opacity-90 flex items-center gap-xs"><span class="material-symbols-outlined text-[18px]">edit</span> Edit</a>
    </div>

    <div class="bg-white rounded-xl border border-outline-variant custom-shadow p-lg flex items-start gap-md">
        <div class="w-12 h-12 bg-primary-container rounded-lg flex items-center justify-center text-on-primary-container"><span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">store</span></div>
        <div class="flex-1">
            <div class="flex items-center gap-sm"><h2 class="text-headline-md font-bold">{{ $branch->name }}</h2><span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $branch->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-surface-variant text-on-surface-variant' }}">{{ $branch->status }}</span></div>
            <p class="text-body-sm text-outline">{{ $branch->code }} • {{ ucfirst($branch->type) }} • {{ $branch->city ?? '—' }}</p>
            <p class="text-body-sm text-on-surface-variant mt-1">Manager: {{ $branch->manager?->name ?? 'Unassigned' }} {{ $branch->phone ? '• '.$branch->phone : '' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-md">
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Total Sales</p><h3 class="text-headline-md font-bold text-primary">Rs. {{ number_format($salesTotal, 0) }}</h3></div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Total Purchases</p><h3 class="text-headline-md font-bold text-secondary">Rs. {{ number_format($purchaseTotal, 0) }}</h3></div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Stock Value</p><h3 class="text-headline-md font-bold">Rs. {{ number_format($stockValue, 0) }}</h3></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-lg">
        <div class="bg-white rounded-xl border border-outline-variant custom-shadow overflow-hidden">
            <div class="px-lg py-md border-b border-outline-variant"><h4 class="text-label-md font-bold">Assigned Staff ({{ $branch->users->count() }})</h4></div>
            <div class="divide-y divide-outline-variant">
                @forelse ($branch->users as $u)
                    <div class="px-lg py-sm flex items-center justify-between"><span class="text-body-sm">{{ $u->name }}</span><span class="text-[11px] text-outline uppercase">{{ \Illuminate\Support\Str::headline($u->getRoleNames()->first() ?? '—') }}</span></div>
                @empty
                    <p class="px-lg py-md text-body-sm text-outline">No staff assigned.</p>
                @endforelse
            </div>
        </div>
        <div class="bg-white rounded-xl border border-outline-variant custom-shadow overflow-hidden">
            <div class="px-lg py-md border-b border-outline-variant"><h4 class="text-label-md font-bold">Accounts & Counters</h4></div>
            <div class="p-lg space-y-sm">
                <p class="text-label-sm text-outline uppercase">POS Counters</p>
                @forelse ($branch->posCounters as $c)<div class="text-body-sm flex justify-between"><span>{{ $c->name }}</span><span class="text-outline">{{ $c->code }}</span></div>@empty<p class="text-body-sm text-outline">No counters.</p>@endforelse
                <p class="text-label-sm text-outline uppercase pt-sm">Accounts</p>
                @forelse ($branch->accounts as $a)<div class="text-body-sm flex justify-between"><span>{{ $a->name }} ({{ ucfirst($a->type) }})</span><span class="font-bold">Rs. {{ number_format($a->current_balance, 0) }}</span></div>@empty<p class="text-body-sm text-outline">No accounts.</p>@endforelse
            </div>
        </div>
    </div>
@endsection
