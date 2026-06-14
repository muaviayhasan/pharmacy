@extends('layouts.app')

@section('title', 'User Management')
@section('page-title', 'User Management')

@section('content')
    {{-- KPI row --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-md">
        <div class="bg-white p-md rounded-lg border border-outline-variant custom-shadow">
            <div class="p-2 bg-primary-container rounded-lg text-on-primary-container w-fit mb-2">
                <span class="material-symbols-outlined">groups</span>
            </div>
            <p class="text-outline text-label-sm uppercase tracking-tight">Total Users</p>
            <h3 class="text-headline-md font-bold">{{ $stats['total'] }}</h3>
        </div>
        <div class="bg-white p-md rounded-lg border border-outline-variant custom-shadow">
            <div class="p-2 bg-green-100 text-green-700 rounded-lg w-fit mb-2">
                <span class="material-symbols-outlined">how_to_reg</span>
            </div>
            <p class="text-outline text-label-sm uppercase tracking-tight">Active Users</p>
            <h3 class="text-headline-md font-bold">{{ $stats['active'] }}</h3>
        </div>
        <div class="bg-white p-md rounded-lg border border-outline-variant custom-shadow">
            <div class="p-2 bg-red-100 text-red-700 rounded-lg w-fit mb-2">
                <span class="material-symbols-outlined">block</span>
            </div>
            <p class="text-outline text-label-sm uppercase tracking-tight">Blocked Users</p>
            <h3 class="text-headline-md font-bold">{{ $stats['blocked'] }}</h3>
        </div>
        <div class="bg-white p-md rounded-lg border border-outline-variant custom-shadow">
            <div class="p-2 bg-primary-fixed text-on-primary-fixed rounded-lg w-fit mb-2">
                <span class="material-symbols-outlined">security</span>
            </div>
            <p class="text-outline text-label-sm uppercase tracking-tight">Users with 2FA</p>
            <h3 class="text-headline-md font-bold">{{ $stats['two_factor'] }}</h3>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="bg-white rounded-lg border border-outline-variant custom-shadow">
        <form method="GET" class="p-md flex flex-col lg:flex-row lg:items-center gap-md border-b border-outline-variant">
            <div class="relative flex-1">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[20px]">search</span>
                <input name="search" value="{{ $filters['search'] ?? '' }}" type="text"
                       class="w-full pl-10 pr-4 py-2 bg-surface-container-low border-none rounded-lg text-body-sm focus:ring-1 focus:ring-primary"
                       placeholder="Search user, email, phone...">
            </div>
            <select name="role" class="bg-surface-container-low border-none rounded-lg text-label-md px-3 py-2 focus:ring-1 focus:ring-primary">
                <option value="">All Roles</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}" @selected(($filters['role'] ?? '') === $role->name)>{{ \Illuminate\Support\Str::headline($role->name) }}</option>
                @endforeach
            </select>
            <select name="status" class="bg-surface-container-low border-none rounded-lg text-label-md px-3 py-2 focus:ring-1 focus:ring-primary">
                <option value="">All Status</option>
                @foreach (['active', 'inactive', 'blocked'] as $st)
                    <option value="{{ $st }}" @selected(($filters['status'] ?? '') === $st)>{{ ucfirst($st) }}</option>
                @endforeach
            </select>
            <select name="branch" class="bg-surface-container-low border-none rounded-lg text-label-md px-3 py-2 focus:ring-1 focus:ring-primary">
                <option value="">All Branches</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected((string) ($filters['branch'] ?? '') === (string) $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button type="submit" class="bg-surface-container-highest text-on-surface-variant px-4 py-2 rounded-lg text-label-md hover:bg-outline-variant transition-all">Filter</button>
                <a href="{{ route('users.create') }}" class="bg-primary text-white px-4 py-2 rounded-lg text-label-md flex items-center gap-2 hover:opacity-90 transition-all">
                    <span class="material-symbols-outlined text-[18px]">person_add</span> Add User
                </a>
            </div>
        </form>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-low text-on-surface-variant text-label-sm uppercase">
                    <tr>
                        <th class="px-4 py-3 font-semibold">User</th>
                        <th class="px-4 py-3 font-semibold">Role / Branch</th>
                        <th class="px-4 py-3 font-semibold">Last Login</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                        <th class="px-4 py-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse ($users as $user)
                        <tr class="hover:bg-surface-container-low transition-colors group">
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-tertiary-container text-white flex items-center justify-center font-bold uppercase">
                                        {{ \Illuminate\Support\Str::of($user->name)->explode(' ')->take(2)->map(fn ($p) => \Illuminate\Support\Str::substr($p, 0, 1))->implode('') }}
                                    </div>
                                    <div>
                                        <p class="text-label-md text-on-surface">{{ $user->name }}</p>
                                        <p class="text-body-sm text-outline">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <span class="text-label-sm font-semibold text-secondary">{{ \Illuminate\Support\Str::headline($user->getRoleNames()->first() ?? '—') }}</span><br>
                                <span class="text-body-sm text-on-surface-variant">{{ $user->branches->pluck('name')->join(', ') ?: 'No branch' }}</span>
                            </td>
                            <td class="px-4 py-4">
                                <p class="text-body-sm text-on-surface">{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</p>
                                @if ($user->two_factor_enabled)
                                    <p class="text-[10px] text-green-600 font-bold">2FA ENABLED</p>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                @php
                                    $badge = match ($user->status) {
                                        'active' => 'bg-green-100 text-green-700',
                                        'blocked' => 'bg-red-100 text-red-700',
                                        default => 'bg-amber-100 text-amber-700',
                                    };
                                @endphp
                                <span class="{{ $badge }} px-2 py-0.5 rounded-full text-[11px] font-bold uppercase">{{ $user->status }}</span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('users.edit', $user) }}" class="p-1.5 text-outline hover:text-primary" title="Edit">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                    </a>
                                    <form method="POST" action="{{ route('users.password', $user) }}">
                                        @csrf
                                        <button class="p-1.5 text-outline hover:text-primary" title="Send password reset">
                                            <span class="material-symbols-outlined text-[18px]">lock_reset</span>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('users.block', $user) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="p-1.5 text-outline hover:text-error" title="{{ $user->status === 'blocked' ? 'Unblock' : 'Block' }}">
                                            <span class="material-symbols-outlined text-[18px]">{{ $user->status === 'blocked' ? 'lock_open' : 'block' }}</span>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Delete this user?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="p-1.5 text-outline hover:text-error" title="Delete">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-outline">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-md border-t border-outline-variant">
            {{ $users->links() }}
        </div>
    </div>
@endsection
