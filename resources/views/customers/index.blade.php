@extends('layouts.app')

@section('title', 'Customers')
@section('page-title', 'Customer Management')

@section('content')
    <div class="grid grid-cols-2 md:grid-cols-4 gap-md">
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Total Customers</p><h3 class="text-headline-md font-bold">{{ number_format($stats['total']) }}</h3></div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Active</p><h3 class="text-headline-md font-bold text-green-600">{{ number_format($stats['active']) }}</h3></div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Credit Accounts</p><h3 class="text-headline-md font-bold">{{ number_format($stats['credit']) }}</h3></div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Total Receivable</p><h3 class="text-headline-md font-bold text-primary">Rs. {{ number_format($stats['receivable'], 0) }}</h3></div>
    </div>

    <div class="bg-white rounded-xl border border-outline-variant custom-shadow">
        <form method="GET" class="p-md flex flex-col lg:flex-row lg:items-center gap-md border-b border-outline-variant">
            <div class="relative flex-1">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[20px]">search</span>
                <input name="search" value="{{ $filters['search'] ?? '' }}" type="text" placeholder="Search customers by name, phone, or email..." class="w-full pl-10 pr-4 py-2 bg-surface-container-low border-none rounded-lg text-body-sm focus:ring-1 focus:ring-primary">
            </div>
            <select name="type" class="bg-surface-container-low border-none rounded-lg text-label-md px-3 py-2 focus:ring-1 focus:ring-primary">
                <option value="">All Types</option>
                @foreach (['walk_in' => 'Walk-in', 'regular' => 'Regular', 'credit' => 'Credit', 'corporate' => 'Corporate'] as $k => $v)<option value="{{ $k }}" @selected(($filters['type'] ?? '') === $k)>{{ $v }}</option>@endforeach
            </select>
            <select name="status" class="bg-surface-container-low border-none rounded-lg text-label-md px-3 py-2 focus:ring-1 focus:ring-primary">
                <option value="">All Status</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
            </select>
            <div class="flex gap-2">
                <button type="submit" class="bg-surface-container-highest text-on-surface-variant px-4 py-2 rounded-lg text-label-md hover:bg-outline-variant">Filter</button>
                <a href="{{ route('customers.create') }}" class="bg-primary text-white px-4 py-2 rounded-lg text-label-md flex items-center gap-2 hover:opacity-90"><span class="material-symbols-outlined text-[18px]">person_add</span> Add Customer</a>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-low text-on-surface-variant text-label-sm uppercase">
                    <tr><th class="px-md py-3">Name</th><th class="px-md py-3">Type</th><th class="px-md py-3">Phone</th><th class="px-md py-3 text-right">Credit Limit</th><th class="px-md py-3 text-right">Balance Due</th><th class="px-md py-3 text-center">Status</th><th class="px-md py-3 text-right">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse ($customers as $c)
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="px-md py-md"><div class="font-label-md text-on-surface">{{ $c->name }}</div><div class="text-[11px] text-outline">{{ $c->sales_count }} sale(s)</div></td>
                            <td class="px-md py-md"><span class="text-label-sm capitalize">{{ str_replace('_', '-', $c->customer_type) }}</span></td>
                            <td class="px-md py-md text-body-sm">{{ $c->phone ?? '—' }}</td>
                            <td class="px-md py-md text-right text-body-sm">Rs. {{ number_format($c->credit_limit, 0) }}</td>
                            <td class="px-md py-md text-right font-bold {{ $c->current_balance > 0 ? 'text-error' : '' }}">Rs. {{ number_format($c->current_balance, 0) }}</td>
                            <td class="px-md py-md text-center"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $c->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-surface-variant text-on-surface-variant' }}">{{ $c->status }}</span></td>
                            <td class="px-md py-md text-right">
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('customers.show', $c) }}" class="p-1.5 text-outline hover:text-primary" title="Ledger"><span class="material-symbols-outlined text-[18px]">account_balance_wallet</span></a>
                                    <a href="{{ route('customers.edit', $c) }}" class="p-1.5 text-outline hover:text-primary" title="Edit"><span class="material-symbols-outlined text-[18px]">edit</span></a>
                                    <form method="POST" action="{{ route('customers.destroy', $c) }}" onsubmit="return confirm('Delete this customer?');">@csrf @method('DELETE')
                                        <button class="p-1.5 text-outline hover:text-error" title="Delete"><span class="material-symbols-outlined text-[18px]">delete</span></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-md py-10 text-center text-outline">No customers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-md border-t border-outline-variant">{{ $customers->links() }}</div>
    </div>
@endsection
