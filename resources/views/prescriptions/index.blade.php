@extends('layouts.app')

@section('title', 'Prescriptions')
@section('page-title', 'Prescription Verification')

@section('content')
    @include('partials.flash')

    <div class="grid grid-cols-2 md:grid-cols-4 gap-md">
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Pending</p><h3 class="text-headline-md font-bold text-amber-600">{{ $stats['pending'] }}</h3></div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Verified</p><h3 class="text-headline-md font-bold text-green-600">{{ $stats['verified'] }}</h3></div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Rejected</p><h3 class="text-headline-md font-bold text-error">{{ $stats['rejected'] }}</h3></div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Sales Awaiting Rx</p><h3 class="text-headline-md font-bold">{{ $stats['flagged_sales'] }}</h3></div>
    </div>

    <div class="bg-white rounded-xl border border-outline-variant custom-shadow">
        <form method="GET" class="p-md flex items-center gap-md border-b border-outline-variant">
            <div class="relative flex-1">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[20px]">search</span>
                <input name="search" value="{{ $filters['search'] ?? '' }}" type="text" placeholder="Search Rx no or doctor..." class="w-full pl-10 pr-4 py-2 bg-surface-container-low border-none rounded-lg text-body-sm focus:ring-1 focus:ring-primary">
            </div>
            <select name="status" class="bg-surface-container-low border-none rounded-lg text-label-md px-3 py-2 focus:ring-1 focus:ring-primary">
                <option value="">All Status</option>
                @foreach (['pending', 'verified', 'rejected', 'needs_review', 'manager_approval_required'] as $s)<option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>@endforeach
            </select>
            <button type="submit" class="bg-surface-container-highest text-on-surface-variant px-4 py-2 rounded-lg text-label-md hover:bg-outline-variant">Filter</button>
            <a href="{{ route('prescriptions.create') }}" class="bg-primary text-white px-4 py-2 rounded-lg text-label-md flex items-center gap-2 hover:opacity-90"><span class="material-symbols-outlined text-[18px]">add</span> Log Prescription</a>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-low text-on-surface-variant text-label-sm uppercase">
                    <tr><th class="px-md py-3">Rx No</th><th class="px-md py-3">Date</th><th class="px-md py-3">Customer</th><th class="px-md py-3">Doctor</th><th class="px-md py-3">Invoice</th><th class="px-md py-3 text-center">Status</th><th class="px-md py-3 text-right">Decision</th></tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse ($prescriptions as $rx)
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="px-md py-md font-bold">
                                {{ $rx->prescription_no }}
                                @if ($rx->attachment_path)
                                    <a href="{{ \Illuminate\Support\Facades\Storage::url($rx->attachment_path) }}" target="_blank" class="text-primary inline-flex align-middle ml-1" title="View scan"><span class="material-symbols-outlined text-[16px]">attachment</span></a>
                                @endif
                            </td>
                            <td class="px-md py-md text-body-sm">{{ $rx->prescription_date?->format('d M Y') }}</td>
                            <td class="px-md py-md text-body-sm">{{ $rx->customer?->name ?? 'Walk-in' }}</td>
                            <td class="px-md py-md text-body-sm">{{ $rx->doctor_name ?? '—' }}<div class="text-[11px] text-outline">{{ $rx->clinic_name }}</div></td>
                            <td class="px-md py-md text-body-sm">{{ $rx->sale?->sale_no ?? '—' }}</td>
                            <td class="px-md py-md text-center">
                                @php $b = match ($rx->verification_status) { 'verified' => 'bg-green-100 text-green-700', 'rejected' => 'bg-red-100 text-red-700', 'manager_approval_required' => 'bg-purple-100 text-purple-700', default => 'bg-amber-100 text-amber-700' }; @endphp
                                <span class="px-2 py-0.5 {{ $b }} rounded-full text-[10px] font-bold uppercase">{{ str_replace('_', ' ', $rx->verification_status) }}</span>
                            </td>
                            <td class="px-md py-md text-right">
                                @if (in_array($rx->verification_status, ['pending', 'needs_review', 'manager_approval_required']))
                                    <div class="flex justify-end gap-1" x-data="{ rejecting: false }">
                                        <form method="POST" action="{{ route('prescriptions.verify', $rx) }}">@csrf<button class="px-2 py-1 bg-primary text-white rounded text-[10px] font-bold uppercase hover:opacity-90">Verify</button></form>
                                        <form method="POST" action="{{ route('prescriptions.approve', $rx) }}">@csrf<button class="px-2 py-1 bg-secondary text-white rounded text-[10px] font-bold uppercase hover:opacity-90" title="Manager approval for controlled medicine">Approve</button></form>
                                        <button type="button" @click="rejecting = true" class="px-2 py-1 border border-error text-error rounded text-[10px] font-bold uppercase hover:bg-error/5">Reject</button>
                                        <div x-show="rejecting" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-md" @click.self="rejecting = false">
                                            <form method="POST" action="{{ route('prescriptions.reject', $rx) }}" class="bg-white rounded-xl p-lg w-full max-w-[28rem] space-y-md text-left">
                                                @csrf
                                                <h4 class="text-headline-md font-semibold">Reject {{ $rx->prescription_no }}</h4>
                                                <textarea name="reason" rows="3" required placeholder="Rejection reason..." class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none"></textarea>
                                                <div class="flex justify-end gap-sm">
                                                    <button type="button" @click="rejecting = false" class="px-lg py-2 rounded-lg text-label-md border border-outline-variant">Cancel</button>
                                                    <button type="submit" class="px-lg py-2 rounded-lg text-label-md bg-error text-on-error">Reject</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-outline text-[11px]">{{ $rx->verification_status === 'rejected' ? ($rx->rejection_reason ?? 'Rejected') : 'Completed' }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-md py-10 text-center text-outline">No prescriptions logged yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-md border-t border-outline-variant">{{ $prescriptions->links() }}</div>
    </div>
@endsection
