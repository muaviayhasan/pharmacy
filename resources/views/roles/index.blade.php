@extends('layouts.app')

@section('title', 'Roles & Permissions')
@section('page-title', 'Roles & Permissions')

@section('content')
    @include('partials.flash')

    <div class="flex flex-col lg:flex-row gap-lg items-start">
        <div class="lg:w-2/3 bg-white rounded-xl border border-outline-variant custom-shadow overflow-hidden">
            <div class="px-lg py-md border-b border-outline-variant"><h4 class="text-headline-md font-semibold">Available Roles</h4></div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-surface-container-low text-on-surface-variant text-label-sm uppercase">
                        <tr><th class="px-md py-3">Role</th><th class="px-md py-3 text-center">Permissions</th><th class="px-md py-3 text-center">Members</th><th class="px-md py-3 text-right">Actions</th></tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        @foreach ($roles as $role)
                            <tr class="hover:bg-surface-container-low transition-colors">
                                <td class="px-md py-md">
                                    <div class="font-label-md text-on-surface">{{ \Illuminate\Support\Str::headline($role->name) }}</div>
                                    @if ($role->name === 'super_admin')<span class="text-[10px] text-primary font-bold uppercase">Full access</span>@endif
                                </td>
                                <td class="px-md py-md text-center">{{ $role->name === 'super_admin' ? 'All' : $role->permissions_count }}</td>
                                <td class="px-md py-md text-center">{{ $role->users_count }}</td>
                                <td class="px-md py-md text-right">
                                    <div class="flex justify-end gap-1">
                                        <a href="{{ route('roles.edit', $role) }}" class="p-1.5 text-outline hover:text-primary" title="Edit permissions"><span class="material-symbols-outlined text-[18px]">tune</span></a>
                                        @unless (in_array($role->name, ['super_admin','business_owner','branch_manager','pharmacist','cashier','inventory_manager','purchase_manager','accountant','auditor']))
                                            <form method="POST" action="{{ route('roles.destroy', $role) }}" onsubmit="return confirm('Delete this role?');">@csrf @method('DELETE')
                                                <button class="p-1.5 text-outline hover:text-error"><span class="material-symbols-outlined text-[18px]">delete</span></button>
                                            </form>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="lg:w-1/3 bg-white rounded-xl border border-outline-variant custom-shadow p-lg">
            <h4 class="text-label-md font-bold mb-md">Create Custom Role</h4>
            <form method="POST" action="{{ route('roles.store') }}" class="space-y-md">
                @csrf
                <input name="name" type="text" placeholder="e.g. Store Supervisor" required class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                @error('name') <p class="text-body-sm text-error">{{ $message }}</p> @enderror
                <button type="submit" class="w-full bg-primary text-on-primary py-2.5 rounded-lg text-label-md hover:opacity-90 flex items-center justify-center gap-sm"><span class="material-symbols-outlined text-[18px]">add</span> Create Role</button>
            </form>
            <p class="text-[11px] text-outline mt-md">New roles start with no permissions. Open the role to assign access from the permission matrix.</p>
        </div>
    </div>
@endsection
