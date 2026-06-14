<div class="space-y-lg">
    <a href="{{ route('purchases.index') }}" wire:navigate class="inline-flex items-center gap-xs text-primary text-label-md hover:underline">
        <span class="material-symbols-outlined text-[20px]">arrow_back</span> Back to Purchases
    </a>

    @error('items')
        <div class="bg-error-container text-on-error-container p-sm px-md rounded-lg flex items-center gap-sm">
            <span class="material-symbols-outlined text-error text-[20px]">error</span>
            <p class="text-body-sm">{{ $message }}</p>
        </div>
    @enderror

    <div class="grid grid-cols-1 lg:grid-cols-10 gap-lg items-start">
        {{-- Left: details + items --}}
        <div class="lg:col-span-7 space-y-lg">
            {{-- Supplier & invoice --}}
            <div class="bg-white rounded-xl border border-outline-variant custom-shadow overflow-hidden">
                <div class="bg-surface-container-low px-md py-sm border-b border-outline-variant">
                    <span class="text-label-md font-bold text-primary flex items-center gap-xs"><span class="material-symbols-outlined text-[18px]">person</span> Supplier &amp; Invoice Details</span>
                </div>
                <div class="p-md grid grid-cols-1 md:grid-cols-2 gap-lg">
                    <div class="space-y-md">
                        <div>
                            <label class="block text-label-sm font-bold text-on-surface-variant mb-1">Supplier <span class="text-error">*</span></label>
                            <select wire:model.live="supplierId" class="w-full border border-outline-variant rounded-lg p-2 text-body-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                                <option value="">Select supplier</option>
                                @foreach ($this->suppliers as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                            @error('supplierId') <p class="text-body-sm text-error mt-xs">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid grid-cols-2 gap-sm">
                            <div>
                                <label class="block text-label-sm font-bold text-on-surface-variant mb-1">Supplier Invoice #</label>
                                <input wire:model="supplierInvoiceNo" type="text" class="w-full border border-outline-variant rounded-lg p-2 text-body-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                            </div>
                            <div>
                                <label class="block text-label-sm font-bold text-on-surface-variant mb-1">Purchase Date <span class="text-error">*</span></label>
                                <input wire:model="invoiceDate" type="date" class="w-full border border-outline-variant rounded-lg p-2 text-body-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                            </div>
                        </div>
                        @if ($this->supplierInfo)
                            <div class="p-sm bg-primary/5 rounded-lg border border-primary/20 flex items-start gap-md">
                                <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center text-primary"><span class="material-symbols-outlined">store</span></div>
                                <div>
                                    <p class="text-label-md font-bold text-primary">{{ $this->supplierInfo->name }}</p>
                                    <p class="text-body-sm text-on-surface-variant">Phone: {{ $this->supplierInfo->phone ?? '—' }}</p>
                                    <p class="text-label-sm text-error font-bold mt-1">Payable: Rs. {{ number_format($this->supplierInfo->current_balance, 0) }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="space-y-md">
                        <div class="grid grid-cols-2 gap-sm">
                            <div>
                                <label class="block text-label-sm font-bold text-on-surface-variant mb-1">Receiving Branch</label>
                                <select wire:model="branchId" class="w-full border border-outline-variant rounded-lg p-2 text-body-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                                    @foreach ($this->branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-label-sm font-bold text-on-surface-variant mb-1">Due Date</label>
                                <input wire:model="dueDate" type="date" class="w-full border border-outline-variant rounded-lg p-2 text-body-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="block text-label-sm font-bold text-on-surface-variant mb-1">Notes / Remarks</label>
                            <textarea wire:model="notes" rows="3" placeholder="Any internal notes..." class="w-full border border-outline-variant rounded-lg p-2 text-body-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Medicine search --}}
            <div class="bg-white rounded-xl border border-outline-variant custom-shadow p-md space-y-md">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant">search</span>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search medicine by name, generic, or barcode to add"
                           class="w-full pl-12 pr-4 py-3 border-2 border-outline-variant rounded-xl text-body-md focus:border-primary outline-none transition-all">
                    @if ($this->searchResults->isNotEmpty())
                        <div class="absolute left-0 right-0 top-full mt-1 bg-white border border-outline-variant rounded-xl shadow-lg z-30 max-h-72 overflow-y-auto custom-scrollbar">
                            @foreach ($this->searchResults as $m)
                                <button type="button" wire:click="addMedicine({{ $m->id }})" class="w-full text-left px-md py-sm hover:bg-surface-container-low flex items-center justify-between border-b border-outline-variant last:border-0">
                                    <div>
                                        <div class="font-label-md text-on-surface">{{ $m->name }}</div>
                                        <div class="text-[11px] text-outline">{{ $m->generic_name }} • {{ $m->manufacturer?->name }}</div>
                                    </div>
                                    <span class="material-symbols-outlined text-primary">add_circle</span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Items table --}}
                <div class="overflow-x-auto custom-scrollbar border border-outline-variant rounded-lg">
                    <table class="w-full text-left border-collapse min-w-[900px]">
                        <thead class="bg-surface-container-low text-on-surface-variant text-label-sm uppercase tracking-wider font-bold">
                            <tr>
                                <th class="px-md py-sm">Medicine</th>
                                <th class="px-md py-sm">Batch No</th>
                                <th class="px-md py-sm">Expiry</th>
                                <th class="px-md py-sm">Qty</th>
                                <th class="px-md py-sm">Bonus</th>
                                <th class="px-md py-sm">Purchase</th>
                                <th class="px-md py-sm">Sale</th>
                                <th class="px-md py-sm">Tax %</th>
                                <th class="px-md py-sm text-right">Total</th>
                                <th class="px-md py-sm"></th>
                            </tr>
                        </thead>
                        <tbody class="text-body-sm divide-y divide-outline-variant">
                            @forelse ($lines as $i => $line)
                                @php $lt = round($line['purchase_price'] * $line['quantity'] * (1 + ($line['tax_percent'] ?? 0) / 100), 2); @endphp
                                <tr wire:key="pline-{{ $i }}" class="hover:bg-surface-container-low/30">
                                    <td class="px-md py-md">
                                        <div class="font-bold text-on-surface">{{ $line['name'] }}</div>
                                        <div class="text-[10px] text-on-surface-variant">{{ $line['generic'] }} | {{ $line['manufacturer'] }}</div>
                                    </td>
                                    <td class="px-md py-md"><input wire:model.live.blur="lines.{{ $i }}.batch_no" type="text" class="w-24 border border-outline-variant rounded px-1 py-1 text-body-sm focus:border-primary outline-none"></td>
                                    <td class="px-md py-md"><input wire:model.live.blur="lines.{{ $i }}.expiry_date" type="date" class="w-32 border border-outline-variant rounded px-1 py-1 text-body-sm focus:border-primary outline-none"></td>
                                    <td class="px-md py-md"><input wire:model.live="lines.{{ $i }}.quantity" type="number" min="1" class="w-16 border border-outline-variant rounded px-1 py-1 text-body-sm focus:border-primary outline-none"></td>
                                    <td class="px-md py-md"><input wire:model.live="lines.{{ $i }}.bonus_quantity" type="number" min="0" class="w-14 border border-outline-variant rounded px-1 py-1 text-body-sm focus:border-primary outline-none"></td>
                                    <td class="px-md py-md"><input wire:model.live="lines.{{ $i }}.purchase_price" type="number" step="0.01" min="0" class="w-20 border border-outline-variant rounded px-1 py-1 text-body-sm focus:border-primary outline-none"></td>
                                    <td class="px-md py-md"><input wire:model.live="lines.{{ $i }}.sale_price" type="number" step="0.01" min="0" class="w-20 border border-outline-variant rounded px-1 py-1 text-body-sm focus:border-primary outline-none"></td>
                                    <td class="px-md py-md"><input wire:model.live="lines.{{ $i }}.tax_percent" type="number" step="0.01" min="0" class="w-14 border border-outline-variant rounded px-1 py-1 text-body-sm focus:border-primary outline-none"></td>
                                    <td class="px-md py-md text-right font-bold text-primary whitespace-nowrap">Rs. {{ number_format($lt, 2) }}</td>
                                    <td class="px-md py-md text-right">
                                        <button type="button" wire:click="removeLine({{ $i }})" class="text-error hover:bg-error/10 p-1 rounded"><span class="material-symbols-outlined">delete</span></button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="px-md py-2xl text-center text-outline">Search above to add medicines to this purchase.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <p class="text-[11px] text-on-surface-variant flex items-center gap-xs">
                    <span class="material-symbols-outlined text-[16px]">info</span>
                    Each batch is stored separately with its own expiry, cost, sale price and quantity (qty + bonus added to stock).
                </p>
            </div>
        </div>

        {{-- Right: summary --}}
        <div class="lg:col-span-3 space-y-lg">
            <div class="bg-white rounded-xl border-2 border-primary/20 shadow-lg overflow-hidden">
                <div class="bg-primary text-on-primary px-md py-sm flex justify-between items-center">
                    <span class="text-label-md font-bold">Purchase Summary</span>
                    <span class="material-symbols-outlined">receipt_long</span>
                </div>
                <div class="p-md space-y-md">
                    <div class="space-y-sm text-body-sm">
                        <div class="flex justify-between text-on-surface-variant"><span>Total Items</span><span class="font-bold text-on-surface">{{ str_pad((string) count($lines), 2, '0', STR_PAD_LEFT) }}</span></div>
                        <div class="flex justify-between text-on-surface-variant"><span>Total Qty (incl. bonus)</span><span class="font-bold text-on-surface">{{ $this->totalQty }}</span></div>
                        <div class="border-t border-dashed border-outline-variant pt-sm">
                            <div class="flex justify-between"><span>Subtotal</span><span class="font-bold">Rs. {{ number_format($this->subtotal, 2) }}</span></div>
                            <div class="flex justify-between"><span>Tax</span><span class="font-bold">Rs. {{ number_format($this->taxTotal, 2) }}</span></div>
                            <div class="flex justify-between items-center mt-xs">
                                <span>Discount</span>
                                <input wire:model.live="discount" type="number" step="0.01" min="0" class="w-24 border border-outline-variant rounded px-2 py-1 text-right text-body-sm focus:border-primary outline-none">
                            </div>
                        </div>
                    </div>
                    <div class="bg-primary-container/10 p-md rounded-lg flex justify-between items-center">
                        <span class="text-label-md font-bold text-primary">Grand Total</span>
                        <span class="text-headline-md font-extrabold text-primary">Rs. {{ number_format($this->grandTotal, 2) }}</span>
                    </div>

                    <div>
                        <label class="block text-label-sm font-bold text-on-surface-variant mb-sm">Payment Mode</label>
                        <div class="grid grid-cols-2 gap-xs">
                            @foreach (['cash' => 'Cash', 'bank' => 'Bank', 'cheque' => 'Cheque', 'credit' => 'Credit'] as $val => $label)
                                <button type="button" wire:click="setPaymentType('{{ $val }}')"
                                        class="py-1.5 border rounded text-xs font-bold transition-all {{ $paymentType === $val ? 'border-primary text-primary bg-primary/5' : 'border-outline-variant text-on-surface-variant hover:bg-surface-container-low' }}">{{ $label }}</button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-label-sm font-bold text-on-surface-variant mb-1">Paid Amount</label>
                        <input wire:model.live="paidAmount" type="number" step="0.01" min="0" class="w-full border-2 border-primary/20 rounded-lg p-2 text-headline-md font-bold text-center outline-none focus:border-primary">
                    </div>
                    <div class="bg-error/5 p-sm rounded-lg flex justify-between items-center border border-error/20">
                        <span class="text-label-sm text-error font-bold">Balance Due</span>
                        <span class="text-label-md text-error font-extrabold">Rs. {{ number_format(max($this->dueAmount, 0), 2) }}</span>
                    </div>

                    <button wire:click="save" wire:loading.attr="disabled" @disabled(count($lines) === 0 || ! $supplierId)
                            class="w-full py-3 bg-primary-container text-white rounded-lg font-bold shadow-md hover:brightness-110 active:scale-95 transition-all disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-sm">
                        <span wire:loading.remove wire:target="save" class="flex items-center gap-sm"><span class="material-symbols-outlined">save</span> Save Purchase</span>
                        <span wire:loading wire:target="save" class="flex items-center gap-sm"><span class="material-symbols-outlined animate-spin">progress_activity</span> Saving...</span>
                    </button>
                </div>
            </div>

            {{-- Ledger preview --}}
            @if ($this->supplierInfo)
                <div class="bg-white rounded-xl border border-outline-variant custom-shadow overflow-hidden">
                    <div class="bg-surface-container-low px-md py-sm border-b border-outline-variant">
                        <span class="text-label-sm font-bold text-on-surface-variant uppercase tracking-wider">Supplier Ledger Preview</span>
                    </div>
                    <div class="p-md space-y-xs text-body-sm">
                        <div class="flex justify-between"><span class="text-on-surface-variant">Prev. Payable</span><span class="font-bold">Rs. {{ number_format($this->supplierInfo->current_balance, 2) }}</span></div>
                        <div class="flex justify-between"><span class="text-on-surface-variant">Current Purchase</span><span class="font-bold">+ Rs. {{ number_format($this->grandTotal, 2) }}</span></div>
                        <div class="flex justify-between text-primary"><span class="text-on-surface-variant">Paid Now</span><span class="font-bold">- Rs. {{ number_format($this->paidAmount, 2) }}</span></div>
                        <div class="border-t border-outline-variant pt-xs flex justify-between"><span class="font-bold">New Payable</span><span class="font-extrabold text-error">Rs. {{ number_format($this->supplierInfo->current_balance + $this->grandTotal - $this->paidAmount, 2) }}</span></div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
