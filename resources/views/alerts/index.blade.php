@extends('layouts.app')

@section('title', 'Alerts')
@section('page-title', 'Alert Center')

@section('content')
    @include('partials.flash')

    <div class="flex items-center justify-between">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-md flex-1">
            <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Open Alerts</p><h3 class="text-headline-md font-bold">{{ $stats['open'] }}</h3></div>
            <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Critical</p><h3 class="text-headline-md font-bold text-error">{{ $stats['critical'] }}</h3></div>
            <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Expiry</p><h3 class="text-headline-md font-bold text-amber-600">{{ $stats['expiry'] }}</h3></div>
            <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Payments</p><h3 class="text-headline-md font-bold text-secondary">{{ $stats['payments'] }}</h3></div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-outline-variant custom-shadow">
        <form method="GET" class="p-md flex flex-col lg:flex-row lg:items-center gap-md border-b border-outline-variant">
            <h4 class="text-label-md font-bold flex-1 flex items-center gap-2"><span class="material-symbols-outlined text-primary">notifications_active</span> Alert Queue</h4>
            <select name="priority" class="bg-surface-container-low border-none rounded-lg text-label-md px-3 py-2 focus:ring-1 focus:ring-primary">
                <option value="">All Priority</option>
                @foreach (['critical', 'high', 'medium', 'low'] as $p)<option value="{{ $p }}" @selected(($filters['priority'] ?? '') === $p)>{{ ucfirst($p) }}</option>@endforeach
            </select>
            <select name="module" class="bg-surface-container-low border-none rounded-lg text-label-md px-3 py-2 focus:ring-1 focus:ring-primary">
                <option value="">All Modules</option>
                @foreach (['inventory', 'expiry', 'ledger'] as $m)<option value="{{ $m }}" @selected(($filters['module'] ?? '') === $m)>{{ ucfirst($m) }}</option>@endforeach
            </select>
            <select name="status" class="bg-surface-container-low border-none rounded-lg text-label-md px-3 py-2 focus:ring-1 focus:ring-primary">
                <option value="">All Status</option>
                @foreach (['unread', 'resolved', 'dismissed'] as $s)<option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>{{ ucfirst($s) }}</option>@endforeach
            </select>
            <div class="flex gap-2">
                <button type="submit" class="bg-surface-container-highest text-on-surface-variant px-4 py-2 rounded-lg text-label-md hover:bg-outline-variant">Filter</button>
                <button form="scan" type="submit" class="bg-primary text-white px-4 py-2 rounded-lg text-label-md flex items-center gap-2 hover:opacity-90"><span class="material-symbols-outlined text-[18px]">radar</span> Run Scan</button>
            </div>
        </form>
        <form id="scan" method="POST" action="{{ route('alerts.generate') }}">@csrf</form>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-low text-on-surface-variant text-label-sm uppercase">
                    <tr><th class="px-md py-3 text-center">Priority</th><th class="px-md py-3">Type</th><th class="px-md py-3">Message</th><th class="px-md py-3">Branch</th><th class="px-md py-3 text-center">Status</th><th class="px-md py-3 text-right">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse ($alerts as $alert)
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="px-md py-md text-center">
                                @php $pb = match ($alert->priority) { 'critical' => 'bg-red-100 text-red-700', 'high' => 'bg-orange-100 text-orange-700', 'medium' => 'bg-amber-100 text-amber-700', default => 'bg-surface-variant text-on-surface-variant' }; @endphp
                                <span class="px-2 py-0.5 {{ $pb }} rounded-full text-[10px] font-bold uppercase">{{ $alert->priority }}</span>
                            </td>
                            <td class="px-md py-md"><div class="font-label-md text-on-surface">{{ \Illuminate\Support\Str::headline($alert->alert_type) }}</div><div class="text-[11px] text-outline uppercase">{{ $alert->module }}</div></td>
                            <td class="px-md py-md text-body-sm max-w-md"><div class="font-medium">{{ $alert->title }}</div><div class="text-[11px] text-outline">{{ $alert->message }}</div></td>
                            <td class="px-md py-md text-body-sm">{{ $alert->branch?->name ?? '—' }}</td>
                            <td class="px-md py-md text-center">
                                @php $sb = match ($alert->status) { 'resolved' => 'bg-green-100 text-green-700', 'dismissed' => 'bg-surface-variant text-on-surface-variant', default => 'bg-blue-100 text-blue-700' }; @endphp
                                <span class="px-2 py-0.5 {{ $sb }} rounded-full text-[10px] font-bold uppercase">{{ $alert->status }}</span>
                            </td>
                            <td class="px-md py-md text-right">
                                @if (! in_array($alert->status, ['resolved', 'dismissed']))
                                    <div class="flex justify-end gap-1">
                                        <form method="POST" action="{{ route('alerts.resolve', $alert) }}">@csrf<button class="px-2 py-1 bg-primary text-white rounded text-[10px] font-bold uppercase hover:opacity-90">Resolve</button></form>
                                        <form method="POST" action="{{ route('alerts.dismiss', $alert) }}">@csrf<button class="px-2 py-1 border border-outline-variant text-on-surface-variant rounded text-[10px] font-bold uppercase hover:bg-surface-container-low">Dismiss</button></form>
                                    </div>
                                @else
                                    <span class="text-outline text-[11px]">{{ $alert->resolved_at?->diffForHumans() ?? '—' }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-md py-10 text-center text-outline">No alerts. Click "Run Scan" to detect low stock, expiry, and payment alerts.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-md border-t border-outline-variant">{{ $alerts->links() }}</div>
    </div>
@endsection
