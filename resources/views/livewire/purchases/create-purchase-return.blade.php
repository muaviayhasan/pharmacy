<div class="grid grid-cols-1 lg:grid-cols-3 gap-lg items-start">
    <div class="lg:col-span-2 space-y-lg">
        @error('items')
            <div class="bg-error-container text-on-error-container p-sm px-md rounded-lg flex items-center gap-sm"><span class="material-symbols-outlined text-error text-[20px]">error</span><p class="text-body-sm">{{ $message }}</p></div>
        @enderror

        <div class="bg-white rounded-xl border border-outline-variant custom-shadow p-md">
            @if (! $this->purchase)
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">search</span>
                    <input wire:model.live.debounce.300ms="purchaseSearch" type="text" placeholder="Search the original purchase invoice (PINV-... or supplier invoice)"
                           class="w-full pl-12 pr-4 py-3 border-2 border-outline-variant rounded-xl text-body-md focus:border-primary outline-none">
                    @if ($this->searchResults->isNotEmpty())
                        <div class="absolute left-0 right-0 top-full mt-1 bg-white border border-outline-variant rounded-xl shadow-lg z-30 max-h-72 overflow-y-auto">
                            @foreach ($this->searchResults as $p)
                                <button type="button" wire:click="selectPurchase({{ $p->id }})" class="w-full text-left px-md py-sm hover:bg-surface-container-low flex items-center justify-between border-b border-outline-variant last:border-0">
                                    <div><div class="font-label-md">{{ $p->purchase_no }}</div><div class="text-[11px] text-outline">{{ $p->supplier?->name }} • {{ $p->invoice_date?->format('d M Y') }}</div></div>
                                    <span class="text-label-md text-primary">Rs. {{ number_format($p->grand_total, 0) }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                <div class="flex items-center justify-between">
                    <div><p class="font-label-md text-on-surface">{{ $this->purchase->purchase_no }}</p><p class="text-[11px] text-outline">{{ $this->purchase->supplier?->name }} • {{ $this->purchase->invoice_date?->format('d M Y') }}</p></div>
                    <button wire:click="clearPurchase" class="text-outline hover:text-error flex items-center gap-xs text-label-sm"><span class="material-symbols-outlined text-[18px]">close</span> Change Invoice</button>
                </div>
            @endif
        </div>

        @if ($this->purchase)
            <div class="bg-white rounded-xl border border-outline-variant custom-shadow overflow-hidden">
                <div class="bg-surface-container-low px-md py-sm border-b border-outline-variant"><span class="text-label-md font-bold text-primary">Returnable Batches</span></div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-body-sm">
                        <thead class="bg-surface-container-low text-on-surface-variant text-label-sm uppercase">
                            <tr><th class="px-md py-sm">Medicine & Batch</th><th class="px-md py-sm">Expiry</th><th class="px-md py-sm text-right">Available</th><th class="px-md py-sm text-right">Unit Cost</th><th class="px-md py-sm text-right">Return Qty</th><th class="px-md py-sm text-right">Amount</th></tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            @forelse ($lines as $batchId => $line)
                                <tr wire:key="pr-{{ $batchId }}">
                                    <td class="px-md py-sm"><div class="font-label-md">{{ $line['name'] }}</div><div class="text-[11px] text-outline">{{ $line['batch'] }}</div></td>
                                    <td class="px-md py-sm">{{ $line['expiry'] }}</td>
                                    <td class="px-md py-sm text-right">{{ $line['available'] }}</td>
                                    <td class="px-md py-sm text-right">Rs. {{ number_format($line['unit'], 2) }}</td>
                                    <td class="px-md py-sm text-right"><input wire:model.live="lines.{{ $batchId }}.quantity" type="number" min="0" max="{{ $line['available'] }}" class="w-20 border border-outline-variant rounded px-2 py-1 text-right focus:border-primary outline-none"></td>
                                    <td class="px-md py-sm text-right font-bold text-primary">Rs. {{ number_format($line['unit'] * (int) $line['quantity'], 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-md py-lg text-center text-outline">No returnable batches remain for this purchase.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <div class="bg-white rounded-xl border-2 border-primary/20 shadow-lg overflow-hidden">
        <div class="bg-primary text-on-primary px-md py-sm flex justify-between items-center"><span class="text-label-md font-bold">Return Summary</span><span class="material-symbols-outlined">assignment_return</span></div>
        <div class="p-md space-y-md">
            <div class="flex justify-between items-end"><span class="text-on-surface-variant">Return Amount</span><span class="text-headline-md font-bold text-primary">Rs. {{ number_format($this->returnTotal, 2) }}</span></div>
            <div>
                <label class="block text-label-sm font-bold text-on-surface-variant mb-sm">Settlement</label>
                <select wire:model="settlement" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                    <option value="ledger_adjust">Adjust Supplier Ledger</option>
                    <option value="refund">Cash/Bank Refund</option>
                </select>
            </div>
            <div>
                <label class="block text-label-sm font-bold text-on-surface-variant mb-sm">Reason</label>
                <textarea wire:model="reason" rows="2" placeholder="Damaged / expired / wrong item..." class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none"></textarea>
            </div>
            <button wire:click="save" wire:loading.attr="disabled" @disabled(! $this->purchase || $this->returnTotal <= 0)
                    class="w-full py-3 bg-primary-container text-white rounded-lg font-bold hover:brightness-110 active:scale-95 transition-all disabled:opacity-40 flex items-center justify-center gap-sm">
                <span class="material-symbols-outlined">check_circle</span> Process Return
            </button>
        </div>
    </div>
</div>
