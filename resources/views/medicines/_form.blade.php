@php
    $m = $medicine ?? null;
    $val = fn ($field, $default = '') => old($field, $m->$field ?? $default);
@endphp

@include('partials.flash')

{{-- Basic --}}
<div class="bg-white rounded-xl border border-outline-variant custom-shadow overflow-hidden">
    <div class="bg-surface-container-low px-md py-sm border-b border-outline-variant">
        <span class="text-label-md font-bold text-primary flex items-center gap-xs"><span class="material-symbols-outlined text-[18px]">medication</span> Medicine Information</span>
    </div>
    <div class="p-lg grid grid-cols-1 md:grid-cols-2 gap-lg">
        <div class="space-y-sm">
            <label class="block text-label-sm font-bold text-on-surface-variant">Medicine Name <span class="text-error">*</span></label>
            <input name="name" type="text" value="{{ $val('name') }}" required placeholder="Enter medicine full name"
                   class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
        </div>
        <div class="space-y-sm">
            <label class="block text-label-sm font-bold text-on-surface-variant">Generic Name</label>
            <input name="generic_name" type="text" value="{{ $val('generic_name') }}" placeholder="e.g. Paracetamol"
                   class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary focus:border-primary outline-none">
        </div>
        <div class="space-y-sm">
            <label class="block text-label-sm font-bold text-on-surface-variant">Category</label>
            <select name="category_id" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                <option value="">Select category</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->id }}" @selected((string) $val('category_id') === (string) $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="space-y-sm">
            <label class="block text-label-sm font-bold text-on-surface-variant">Dosage Form</label>
            <select name="dosage_form" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                <option value="">Select form</option>
                @foreach (['Tablet', 'Capsule', 'Syrup', 'Injection', 'Ointment', 'Drops', 'Inhaler', 'Sachet'] as $form)
                    <option value="{{ $form }}" @selected($val('dosage_form') === $form)>{{ $form }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-2 gap-sm">
            <div class="space-y-sm">
                <label class="block text-label-sm font-bold text-on-surface-variant">Strength</label>
                <input name="strength" type="text" value="{{ $val('strength') }}" placeholder="500"
                       class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
            </div>
            <div class="space-y-sm">
                <label class="block text-label-sm font-bold text-on-surface-variant">Unit</label>
                <select name="strength_unit" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                    <option value="">—</option>
                    @foreach (['mg', 'ml', 'mcg', 'g', 'IU', '%'] as $u)
                        <option value="{{ $u }}" @selected($val('strength_unit') === $u)>{{ $u }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="space-y-sm">
            <label class="block text-label-sm font-bold text-on-surface-variant">Pack Size</label>
            <input name="pack_size" type="text" value="{{ $val('pack_size') }}" placeholder="e.g. 10x10"
                   class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
        </div>
        <div class="space-y-sm">
            <label class="block text-label-sm font-bold text-on-surface-variant">Manufacturer</label>
            <select name="manufacturer_id" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                <option value="">Select manufacturer</option>
                @foreach ($manufacturers as $mf)
                    <option value="{{ $mf->id }}" @selected((string) $val('manufacturer_id') === (string) $mf->id)>{{ $mf->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="space-y-sm">
            <label class="block text-label-sm font-bold text-on-surface-variant">Preferred Supplier</label>
            <select name="default_supplier_id" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                <option value="">Select supplier</option>
                @foreach ($suppliers as $s)
                    <option value="{{ $s->id }}" @selected((string) $val('default_supplier_id') === (string) $s->id)>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="space-y-sm">
            <label class="block text-label-sm font-bold text-on-surface-variant">Barcode</label>
            <input name="barcode" type="text" value="{{ $val('barcode') }}" placeholder="Scan or enter barcode"
                   class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
        </div>
        <div class="space-y-sm">
            <label class="block text-label-sm font-bold text-on-surface-variant">Rack / Shelf</label>
            <input name="rack_shelf" type="text" value="{{ $val('rack_shelf') }}" placeholder="e.g. A-12"
                   class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
        </div>
    </div>
</div>

{{-- Pricing & stock --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-lg">
    <div class="bg-white rounded-xl border border-outline-variant custom-shadow overflow-hidden">
        <div class="bg-surface-container-low px-md py-sm border-b border-outline-variant">
            <span class="text-label-md font-bold text-primary flex items-center gap-xs"><span class="material-symbols-outlined text-[18px]">payments</span> Pricing</span>
        </div>
        <div class="p-lg grid grid-cols-2 gap-lg">
            <div class="space-y-sm">
                <label class="block text-label-sm font-bold text-on-surface-variant">Purchase Price <span class="text-error">*</span></label>
                <input name="purchase_price" type="number" step="0.01" min="0" value="{{ $val('purchase_price', 0) }}" required class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
            </div>
            <div class="space-y-sm">
                <label class="block text-label-sm font-bold text-on-surface-variant">MRP / Sale Price <span class="text-error">*</span></label>
                <input name="sale_price" type="number" step="0.01" min="0" value="{{ $val('sale_price', 0) }}" required class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
            </div>
            <div class="space-y-sm">
                <label class="block text-label-sm font-bold text-on-surface-variant">Wholesale Price</label>
                <input name="wholesale_price" type="number" step="0.01" min="0" value="{{ $val('wholesale_price', 0) }}" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
            </div>
            <div class="space-y-sm">
                <label class="block text-label-sm font-bold text-on-surface-variant">Tax (%)</label>
                <input name="tax_percent" type="number" step="0.01" min="0" max="100" value="{{ $val('tax_percent', 0) }}" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
            </div>
            <div class="space-y-sm col-span-2">
                <label class="block text-label-sm font-bold text-on-surface-variant">Max Discount (%)</label>
                <input name="max_discount_percent" type="number" step="0.01" min="0" max="100" value="{{ $val('max_discount_percent', 0) }}" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-outline-variant custom-shadow overflow-hidden">
        <div class="bg-surface-container-low px-md py-sm border-b border-outline-variant">
            <span class="text-label-md font-bold text-primary flex items-center gap-xs"><span class="material-symbols-outlined text-[18px]">inventory_2</span> Stock & Classification</span>
        </div>
        <div class="p-lg space-y-lg">
            <div class="grid grid-cols-3 gap-md">
                <div class="space-y-sm">
                    <label class="block text-label-sm font-bold text-on-surface-variant">Min Stock</label>
                    <input name="min_stock_level" type="number" min="0" value="{{ $val('min_stock_level', 0) }}" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                </div>
                <div class="space-y-sm">
                    <label class="block text-label-sm font-bold text-on-surface-variant">Reorder</label>
                    <input name="reorder_level" type="number" min="0" value="{{ $val('reorder_level', 0) }}" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                </div>
                <div class="space-y-sm">
                    <label class="block text-label-sm font-bold text-on-surface-variant">Max Stock</label>
                    <input name="max_stock_level" type="number" min="0" value="{{ $val('max_stock_level', 0) }}" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                </div>
            </div>
            <div class="flex items-center justify-between bg-surface-container-low p-md rounded-lg border border-outline-variant">
                <span class="text-label-md text-on-surface">Prescription Required</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="prescription_required" value="1" class="sr-only peer" @checked(old('prescription_required', $m->prescription_required ?? false))>
                    <div class="w-11 h-6 bg-surface-variant rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                </label>
            </div>
            <div class="flex items-center justify-between bg-surface-container-low p-md rounded-lg border border-outline-variant">
                <span class="text-label-md text-on-surface">Controlled Medicine</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="controlled_medicine" value="1" class="sr-only peer" @checked(old('controlled_medicine', $m->controlled_medicine ?? false))>
                    <div class="w-11 h-6 bg-surface-variant rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-error"></div>
                </label>
            </div>
            <div class="space-y-sm">
                <label class="block text-label-sm font-bold text-on-surface-variant">Status</label>
                <select name="status" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                    <option value="active" @selected($val('status', 'active') === 'active')>Active</option>
                    <option value="inactive" @selected($val('status') === 'inactive')>Inactive</option>
                </select>
            </div>
        </div>
    </div>
</div>
