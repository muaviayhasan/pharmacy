<div class="grid grid-cols-1 lg:grid-cols-3 gap-lg items-start">
    <div class="lg:col-span-2 space-y-lg">
        @error('items')
            <div class="bg-error-container text-on-error-container p-sm px-md rounded-lg flex items-center gap-sm">
                <span class="material-symbols-outlined text-error text-[20px]">error</span><p class="text-body-sm">{{ $message }}</p>
            </div>
        @enderror

        <div class="bg-white rounded-xl border border-outline-variant custom-shadow overflow-hidden">
            <div class="bg-surface-container-low px-md py-sm border-b border-outline-variant">
                <span class="text-label-md font-bold text-primary flex items-center gap-xs"><span class="material-symbols-outlined text-[18px]">tune</span> Create Stock Adjustment</span>
            </div>
            <div class="p-lg grid grid-cols-1 md:grid-cols-2 gap-lg">
                <div class="space-y-sm">
                    <label class="block text-label-sm font-bold text-on-surface-variant">Adjustment Type</label>
                    <select wire:model="adjustmentType" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                        <option value="count">Stock Count (recount)</option>
                        <option value="damage">Damage</option>
                        <option value="expiry">Expiry / Disposal</option>
                        <option value="increase">Manual Increase</option>
                        <option value="decrease">Manual Decrease</option>
                    </select>
                </div>
                <div class="space-y-sm">
                    <label class="block text-label-sm font-bold text-on-surface-variant">Adjustment Date</label>
                    <input type="text" disabled value="{{ now()->format('d M Y') }}" class="w-full border border-outline-variant rounded-lg p-md text-body-sm bg-surface-container-low text-outline">
                </div>
                <div class="space-y-sm md:col-span-2">
                    <label class="block text-label-sm font-bold text-on-surface-variant">Reason / Remarks</label>
                    <textarea wire:model="reason" rows="2" placeholder="Explain the discrepancy..." class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none"></textarea>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-outline-variant custom-shadow p-md space-y-md">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">search</span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Scan barcode or type medicine / batch to add"
                       class="w-full pl-12 pr-4 py-3 border-2 border-outline-variant rounded-xl text-body-md focus:border-primary outline-none">
                @if ($this->searchResults->isNotEmpty())
                    <div class="absolute left-0 right-0 top-full mt-1 bg-white border border-outline-variant rounded-xl shadow-lg z-30 max-h-72 overflow-y-auto">
                        @foreach ($this->searchResults as $b)
                            <button type="button" wire:click="addBatch({{ $b->id }})" class="w-full text-left px-md py-sm hover:bg-surface-container-low flex items-center justify-between border-b border-outline-variant last:border-0">
                                <div><div class="font-label-md">{{ $b->medicine?->name }}</div><div class="text-[11px] text-outline">Batch {{ $b->batch_no }} • Exp {{ $b->expiry_date?->format('m/Y') }}</div></div>
                                <span class="text-label-sm text-outline">{{ $b->available_quantity }} in stock</span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="overflow-x-auto border border-outline-variant rounded-lg">
                <table class="w-full text-left text-body-sm">
                    <thead class="bg-surface-container-low text-on-surface-variant text-label-sm uppercase">
                        <tr><th class="px-md py-sm">Medicine</th><th class="px-md py-sm">Batch / Exp</th><th class="px-md py-sm text-right">System</th><th class="px-md py-sm text-right">Counted</th><th class="px-md py-sm text-right">Diff</th><th class="px-md py-sm"></th></tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        @forelse ($lines as $batchId => $line)
                            @php $diff = (int) $line['counted_qty'] - (int) $line['system_qty']; @endphp
                            <tr wire:key="adj-{{ $batchId }}">
                                <td class="px-md py-sm font-label-md">{{ $line['name'] }}</td>
                                <td class="px-md py-sm">{{ $line['batch_no'] }} <span class="text-outline">• {{ $line['expiry'] }}</span></td>
                                <td class="px-md py-sm text-right">{{ $line['system_qty'] }}</td>
                                <td class="px-md py-sm text-right"><input wire:model.live="lines.{{ $batchId }}.counted_qty" type="number" min="0" class="w-20 border border-outline-variant rounded px-2 py-1 text-right focus:border-primary outline-none"></td>
                                <td class="px-md py-sm text-right font-bold {{ $diff < 0 ? 'text-error' : ($diff > 0 ? 'text-primary' : 'text-outline') }}">{{ $diff > 0 ? '+' : '' }}{{ $diff }}</td>
                                <td class="px-md py-sm text-right"><button type="button" wire:click="removeLine({{ $batchId }})" class="text-error hover:bg-error/10 p-1 rounded"><span class="material-symbols-outlined text-[18px]">delete</span></button></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-md py-lg text-center text-outline">Search above to add batches to count/adjust.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Summary --}}
    <div class="bg-white rounded-xl border-2 border-primary/20 shadow-lg overflow-hidden">
        <div class="bg-primary text-on-primary px-md py-sm flex justify-between items-center">
            <span class="text-label-md font-bold">Adjustment Summary</span>
            <span class="material-symbols-outlined">tune</span>
        </div>
        <div class="p-md space-y-md">
            @php
                $totalDiff = collect($lines)->sum(fn ($l) => (int) $l['counted_qty'] - (int) $l['system_qty']);
                $valueImpact = collect($lines)->sum(fn ($l) => ((int) $l['counted_qty'] - (int) $l['system_qty']) * (float) $l['purchase_price']);
            @endphp
            <div class="flex justify-between text-body-sm"><span class="text-on-surface-variant">Items</span><span class="font-bold">{{ count($lines) }}</span></div>
            <div class="flex justify-between text-body-sm"><span class="text-on-surface-variant">Net Qty Change</span><span class="font-bold {{ $totalDiff < 0 ? 'text-error' : 'text-primary' }}">{{ $totalDiff > 0 ? '+' : '' }}{{ $totalDiff }}</span></div>
            <div class="flex justify-between items-end pt-sm border-t border-dashed border-outline-variant">
                <span class="text-on-surface-variant">Value Impact</span>
                <span class="text-headline-md font-bold {{ $valueImpact < 0 ? 'text-error' : 'text-primary' }}">Rs. {{ number_format($valueImpact, 2) }}</span>
            </div>
            <button wire:click="save" wire:loading.attr="disabled" @disabled(count($lines) === 0)
                    class="w-full py-3 bg-primary-container text-white rounded-lg font-bold hover:brightness-110 active:scale-95 transition-all disabled:opacity-40 flex items-center justify-center gap-sm">
                <span class="material-symbols-outlined">send</span> Submit for Approval
            </button>
            <p class="text-[11px] text-outline text-center">Stock changes apply only after a manager approves the adjustment.</p>
        </div>
    </div>
</div>
