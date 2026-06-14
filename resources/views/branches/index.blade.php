@extends('layouts.app')

@section('title', 'Branches')
@section('page-title', 'Branch Management')

@section('content')
    <div class="grid grid-cols-2 md:grid-cols-4 gap-md">
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Total Branches</p><h3 class="text-headline-md font-bold">{{ $stats['total'] }}</h3></div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Active</p><h3 class="text-headline-md font-bold text-green-600">{{ $stats['active'] }}</h3></div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">POS Counters</p><h3 class="text-headline-md font-bold">{{ $stats['counters'] }}</h3></div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Consolidated Stock</p><h3 class="text-headline-md font-bold text-primary">Rs. {{ number_format($stats['stock_value'], 0) }}</h3></div>
    </div>

    <div class="bg-white rounded-xl border border-outline-variant custom-shadow">
        <form method="GET" class="p-md flex items-center gap-md border-b border-outline-variant">
            <div class="relative flex-1">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[20px]">search</span>
                <input name="search" value="{{ $filters['search'] ?? '' }}" type="text" placeholder="Search branch name or code..." class="w-full pl-10 pr-4 py-2 bg-surface-container-low border-none rounded-lg text-body-sm focus:ring-1 focus:ring-primary">
            </div>
            <select name="status" class="bg-surface-container-low border-none rounded-lg text-label-md px-3 py-2 focus:ring-1 focus:ring-primary">
                <option value="">All Status</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
            </select>
            <button type="submit" class="bg-surface-container-highest text-on-surface-variant px-4 py-2 rounded-lg text-label-md hover:bg-outline-variant">Filter</button>
            <a href="{{ route('branches.create') }}" class="bg-primary text-white px-4 py-2 rounded-lg text-label-md flex items-center gap-2 hover:opacity-90"><span class="material-symbols-outlined text-[18px]">add_business</span> Add Branch</a>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-low text-on-surface-variant text-label-sm uppercase">
                    <tr><th class="px-md py-3">Branch</th><th class="px-md py-3">Code</th><th class="px-md py-3">Type</th><th class="px-md py-3">Manager</th><th class="px-md py-3 text-center">Users</th><th class="px-md py-3 text-center">Status</th><th class="px-md py-3 text-right">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse ($branches as $b)
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="px-md py-md"><div class="font-label-md text-on-surface">{{ $b->name }}</div><div class="text-[11px] text-outline">{{ $b->city ?? '—' }}</div></td>
                            <td class="px-md py-md font-mono text-body-sm">{{ $b->code }}</td>
                            <td class="px-md py-md text-body-sm capitalize">{{ $b->type }}</td>
                            <td class="px-md py-md text-body-sm">{{ $b->manager?->name ?? '—' }}</td>
                            <td class="px-md py-md text-center">{{ $b->users_count }}</td>
                            <td class="px-md py-md text-center"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $b->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-surface-variant text-on-surface-variant' }}">{{ $b->status }}</span></td>
                            <td class="px-md py-md text-right">
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('branches.show', $b) }}" class="p-1.5 text-outline hover:text-primary" title="View"><span class="material-symbols-outlined text-[18px]">visibility</span></a>
                                    <a href="{{ route('branches.edit', $b) }}" class="p-1.5 text-outline hover:text-primary" title="Edit"><span class="material-symbols-outlined text-[18px]">edit</span></a>
                                    <form method="POST" action="{{ route('branches.destroy', $b) }}" onsubmit="return confirm('Delete this branch?');">@csrf @method('DELETE')
                                        <button class="p-1.5 text-outline hover:text-error" title="Delete"><span class="material-symbols-outlined text-[18px]">delete</span></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-md py-10 text-center text-outline">No branches found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-md border-t border-outline-variant">{{ $branches->links() }}</div>
    </div>
@endsection
