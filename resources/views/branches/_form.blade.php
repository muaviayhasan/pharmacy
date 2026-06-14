@php $b = $branch ?? null; $val = fn ($f, $d = '') => old($f, $b->$f ?? $d); @endphp
@include('partials.flash')

<div class="bg-white rounded-xl border border-outline-variant custom-shadow overflow-hidden max-w-3xl">
    <div class="bg-surface-container-low px-md py-sm border-b border-outline-variant">
        <span class="text-label-md font-bold text-primary flex items-center gap-xs"><span class="material-symbols-outlined text-[18px]">store</span> Branch Details</span>
    </div>
    <div class="p-lg grid grid-cols-1 md:grid-cols-2 gap-lg">
        <div class="space-y-sm">
            <label class="block text-label-sm font-bold text-on-surface-variant">Branch Name <span class="text-error">*</span></label>
            <input name="name" type="text" value="{{ $val('name') }}" required class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
        </div>
        <div class="space-y-sm">
            <label class="block text-label-sm font-bold text-on-surface-variant">Code <span class="text-error">*</span></label>
            <input name="code" type="text" value="{{ $val('code') }}" required class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none uppercase">
        </div>
        <div class="space-y-sm">
            <label class="block text-label-sm font-bold text-on-surface-variant">Type</label>
            <select name="type" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                @foreach (['main' => 'Main', 'branch' => 'Branch', 'outlet' => 'Outlet', 'warehouse' => 'Warehouse'] as $k => $v)<option value="{{ $k }}" @selected($val('type', 'branch') === $k)>{{ $v }}</option>@endforeach
            </select>
        </div>
        <div class="space-y-sm">
            <label class="block text-label-sm font-bold text-on-surface-variant">Manager</label>
            <select name="manager_id" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                <option value="">Unassigned</option>
                @foreach ($users as $u)<option value="{{ $u->id }}" @selected((string) $val('manager_id') === (string) $u->id)>{{ $u->name }}</option>@endforeach
            </select>
        </div>
        <div class="space-y-sm">
            <label class="block text-label-sm font-bold text-on-surface-variant">Phone</label>
            <input name="phone" type="text" value="{{ $val('phone') }}" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
        </div>
        <div class="space-y-sm">
            <label class="block text-label-sm font-bold text-on-surface-variant">Email</label>
            <input name="email" type="email" value="{{ $val('email') }}" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
        </div>
        <div class="space-y-sm md:col-span-2">
            <label class="block text-label-sm font-bold text-on-surface-variant">Address</label>
            <input name="address" type="text" value="{{ $val('address') }}" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
        </div>
        <div class="space-y-sm">
            <label class="block text-label-sm font-bold text-on-surface-variant">City</label>
            <input name="city" type="text" value="{{ $val('city') }}" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
        </div>
        <div class="space-y-sm">
            <label class="block text-label-sm font-bold text-on-surface-variant">Status</label>
            <select name="status" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                <option value="active" @selected($val('status', 'active') === 'active')>Active</option>
                <option value="inactive" @selected($val('status') === 'inactive')>Inactive</option>
            </select>
        </div>
    </div>
    <div class="px-lg py-md bg-surface-container-low flex justify-end gap-sm">
        <a href="{{ route('branches.index') }}" class="px-lg py-2.5 rounded-lg text-label-md border border-outline-variant text-on-surface-variant hover:bg-surface-container">Cancel</a>
        <button type="submit" class="px-lg py-2.5 rounded-lg text-label-md bg-primary text-on-primary hover:opacity-90 flex items-center gap-sm"><span class="material-symbols-outlined text-[18px]">save</span> {{ $b ? 'Update' : 'Save' }} Branch</button>
    </div>
</div>
