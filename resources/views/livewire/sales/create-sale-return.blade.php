<div class="grid grid-cols-1 lg:grid-cols-3 gap-lg items-start">
    <div class="lg:col-span-2 space-y-lg">
        @error('items')
            <div class="bg-error-container text-on-error-container p-sm px-md rounded-lg flex items-center gap-sm"><span class="material-symbols-outlined text-error text-[20px]">error</span><p class="text-body-sm">{{ $message }}</p></div>
        @enderror

        {{-- Select sale --}}
        <div class="bg-white rounded-xl border border-outline-variant custom-shadow p-md">
            @if (! $this->sale)
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">search</span>
                    <input wire:model.live.debounce.300ms="saleSearch" type="text" placeholder="Search the original sale invoice no (e.g. SL-MAIN-...)"
                           class="w-full pl-12 pr-4 py-3 border-2 border-outline-variant rounded-xl text-body-md focus:border-primary outline-none">
                    @if ($this->searchResults->isNotEmpty())
                        <div class="absolute left-0 right-0 top-full mt-1 bg-white border border-outline-variant rounded-xl shadow-lg z-30 max-h-72 overflow-y-auto">
                            @foreach ($this->searchResults as $s)
                                <button type="button" wire:click="selectSale({{ $s->id }})" class="w-full text-left px-md py-sm hover:bg-surface-container-low flex items-center justify-between border-b border-outline-variant last:border-0">
                                    <div><div class="font-label-md">{{ $s->sale_no }}</div><div class="text-[11px] text-outline">{{ $s->customer?->name ?? 'Walk-in' }} • {{ $s->sale_date?->format('d M Y') }}</div></div>
                                    <span class="text-label-md text-primary">Rs. {{ number_format($s->grand_total, 0) }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                <div class="flex items-center justify-between">
                    <div><p class="font-label-md text-on-surface">{{ $this->sale->sale_no }}</p><p class="text-[11px] text-outline">{{ $this->sale->customer?->name ?? 'Walk-in' }} • {{ $this->sale->sale_date?->format('d M Y') }}</p></div>
                    <button wire:click="clearSale" class="text-outline hover:text-error flex items-center gap-xs text-label-sm"><span class="material-symbols-outlined text-[18px]">close</span> Change</button>
                </div>
            @endif
        </div>

        {{-- Items --}}
        @if ($this->sale)
            <div class="bg-white rounded-xl border border-outline-variant custom-shadow overflow-hidden">
                <div class="bg-surface-container-low px-md py-sm border-b border-outline-variant"><span class="text-label-md font-bold text-primary">Return Items Processing</span></div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-body-sm">
                        <thead class="bg-surface-container-low text-on-surface-variant text-label-sm uppercase">
                            <tr><th class="px-md py-sm">Medicine</th><th class="px-md py-sm">Batch</th><th class="px-md py-sm text-right">Remaining</th><th class="px-md py-sm text-right">Return Qty</th><th class="px-md py-sm text-center">Restock</th><th class="px-md py-sm text-right">Refund</th></tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            @forelse ($lines as $itemId => $line)
                                <tr wire:key="sr-{{ $itemId }}">
                                    <td class="px-md py-sm font-label-md">{{ $line['name'] }}</td>
                                    <td class="px-md py-sm">{{ $line['batch'] ?? '—' }}</td>
                                    <td class="px-md py-sm text-right">{{ $line['remaining'] }}</td>
                                    <td class="px-md py-sm text-right"><input wire:model.live="lines.{{ $itemId }}.quantity" type="number" min="0" max="{{ $line['remaining'] }}" class="w-20 border border-outline-variant rounded px-2 py-1 text-right focus:border-primary outline-none"></td>
                                    <td class="px-md py-sm text-center"><input wire:model.live="lines.{{ $itemId }}.restock" type="checkbox" class="w-4 h-4 text-primary rounded border-outline-variant focus:ring-primary"></td>
                                    <td class="px-md py-sm text-right font-bold text-primary">Rs. {{ number_format($line['line_unit'] * (int) $line['quantity'], 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-md py-lg text-center text-outline">All items on this invoice are already returned.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    {{-- Summary --}}
    <div class="bg-white rounded-xl border-2 border-primary/20 shadow-lg overflow-hidden">
        <div class="bg-primary text-on-primary px-md py-sm flex justify-between items-center"><span class="text-label-md font-bold">Refund Summary</span><span class="material-symbols-outlined">assignment_return</span></div>
        <div class="p-md space-y-md">
            <div class="flex justify-between items-end pt-sm">
                <span class="text-on-surface-variant">Total Refund</span>
                <span class="text-headline-md font-bold text-primary">Rs. {{ number_format($this->refundTotal, 2) }}</span>
            </div>
            <div>
                <label class="block text-label-sm font-bold text-on-surface-variant mb-sm">Refund Method</label>
                <select wire:model="refundMethod" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                    <option value="cash">Cash</option>
                    <option value="card">Card</option>
                    <option value="ledger_credit">Customer Credit Note</option>
                </select>
            </div>
            <div>
                <label class="block text-label-sm font-bold text-on-surface-variant mb-sm">Reason</label>
                <textarea wire:model="reason" rows="2" placeholder="Reason for return..." class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none"></textarea>
            </div>
            <button wire:click="save" wire:loading.attr="disabled" @disabled(! $this->sale || $this->refundTotal <= 0)
                    class="w-full py-3 bg-primary-container text-white rounded-lg font-bold hover:brightness-110 active:scale-95 transition-all disabled:opacity-40 flex items-center justify-center gap-sm">
                <span class="material-symbols-outlined">check_circle</span> Process Return
            </button>
        </div>
    </div>
</div>
