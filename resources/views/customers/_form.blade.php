@php $c = $customer ?? null; $val = fn ($f, $d = '') => old($f, $c->$f ?? $d); @endphp
@include('partials.flash')

<div class="bg-white rounded-xl border border-outline-variant custom-shadow overflow-hidden max-w-3xl">
    <div class="bg-surface-container-low px-md py-sm border-b border-outline-variant">
        <span class="text-label-md font-bold text-primary flex items-center gap-xs"><span class="material-symbols-outlined text-[18px]">person</span> Customer Details</span>
    </div>
    <div class="p-lg grid grid-cols-1 md:grid-cols-2 gap-lg">
        <div class="space-y-sm">
            <label class="block text-label-sm font-bold text-on-surface-variant">Name <span class="text-error">*</span></label>
            <input name="name" type="text" value="{{ $val('name') }}" required class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
        </div>
        <div class="space-y-sm">
            <label class="block text-label-sm font-bold text-on-surface-variant">Customer Type <span class="text-error">*</span></label>
            <select name="customer_type" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                @foreach (['walk_in' => 'Walk-in', 'regular' => 'Regular', 'credit' => 'Credit', 'corporate' => 'Corporate'] as $k => $v)<option value="{{ $k }}" @selected($val('customer_type', 'walk_in') === $k)>{{ $v }}</option>@endforeach
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
            <label class="block text-label-sm font-bold text-on-surface-variant">Credit Limit</label>
            <input name="credit_limit" type="number" step="0.01" min="0" value="{{ $val('credit_limit', 0) }}" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
        </div>
        @unless ($c)
            <div class="space-y-sm">
                <label class="block text-label-sm font-bold text-on-surface-variant">Opening Balance</label>
                <input name="opening_balance" type="number" step="0.01" value="{{ $val('opening_balance', 0) }}" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
            </div>
        @endunless
        <div class="space-y-sm">
            <label class="block text-label-sm font-bold text-on-surface-variant">Status</label>
            <select name="status" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                <option value="active" @selected($val('status', 'active') === 'active')>Active</option>
                <option value="inactive" @selected($val('status') === 'inactive')>Inactive</option>
            </select>
        </div>
    </div>
    <div class="px-lg py-md bg-surface-container-low flex justify-end gap-sm">
        <a href="{{ route('customers.index') }}" class="px-lg py-2.5 rounded-lg text-label-md border border-outline-variant text-on-surface-variant hover:bg-surface-container">Cancel</a>
        <button type="submit" class="px-lg py-2.5 rounded-lg text-label-md bg-primary text-on-primary hover:opacity-90 flex items-center gap-sm"><span class="material-symbols-outlined text-[18px]">save</span> {{ $c ? 'Update' : 'Save' }} Customer</button>
    </div>
</div>
