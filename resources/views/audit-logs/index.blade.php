@extends('layouts.app')

@section('title', 'Audit Logs')
@section('page-title', 'Audit Logs')

@section('content')
    <div class="grid grid-cols-2 md:grid-cols-4 gap-md">
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Total Logs</p><h3 class="text-headline-md font-bold">{{ number_format($stats['total']) }}</h3></div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">High Risk</p><h3 class="text-headline-md font-bold text-error">{{ $stats['critical'] }}</h3></div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Failed Events</p><h3 class="text-headline-md font-bold text-amber-600">{{ $stats['failed'] }}</h3></div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Today</p><h3 class="text-headline-md font-bold">{{ $stats['today'] }}</h3></div>
    </div>

    <div class="bg-white rounded-xl border border-outline-variant custom-shadow">
        <form method="GET" class="p-md flex flex-col lg:flex-row lg:items-center gap-md border-b border-outline-variant">
            <select name="module" class="bg-surface-container-low border-none rounded-lg text-label-md px-3 py-2 focus:ring-1 focus:ring-primary">
                <option value="">All Modules</option>
                @foreach ($modules as $m)<option value="{{ $m }}" @selected(($filters['module'] ?? '') === $m)>{{ ucfirst($m) }}</option>@endforeach
            </select>
            <select name="risk" class="bg-surface-container-low border-none rounded-lg text-label-md px-3 py-2 focus:ring-1 focus:ring-primary">
                <option value="">All Risk</option>
                @foreach (['low', 'medium', 'high'] as $r)<option value="{{ $r }}" @selected(($filters['risk'] ?? '') === $r)>{{ ucfirst($r) }}</option>@endforeach
            </select>
            <select name="user" class="bg-surface-container-low border-none rounded-lg text-label-md px-3 py-2 focus:ring-1 focus:ring-primary">
                <option value="">All Users</option>
                @foreach ($users as $u)<option value="{{ $u->id }}" @selected((string) ($filters['user'] ?? '') === (string) $u->id)>{{ $u->name }}</option>@endforeach
            </select>
            <input name="from" value="{{ $filters['from'] ?? '' }}" type="date" class="bg-surface-container-low border-none rounded-lg text-label-md px-3 py-2 focus:ring-1 focus:ring-primary">
            <input name="to" value="{{ $filters['to'] ?? '' }}" type="date" class="bg-surface-container-low border-none rounded-lg text-label-md px-3 py-2 focus:ring-1 focus:ring-primary">
            <button type="submit" class="bg-surface-container-highest text-on-surface-variant px-4 py-2 rounded-lg text-label-md hover:bg-outline-variant">Filter</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-low text-on-surface-variant text-label-sm uppercase">
                    <tr><th class="px-md py-3">Timestamp</th><th class="px-md py-3">User</th><th class="px-md py-3">Role</th><th class="px-md py-3">Module</th><th class="px-md py-3">Action</th><th class="px-md py-3">Detail</th><th class="px-md py-3 text-center">Risk</th><th class="px-md py-3 text-center">Status</th></tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="px-md py-md text-body-sm whitespace-nowrap">{{ $log->created_at?->format('d M, H:i') }}</td>
                            <td class="px-md py-md text-body-sm">{{ $log->user?->name ?? 'System' }}</td>
                            <td class="px-md py-md text-[11px] text-outline uppercase">{{ \Illuminate\Support\Str::headline($log->role_name ?? '—') }}</td>
                            <td class="px-md py-md text-body-sm capitalize">{{ $log->module }}</td>
                            <td class="px-md py-md"><span class="text-label-sm font-bold">{{ \Illuminate\Support\Str::headline($log->action) }}</span></td>
                            <td class="px-md py-md text-[11px] text-outline max-w-xs truncate">{{ $log->new_value['note'] ?? ($log->reference_type ? class_basename($log->reference_type).' #'.$log->reference_id : '') }}</td>
                            <td class="px-md py-md text-center">
                                @php $rb = match ($log->risk_level) { 'high' => 'bg-red-100 text-red-700', 'medium' => 'bg-amber-100 text-amber-700', default => 'bg-surface-variant text-on-surface-variant' }; @endphp
                                <span class="px-2 py-0.5 {{ $rb }} rounded-full text-[10px] font-bold uppercase">{{ $log->risk_level }}</span>
                            </td>
                            <td class="px-md py-md text-center">
                                <span class="px-2 py-0.5 {{ $log->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }} rounded-full text-[10px] font-bold uppercase">{{ $log->status }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-md py-10 text-center text-outline">No audit entries yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-md border-t border-outline-variant">{{ $logs->links() }}</div>
    </div>
@endsection
