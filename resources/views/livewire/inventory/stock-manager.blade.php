<div class="space-y-lg">
    {{-- KPI cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-md">
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow">
            <p class="text-label-sm text-on-surface-variant flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">account_balance_wallet</span> Stock Value</p>
            <h3 class="text-headline-md font-extrabold mt-1">Rs. {{ number_format($this->kpis['stock_value'], 0) }}</h3>
        </div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow">
            <p class="text-label-sm text-on-surface-variant flex items-center gap-1"><span class="material-symbols-outlined text-[16px] text-primary">check_circle</span> Units in Stock</p>
            <h3 class="text-headline-md font-extrabold mt-1">{{ number_format($this->kpis['units']) }}</h3>
        </div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow">
            <p class="text-label-sm text-on-surface-variant flex items-center gap-1"><span class="material-symbols-outlined text-[16px] text-yellow-600">trending_down</span> Low Stock</p>
            <h3 class="text-headline-md font-extrabold mt-1 text-yellow-600">{{ $this->kpis['low'] }}</h3>
        </div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow">
            <p class="text-label-sm text-on-surface-variant flex items-center gap-1"><span class="material-symbols-outlined text-[16px] text-secondary">event_busy</span> Near Expiry</p>
            <h3 class="text-headline-md font-extrabold mt-1">{{ $this->kpis['near_expiry'] }}</h3>
        </div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow">
            <p class="text-label-sm text-on-surface-variant flex items-center gap-1"><span class="material-symbols-outlined text-[16px] text-error">dangerous</span> Expired</p>
            <h3 class="text-headline-md font-extrabold mt-1 text-error">{{ $this->kpis['expired'] }}</h3>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow">
        <div class="flex items-center justify-between mb-md">
            <h4 class="text-label-md font-bold flex items-center gap-2"><span class="material-symbols-outlined text-primary">filter_list</span> Inventory Filters</h4>
            <button wire:click="resetFilters" class="text-primary text-label-sm hover:underline">Clear All</button>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-sm">
            <div>
                <label class="text-[11px] font-bold text-on-surface-variant block mb-1">Branch</label>
                <select wire:model.live="branchFilter" class="w-full text-body-sm py-2 px-3 border border-outline-variant rounded-lg focus:border-primary outline-none">
                    <option value="">All Branches</option>
                    @foreach ($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="text-[11px] font-bold text-on-surface-variant block mb-1">Category</label>
                <select wire:model.live="categoryFilter" class="w-full text-body-sm py-2 px-3 border border-outline-variant rounded-lg focus:border-primary outline-none">
                    <option value="">All Categories</option>
                    @foreach ($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="text-[11px] font-bold text-on-surface-variant block mb-1">Stock Status</label>
                <select wire:model.live="stockStatus" class="w-full text-body-sm py-2 px-3 border border-outline-variant rounded-lg focus:border-primary outline-none">
                    <option value="">All Status</option>
                    <option value="in_stock">In Stock</option>
                    <option value="low">Low Stock</option>
                    <option value="out">Out of Stock</option>
                </select>
            </div>
            <div>
                <label class="text-[11px] font-bold text-on-surface-variant block mb-1">Expiry</label>
                <select wire:model.live="expiryStatus" class="w-full text-body-sm py-2 px-3 border border-outline-variant rounded-lg focus:border-primary outline-none">
                    <option value="">All Expiry</option>
                    <option value="expired">Expired</option>
                    <option value="near">Near Expiry</option>
                    <option value="ok">Safe</option>
                </select>
            </div>
            <div class="col-span-2">
                <label class="text-[11px] font-bold text-on-surface-variant block mb-1">Search</label>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Medicine, generic, or batch..." class="w-full text-body-sm py-2 px-3 border border-outline-variant rounded-lg focus:border-primary outline-none">
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-lg items-start">
        <div class="xl:col-span-8 space-y-lg">
            {{-- Current stock --}}
            <div class="bg-white rounded-xl border border-outline-variant custom-shadow overflow-hidden">
                <div class="px-md py-4 border-b border-outline-variant flex justify-between items-center bg-surface-container-low/30">
                    <h4 class="text-headline-md font-bold">Current Stock</h4>
                    <span class="text-label-sm text-outline">{{ $this->rows->count() }} lines</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-surface-container-low/50 border-b border-outline-variant text-label-sm font-bold text-on-surface-variant">
                            <tr>
                                <th class="px-md py-3">Medicine</th>
                                <th class="px-md py-3">Generic / Mfr</th>
                                <th class="px-md py-3">Branch</th>
                                <th class="px-md py-3 text-right">Stock</th>
                                <th class="px-md py-3 text-right">Value</th>
                                <th class="px-md py-3">Nearest Expiry</th>
                                <th class="px-md py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            @forelse ($this->rows as $r)
                                <tr wire:click="selectMedicine({{ $r->medicine_id }}, {{ $r->branch_id }})"
                                    class="hover:bg-primary-fixed-dim/10 transition-colors cursor-pointer {{ $selectedMedicineId === $r->medicine_id && $selectedBranchId === $r->branch_id ? 'bg-primary/5' : '' }}">
                                    <td class="px-md py-3">
                                        <p class="font-label-md text-on-surface">{{ $r->name }}</p>
                                        <p class="text-[11px] text-on-surface-variant">{{ $r->dosage_form }}</p>
                                    </td>
                                    <td class="px-md py-3"><p class="text-body-sm">{{ $r->generic_name ?? '—' }}</p><p class="text-[11px] text-outline">{{ $r->mfr }}</p></td>
                                    <td class="px-md py-3 text-body-sm">{{ $r->branch }}</td>
                                    <td class="px-md py-3 text-right font-bold text-body-sm">{{ number_format($r->qty) }}</td>
                                    <td class="px-md py-3 text-right text-label-md">Rs. {{ number_format($r->value, 0) }}</td>
                                    <td class="px-md py-3 text-body-sm {{ $r->expiry_flag !== 'ok' ? 'text-error font-bold' : '' }}">{{ \Illuminate\Support\Carbon::parse($r->nearest_expiry)->format('M Y') }}</td>
                                    <td class="px-md py-3 text-center">
                                        @php
                                            $sb = match ($r->status) {
                                                'in_stock' => 'bg-green-100 text-green-700',
                                                'low' => 'bg-orange-100 text-orange-700',
                                                default => 'bg-red-100 text-red-700',
                                            };
                                        @endphp
                                        <span class="inline-flex px-2 py-1 rounded {{ $sb }} text-[10px] font-bold uppercase">{{ str_replace('_', ' ', $r->status) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-md py-10 text-center text-outline">No stock matches the current filters.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Batch detail (selected) --}}
            @if ($selectedMedicineId && $this->selectedBatches->isNotEmpty())
                <div class="bg-white rounded-xl border border-outline-variant custom-shadow overflow-hidden">
                    <div class="px-md py-4 border-b border-outline-variant flex justify-between items-center">
                        <h4 class="text-label-md font-bold">Batch-wise Details: <span class="text-primary">{{ $this->selectedBatches->first()->medicine?->name }}</span></h4>
                        <span class="text-label-sm text-outline">{{ $this->selectedBatches->count() }} active batches</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-body-sm">
                            <thead class="bg-surface-container-low/30 border-b border-outline-variant text-label-sm font-bold">
                                <tr><th class="px-md py-2">Batch</th><th class="px-md py-2">Branch</th><th class="px-md py-2">Expiry</th><th class="px-md py-2">Supplier</th><th class="px-md py-2 text-right">Available</th><th class="px-md py-2 text-right">Value</th><th class="px-md py-2 text-center">Status</th></tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant">
                                @foreach ($this->selectedBatches as $batch)
                                    <tr class="{{ $batch->isExpired() ? 'bg-error-container/20' : '' }}">
                                        <td class="px-md py-3 font-mono">{{ $batch->batch_no }}</td>
                                        <td class="px-md py-3">{{ $batch->branch?->name }}</td>
                                        <td class="px-md py-3 {{ $batch->isExpired() ? 'text-error font-bold' : '' }}">{{ $batch->expiry_date?->format('d M Y') }}</td>
                                        <td class="px-md py-3">{{ $batch->supplier?->name ?? '—' }}</td>
                                        <td class="px-md py-3 text-right font-bold">{{ number_format($batch->available_quantity) }}</td>
                                        <td class="px-md py-3 text-right">Rs. {{ number_format($batch->available_quantity * $batch->purchase_price, 2) }}</td>
                                        <td class="px-md py-3 text-center">
                                            @php $exp = $batch->isExpired(); $nearExp = ! $exp && $batch->expiry_date && $batch->expiry_date->lte(now()->addDays(90)); @endphp
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $exp ? 'bg-red-100 text-red-700' : ($nearExp ? 'bg-orange-100 text-orange-700' : 'bg-green-100 text-green-700') }}">{{ $exp ? 'Expired' : ($nearExp ? 'Near Expiry' : 'OK') }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Expiry risk --}}
            <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow">
                <h4 class="text-label-md font-bold mb-md">Expiry Risk Management</h4>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-md">
                    @php
                        $risk = [
                            ['Expired', $this->expiryRisk['expired'], 'text-error', 'bg-error/5 border-error/20'],
                            ['≤ 30 Days', $this->expiryRisk['d30'], 'text-secondary', 'bg-secondary/5 border-secondary/20'],
                            ['≤ 60 Days', $this->expiryRisk['d60'], 'text-orange-700', 'bg-orange-50 border-orange-200'],
                            ['≤ 90 Days', $this->expiryRisk['d90'], 'text-blue-700', 'bg-blue-50 border-blue-200'],
                            ['Safe', $this->expiryRisk['normal'], 'text-primary', 'bg-primary/5 border-primary/20'],
                        ];
                    @endphp
                    @foreach ($risk as [$label, $count, $color, $bg])
                        <div class="p-3 border rounded-lg text-center {{ $bg }}">
                            <p class="text-label-sm font-bold {{ $color }}">{{ $label }}</p>
                            <p class="text-headline-md font-extrabold {{ $color }}">{{ $count }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Right column --}}
        <div class="xl:col-span-4 space-y-lg">
            {{-- Reorder suggestions --}}
            <div class="bg-white rounded-xl border border-outline-variant custom-shadow overflow-hidden">
                <div class="px-md py-4 border-b border-outline-variant flex justify-between items-center bg-orange-50/50">
                    <h4 class="text-label-md font-bold text-orange-800">Reorder Suggestions</h4>
                    <a href="{{ route('purchases.create') }}" class="text-[11px] font-bold bg-orange-200 text-orange-900 px-2 py-0.5 rounded-full hover:bg-orange-300">CREATE PO</a>
                </div>
                <div class="divide-y divide-outline-variant">
                    @forelse ($this->reorderSuggestions as $r)
                        <div class="px-md py-3 flex items-center justify-between">
                            <div>
                                <p class="text-body-sm font-bold">{{ $r->name }}</p>
                                <p class="text-[11px] text-outline">{{ $r->branch }} • {{ number_format($r->qty) }} left (reorder ≤ {{ $r->reorder_level }})</p>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $r->status === 'out' ? 'bg-red-100 text-red-700' : 'bg-orange-100 text-orange-700' }}">{{ str_replace('_', ' ', $r->status) }}</span>
                        </div>
                    @empty
                        <p class="px-md py-6 text-center text-body-sm text-outline">All stock is above reorder level. 🎉</p>
                    @endforelse
                </div>
            </div>

            {{-- Recent movements --}}
            <div class="bg-white rounded-xl border border-outline-variant custom-shadow overflow-hidden">
                <div class="px-md py-4 border-b border-outline-variant bg-surface-container-low/30">
                    <h4 class="text-label-md font-bold">Recent Stock Movement</h4>
                </div>
                <div class="divide-y divide-outline-variant">
                    @forelse ($this->recentMovements as $mv)
                        @php $isIn = $mv->quantity_in > 0; @endphp
                        <div class="p-md flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $isIn ? 'bg-blue-50' : 'bg-green-50' }}">
                                    <span class="material-symbols-outlined text-[18px] {{ $isIn ? 'text-blue-600' : 'text-green-600' }}">{{ $isIn ? 'add_shopping_cart' : 'shopping_basket' }}</span>
                                </div>
                                <div>
                                    <p class="text-label-md font-bold">{{ \Illuminate\Support\Str::headline($mv->movement_type) }}</p>
                                    <p class="text-[11px] text-on-surface-variant">{{ $mv->branch?->name }} • {{ $mv->medicine?->name }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-body-sm font-bold {{ $isIn ? 'text-blue-600' : 'text-green-600' }}">{{ $isIn ? '+'.$mv->quantity_in : '-'.$mv->quantity_out }}</span>
                                <p class="text-[10px] text-on-surface-variant">{{ $mv->created_at?->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="px-md py-6 text-center text-body-sm text-outline">No stock movements yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
