@extends('layouts.app')

@section('title', 'Stock Adjustments')
@section('page-title', 'Stock Adjustments')

@section('content')
    <div class="grid grid-cols-3 gap-md">
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Pending</p><h3 class="text-headline-md font-bold text-amber-600">{{ $stats['pending'] }}</h3></div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Completed</p><h3 class="text-headline-md font-bold text-green-600">{{ $stats['completed'] }}</h3></div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Rejected</p><h3 class="text-headline-md font-bold text-error">{{ $stats['rejected'] }}</h3></div>
    </div>

    <div class="bg-white rounded-xl border border-outline-variant custom-shadow">
        <form method="GET" class="p-md flex flex-col lg:flex-row lg:items-center gap-md border-b border-outline-variant">
            <div class="relative flex-1">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[20px]">search</span>
                <input name="search" value="{{ $filters['search'] ?? '' }}" type="text" placeholder="Search adjustment no..." class="w-full pl-10 pr-4 py-2 bg-surface-container-low border-none rounded-lg text-body-sm focus:ring-1 focus:ring-primary">
            </div>
            <select name="status" class="bg-surface-container-low border-none rounded-lg text-label-md px-3 py-2 focus:ring-1 focus:ring-primary">
                <option value="">All Status</option>
                @foreach (['pending', 'completed', 'rejected'] as $st)<option value="{{ $st }}" @selected(($filters['status'] ?? '') === $st)>{{ ucfirst($st) }}</option>@endforeach
            </select>
            <div class="flex gap-2">
                <button type="submit" class="bg-surface-container-highest text-on-surface-variant px-4 py-2 rounded-lg text-label-md hover:bg-outline-variant">Filter</button>
                <a href="{{ route('stock-adjustments.create') }}" wire:navigate class="bg-primary text-white px-4 py-2 rounded-lg text-label-md flex items-center gap-2 hover:opacity-90"><span class="material-symbols-outlined text-[18px]">add</span> New Adjustment</a>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-low text-on-surface-variant text-label-sm uppercase">
                    <tr><th class="px-md py-3">Adjustment</th><th class="px-md py-3">Date</th><th class="px-md py-3">Branch</th><th class="px-md py-3">Type</th><th class="px-md py-3 text-center">Items</th><th class="px-md py-3">Requested By</th><th class="px-md py-3 text-center">Status</th><th class="px-md py-3 text-right">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse ($adjustments as $a)
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="px-md py-md font-bold">{{ $a->adjustment_no }}</td>
                            <td class="px-md py-md text-body-sm">{{ $a->adjustment_date?->format('d M Y') }}</td>
                            <td class="px-md py-md text-body-sm">{{ $a->branch?->name }}</td>
                            <td class="px-md py-md text-body-sm capitalize">{{ $a->adjustment_type }}</td>
                            <td class="px-md py-md text-center">{{ $a->items_count }}</td>
                            <td class="px-md py-md text-body-sm">{{ $a->creator?->name }}</td>
                            <td class="px-md py-md text-center">
                                @php $b = match ($a->status) { 'completed' => 'bg-green-100 text-green-700', 'rejected' => 'bg-red-100 text-red-700', default => 'bg-amber-100 text-amber-700' }; @endphp
                                <span class="px-2 py-0.5 {{ $b }} rounded-full text-[10px] font-bold uppercase">{{ $a->status }}</span>
                            </td>
                            <td class="px-md py-md text-right">
                                @if ($a->status === 'pending')
                                    <div class="flex justify-end gap-1">
                                        <form method="POST" action="{{ route('stock-adjustments.approve', $a) }}" onsubmit="return confirm('Approve and apply stock changes?');">@csrf
                                            <button class="px-2 py-1 bg-primary text-white rounded text-[10px] font-bold uppercase hover:opacity-90">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('stock-adjustments.reject', $a) }}">@csrf
                                            <button class="px-2 py-1 border border-error text-error rounded text-[10px] font-bold uppercase hover:bg-error/5">Reject</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-outline text-[11px]">{{ $a->approver?->name ? 'by '.$a->approver->name : '—' }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-md py-10 text-center text-outline">No adjustments yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-md border-t border-outline-variant">{{ $adjustments->links() }}</div>
    </div>
@endsection
