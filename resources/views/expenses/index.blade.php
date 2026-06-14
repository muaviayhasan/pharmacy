@extends('layouts.app')

@section('title', 'Expenses')
@section('page-title', 'Expense Management')

@section('content')
    <div class="grid grid-cols-2 md:grid-cols-4 gap-md">
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Total Approved</p><h3 class="text-headline-md font-bold text-primary">Rs. {{ number_format($stats['total'], 0) }}</h3></div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Pending</p><h3 class="text-headline-md font-bold text-amber-600">Rs. {{ number_format($stats['pending'], 0) }}</h3></div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Today</p><h3 class="text-headline-md font-bold">Rs. {{ number_format($stats['today'], 0) }}</h3></div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Vouchers</p><h3 class="text-headline-md font-bold">{{ number_format($stats['count']) }}</h3></div>
    </div>

    <div class="bg-white rounded-xl border border-outline-variant custom-shadow">
        <form method="GET" class="p-md flex flex-col lg:flex-row lg:items-center gap-md border-b border-outline-variant">
            <div class="relative flex-1">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[20px]">search</span>
                <input name="search" value="{{ $filters['search'] ?? '' }}" type="text" placeholder="Search voucher or title..." class="w-full pl-10 pr-4 py-2 bg-surface-container-low border-none rounded-lg text-body-sm focus:ring-1 focus:ring-primary">
            </div>
            <select name="branch" class="bg-surface-container-low border-none rounded-lg text-label-md px-3 py-2 focus:ring-1 focus:ring-primary">
                <option value="">All Branches</option>
                @foreach ($branches as $b)<option value="{{ $b->id }}" @selected((string) ($filters['branch'] ?? '') === (string) $b->id)>{{ $b->name }}</option>@endforeach
            </select>
            <select name="category" class="bg-surface-container-low border-none rounded-lg text-label-md px-3 py-2 focus:ring-1 focus:ring-primary">
                <option value="">All Categories</option>
                @foreach ($categories as $c)<option value="{{ $c->id }}" @selected((string) ($filters['category'] ?? '') === (string) $c->id)>{{ $c->name }}</option>@endforeach
            </select>
            <select name="status" class="bg-surface-container-low border-none rounded-lg text-label-md px-3 py-2 focus:ring-1 focus:ring-primary">
                <option value="">All Status</option>
                @foreach (['pending', 'approved', 'rejected'] as $s)<option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>{{ ucfirst($s) }}</option>@endforeach
            </select>
            <div class="flex gap-2">
                <button type="submit" class="bg-surface-container-highest text-on-surface-variant px-4 py-2 rounded-lg text-label-md hover:bg-outline-variant">Filter</button>
                <a href="{{ route('expenses.create') }}" class="bg-primary text-white px-4 py-2 rounded-lg text-label-md flex items-center gap-2 hover:opacity-90"><span class="material-symbols-outlined text-[18px]">add</span> Add Expense</a>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-low text-on-surface-variant text-label-sm uppercase">
                    <tr><th class="px-md py-3">Date</th><th class="px-md py-3">Voucher</th><th class="px-md py-3">Category</th><th class="px-md py-3">Title</th><th class="px-md py-3">Branch</th><th class="px-md py-3 text-right">Amount</th><th class="px-md py-3 text-center">Status</th><th class="px-md py-3 text-right">Action</th></tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse ($expenses as $e)
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="px-md py-md text-body-sm">{{ $e->expense_date?->format('d M Y') }}</td>
                            <td class="px-md py-md font-bold">{{ $e->expense_no }}</td>
                            <td class="px-md py-md text-body-sm">{{ $e->category?->name ?? '—' }}</td>
                            <td class="px-md py-md text-body-sm">{{ $e->title }}<div class="text-[11px] text-outline uppercase">{{ $e->payment_method }}</div></td>
                            <td class="px-md py-md text-body-sm">{{ $e->branch?->name }}</td>
                            <td class="px-md py-md text-right font-bold">Rs. {{ number_format($e->total_amount, 2) }}</td>
                            <td class="px-md py-md text-center">
                                @php $b = match ($e->approval_status) { 'approved' => 'bg-green-100 text-green-700', 'rejected' => 'bg-red-100 text-red-700', default => 'bg-amber-100 text-amber-700' }; @endphp
                                <span class="px-2 py-0.5 {{ $b }} rounded-full text-[10px] font-bold uppercase">{{ $e->approval_status }}</span>
                            </td>
                            <td class="px-md py-md text-right">
                                @if ($e->approval_status === 'pending')
                                    <div class="flex justify-end gap-1">
                                        <form method="POST" action="{{ route('expenses.approve', $e) }}">@csrf<button class="px-2 py-1 bg-primary text-white rounded text-[10px] font-bold uppercase hover:opacity-90">Approve</button></form>
                                        <form method="POST" action="{{ route('expenses.reject', $e) }}">@csrf<button class="px-2 py-1 border border-error text-error rounded text-[10px] font-bold uppercase hover:bg-error/5">Reject</button></form>
                                    </div>
                                @else
                                    <span class="text-outline text-[11px]">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-md py-10 text-center text-outline">No expenses recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-md border-t border-outline-variant">{{ $expenses->links() }}</div>
    </div>
@endsection
