@extends('layouts.app')

@section('title', 'Edit Role')
@section('page-title', \Illuminate\Support\Str::headline($role->name).' — Permissions')

@section('content')
    <a href="{{ route('roles.index') }}" class="inline-flex items-center gap-xs text-primary text-label-md hover:underline">
        <span class="material-symbols-outlined text-[20px]">arrow_back</span> Back to Roles
    </a>
    @include('partials.flash')

    @if ($protected)
        <div class="bg-primary-container/10 border border-primary-container rounded-xl p-md flex items-center gap-sm max-w-3xl">
            <span class="material-symbols-outlined text-primary-container">verified_user</span>
            <p class="text-body-sm text-primary-container">The <strong>Super Admin</strong> role always has full access and cannot be edited.</p>
        </div>
    @else
        <form method="POST" action="{{ route('roles.update', $role) }}" x-data="{ all: false }">
            @csrf @method('PUT')
            <div class="bg-white rounded-xl border border-outline-variant custom-shadow overflow-hidden">
                <div class="px-lg py-md border-b border-outline-variant flex items-center justify-between">
                    <h4 class="text-headline-md font-semibold">Permission Matrix</h4>
                    <button type="submit" class="bg-primary text-on-primary px-lg py-2 rounded-lg text-label-md hover:opacity-90 flex items-center gap-sm"><span class="material-symbols-outlined text-[18px]">save</span> Save Permissions</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-surface-container-low text-on-surface-variant text-label-sm uppercase">
                            <tr>
                                <th class="px-md py-3">Module</th>
                                @foreach ($actions as $a)<th class="px-md py-3 text-center">{{ $a }}</th>@endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            @foreach ($matrix as $module => $perms)
                                <tr class="hover:bg-surface-container-low transition-colors">
                                    <td class="px-md py-sm font-label-md capitalize">{{ str_replace('_', ' ', $module) }}</td>
                                    @foreach ($actions as $a)
                                        <td class="px-md py-sm text-center">
                                            @if (isset($perms[$a]))
                                                <input type="checkbox" name="permissions[]" value="{{ $perms[$a] }}" @checked(in_array($perms[$a], $assigned)) class="w-4 h-4 text-primary rounded border-outline-variant focus:ring-primary">
                                            @else
                                                <span class="text-outline-variant">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if (! empty($sensitive))
                    <div class="px-lg py-md border-t border-outline-variant">
                        <h5 class="text-label-md font-bold text-error mb-sm flex items-center gap-xs"><span class="material-symbols-outlined text-[18px]">gpp_maybe</span> Sensitive Permissions</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-sm">
                            @foreach ($sensitive as $perm)
                                <label class="flex items-center gap-sm text-body-sm p-sm border border-outline-variant rounded-lg hover:bg-surface-container-low cursor-pointer">
                                    <input type="checkbox" name="permissions[]" value="{{ $perm }}" @checked(in_array($perm, $assigned)) class="w-4 h-4 text-primary rounded border-outline-variant focus:ring-primary">
                                    <span class="capitalize">{{ $perm }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </form>
    @endif
@endsection
