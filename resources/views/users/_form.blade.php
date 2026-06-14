@php
    $editing = isset($user);
    $selectedRole = old('role', $editing ? $user->getRoleNames()->first() : '');
    $selectedBranches = old('branches', $userBranchIds ?? []);
    $selectedStatus = old('status', $user->status ?? 'active');
    $selectedDefault = old('default_branch_id', $user->default_branch_id ?? '');
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-lg">
    <div class="space-y-sm">
        <label class="block text-label-md text-on-surface-variant ml-1">Full Name <span class="text-error">*</span></label>
        <input name="name" type="text" value="{{ old('name', $user->name ?? '') }}" required
               class="w-full border border-outline-variant rounded-lg p-md focus:ring-2 focus:ring-primary focus:border-primary bg-background text-body-md">
    </div>
    <div class="space-y-sm">
        <label class="block text-label-md text-on-surface-variant ml-1">Email Address <span class="text-error">*</span></label>
        <input name="email" type="email" value="{{ old('email', $user->email ?? '') }}" required
               class="w-full border border-outline-variant rounded-lg p-md focus:ring-2 focus:ring-primary focus:border-primary bg-background text-body-md">
    </div>
    <div class="space-y-sm">
        <label class="block text-label-md text-on-surface-variant ml-1">Phone</label>
        <input name="phone" type="text" value="{{ old('phone', $user->phone ?? '') }}"
               class="w-full border border-outline-variant rounded-lg p-md focus:ring-2 focus:ring-primary focus:border-primary bg-background text-body-md">
    </div>
    <div class="space-y-sm">
        <label class="block text-label-md text-on-surface-variant ml-1">Role <span class="text-error">*</span></label>
        <select name="role" required class="w-full border border-outline-variant rounded-lg p-md focus:ring-2 focus:ring-primary bg-background text-body-md">
            <option value="">Select a role</option>
            @foreach ($roles as $role)
                <option value="{{ $role->name }}" @selected($selectedRole === $role->name)>{{ \Illuminate\Support\Str::headline($role->name) }}</option>
            @endforeach
        </select>
    </div>
    <div class="space-y-sm">
        <label class="block text-label-md text-on-surface-variant ml-1">Status <span class="text-error">*</span></label>
        <select name="status" required class="w-full border border-outline-variant rounded-lg p-md focus:ring-2 focus:ring-primary bg-background text-body-md">
            @foreach (['active', 'inactive', 'blocked'] as $st)
                <option value="{{ $st }}" @selected($selectedStatus === $st)>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
    </div>
    <div class="space-y-sm">
        <label class="block text-label-md text-on-surface-variant ml-1">Default Branch</label>
        <select name="default_branch_id" class="w-full border border-outline-variant rounded-lg p-md focus:ring-2 focus:ring-primary bg-background text-body-md">
            <option value="">None</option>
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}" @selected((string) $selectedDefault === (string) $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="space-y-sm">
    <label class="block text-label-md text-on-surface-variant ml-1">Branch Access</label>
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-sm">
        @foreach ($branches as $branch)
            <label class="flex items-center gap-sm p-md border border-outline-variant rounded-lg cursor-pointer hover:bg-surface-container-low">
                <input type="checkbox" name="branches[]" value="{{ $branch->id }}"
                       @checked(in_array($branch->id, collect($selectedBranches)->map(fn ($id) => (int) $id)->all()))
                       class="w-4 h-4 text-primary border-outline-variant rounded focus:ring-primary">
                <span class="text-body-sm">{{ $branch->name }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-lg">
    <div class="space-y-sm">
        <label class="block text-label-md text-on-surface-variant ml-1">
            Password @unless($editing)<span class="text-error">*</span>@endunless
        </label>
        <input name="password" type="password" {{ $editing ? '' : 'required' }}
               class="w-full border border-outline-variant rounded-lg p-md focus:ring-2 focus:ring-primary bg-background text-body-md"
               placeholder="{{ $editing ? 'Leave blank to keep current' : '' }}">
    </div>
    <div class="space-y-sm">
        <label class="block text-label-md text-on-surface-variant ml-1">Confirm Password</label>
        <input name="password_confirmation" type="password" {{ $editing ? '' : 'required' }}
               class="w-full border border-outline-variant rounded-lg p-md focus:ring-2 focus:ring-primary bg-background text-body-md">
    </div>
</div>

<div class="flex items-center justify-between bg-surface-container-low p-md rounded-lg border border-outline-variant">
    <div>
        <p class="text-label-md text-on-surface">Require Two-Factor Authentication</p>
        <p class="text-label-sm text-outline">User must verify an emailed OTP code at login.</p>
    </div>
    <label class="relative inline-flex items-center cursor-pointer">
        <input type="checkbox" name="two_factor_enabled" value="1" class="sr-only peer" @checked(old('two_factor_enabled', $user->two_factor_enabled ?? false))>
        <div class="w-11 h-6 bg-surface-variant rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
    </label>
</div>

<div class="flex justify-end gap-sm pt-sm">
    <a href="{{ route('users.index') }}" class="px-lg py-2.5 rounded-lg text-label-md border border-outline-variant text-on-surface-variant hover:bg-surface-container-low transition-all">Cancel</a>
    <button type="submit" class="bg-primary text-on-primary px-lg py-2.5 rounded-lg text-label-md hover:opacity-90 transition-all flex items-center gap-sm">
        <span class="material-symbols-outlined text-[18px]">save</span> {{ $editing ? 'Update User' : 'Create User' }}
    </button>
</div>
