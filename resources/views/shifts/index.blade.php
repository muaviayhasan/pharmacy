@extends('layouts.app')

@section('title', 'Shift Management')
@section('page-title', 'Shift Management')

@section('content')
    <div class="grid grid-cols-3 gap-md">
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Open Shifts</p><h3 class="text-headline-md font-bold text-green-600">{{ $stats['open'] }}</h3></div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Pending Approval</p><h3 class="text-headline-md font-bold text-amber-600">{{ $stats['pending'] }}</h3></div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Expected Cash (today)</p><h3 class="text-headline-md font-bold">Rs. {{ number_format($stats['expected_today'], 0) }}</h3></div>
    </div>

    <div class="bg-white rounded-xl border border-outline-variant custom-shadow">
        <form method="GET" class="p-md flex items-center gap-md border-b border-outline-variant">
            <div class="relative flex-1">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[20px]">search</span>
                <input name="search" value="{{ $filters['search'] ?? '' }}" type="text" placeholder="Search shift no..." class="w-full pl-10 pr-4 py-2 bg-surface-container-low border-none rounded-lg text-body-sm focus:ring-1 focus:ring-primary">
            </div>
            <select name="status" class="bg-surface-container-low border-none rounded-lg text-label-md px-3 py-2 focus:ring-1 focus:ring-primary">
                <option value="">All Status</option>
                @foreach (['open', 'pending_approval', 'approved', 'rejected'] as $s)<option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>@endforeach
            </select>
            <button type="submit" class="bg-surface-container-highest text-on-surface-variant px-4 py-2 rounded-lg text-label-md hover:bg-outline-variant">Filter</button>
            <a href="{{ route('shifts.create') }}" class="bg-primary text-white px-4 py-2 rounded-lg text-label-md flex items-center gap-2 hover:opacity-90"><span class="material-symbols-outlined text-[18px]">point_of_sale</span> Open Shift</a>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-low text-on-surface-variant text-label-sm uppercase">
                    <tr><th class="px-md py-3">Shift No</th><th class="px-md py-3">Cashier</th><th class="px-md py-3">Branch / Counter</th><th class="px-md py-3 text-right">Opening</th><th class="px-md py-3 text-right">Expected</th><th class="px-md py-3 text-right">Difference</th><th class="px-md py-3 text-center">Status</th><th class="px-md py-3 text-right">Action</th></tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse ($shifts as $s)
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="px-md py-md font-bold">{{ $s->shift_no }}<div class="text-[11px] text-outline">{{ $s->opened_at?->format('d M, h:i A') }}</div></td>
                            <td class="px-md py-md text-body-sm">{{ $s->cashier?->name }}</td>
                            <td class="px-md py-md text-body-sm">{{ $s->branch?->name }}<div class="text-[11px] text-outline">{{ $s->posCounter?->name ?? 'No counter' }}</div></td>
                            <td class="px-md py-md text-right text-body-sm">Rs. {{ number_format($s->opening_cash, 0) }}</td>
                            <td class="px-md py-md text-right font-bold">Rs. {{ number_format($s->expected_cash, 0) }}</td>
                            <td class="px-md py-md text-right font-bold {{ $s->cash_difference < 0 ? 'text-error' : ($s->cash_difference > 0 ? 'text-secondary' : 'text-outline') }}">
                                {{ $s->status === 'open' ? '—' : ($s->cash_difference == 0 ? 'Balanced' : (($s->cash_difference > 0 ? '+' : '').'Rs. '.number_format($s->cash_difference, 0))) }}
                            </td>
                            <td class="px-md py-md text-center">
                                @php $b = match ($s->status) { 'open' => 'bg-green-100 text-green-700', 'approved' => 'bg-blue-100 text-blue-700', 'rejected' => 'bg-red-100 text-red-700', default => 'bg-amber-100 text-amber-700' }; @endphp
                                <span class="px-2 py-0.5 {{ $b }} rounded-full text-[10px] font-bold uppercase">{{ str_replace('_', ' ', $s->status) }}</span>
                            </td>
                            <td class="px-md py-md text-right">
                                @if ($s->status === 'open')
                                    <a href="{{ route('shifts.close', $s) }}" class="px-2 py-1 bg-primary text-white rounded text-[10px] font-bold uppercase hover:opacity-90">Close</a>
                                @elseif ($s->status === 'pending_approval')
                                    <div class="flex justify-end gap-1">
                                        <form method="POST" action="{{ route('shifts.approve', $s) }}">@csrf<button class="px-2 py-1 bg-primary text-white rounded text-[10px] font-bold uppercase hover:opacity-90">Approve</button></form>
                                        <form method="POST" action="{{ route('shifts.reject', $s) }}">@csrf<button class="px-2 py-1 border border-error text-error rounded text-[10px] font-bold uppercase hover:bg-error/5">Reject</button></form>
                                    </div>
                                @else
                                    <span class="text-outline text-[11px]">{{ $s->approver?->name ? 'by '.$s->approver->name : '—' }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-md py-10 text-center text-outline">No shifts yet. Open one to start selling at the POS.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-md border-t border-outline-variant">{{ $shifts->links() }}</div>
    </div>
@endsection
