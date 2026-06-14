<div class="space-y-md"
     x-data="{ toast: null }"
     x-on:sale-completed.window="toast = 'Sale ' + ($event.detail.saleNo ?? '') + ' completed'; setTimeout(() => toast = null, 4000)"
     x-on:sale-broadcast.window="toast = 'New sale on this branch: ' + ($event.detail.saleNo ?? ''); setTimeout(() => toast = null, 4000)">

    {{-- Toast --}}
    <div x-show="toast" x-transition x-cloak
         class="fixed top-20 right-8 z-50 bg-inverse-surface text-white px-md py-sm rounded-lg shadow-xl flex items-center gap-sm">
        <span class="material-symbols-outlined text-primary-fixed">check_circle</span>
        <span class="text-body-sm" x-text="toast"></span>
    </div>

    {{-- Shift summary strip --}}
    <div class="bg-inverse-surface text-surface-variant rounded-xl px-lg py-sm flex flex-wrap items-center justify-between gap-md text-[12px] font-medium">
        <div class="flex flex-wrap gap-xl">
            <span class="flex items-center gap-xs"><span class="material-symbols-outlined text-[16px] text-primary-fixed">savings</span> Opening Cash: <span class="text-white">Rs. {{ number_format($this->shift?->opening_cash ?? 0, 2) }}</span></span>
            <span class="flex items-center gap-xs"><span class="material-symbols-outlined text-[16px] text-primary-fixed">trending_up</span> Total Sales: <span class="text-white">Rs. {{ number_format(($this->shift?->cash_sales ?? 0) + ($this->shift?->card_sales ?? 0) + ($this->shift?->bank_sales ?? 0) + ($this->shift?->credit_sales ?? 0), 2) }}</span></span>
            <span class="flex items-center gap-xs"><span class="material-symbols-outlined text-[16px] text-primary-fixed">account_balance_wallet</span> Expected Cash: <span class="text-white">Rs. {{ number_format($this->shift?->expected_cash ?? 0, 2) }}</span></span>
        </div>
        <span class="flex items-center gap-xs text-green-400"><span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span> Shift #{{ $this->shift?->shift_no }}</span>
    </div>

    {{-- Prescription warning --}}
    @if ($this->requiresPrescription)
        <div class="bg-error-container/40 border border-error/20 p-sm px-md rounded-lg flex items-center gap-sm">
            <span class="material-symbols-outlined text-error text-[20px]">warning</span>
            <p class="text-body-sm text-on-error-container"><strong>Warning:</strong> The cart contains prescription-required medicine. Verification will be flagged on this sale.</p>
        </div>
    @endif

    @error('cart')
        <div class="bg-error-container text-on-error-container p-sm px-md rounded-lg flex items-center gap-sm">
            <span class="material-symbols-outlined text-error text-[20px]">error</span>
            <p class="text-body-sm">{{ $message }}</p>
        </div>
    @enderror

    <div class="flex flex-col lg:flex-row gap-md">
        {{-- LEFT: search + cart --}}
        <div class="lg:w-[65%] flex flex-col gap-md">
            {{-- Search --}}
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-md flex items-center pointer-events-none">
                    <span class="material-symbols-outlined text-outline">search</span>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" autofocus
                       class="w-full pl-12 pr-12 py-4 bg-white border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition-all text-body-md shadow-sm"
                       placeholder="Scan barcode or search medicine by name, generic, or batch">
                <div class="absolute inset-y-0 right-0 pr-md flex items-center gap-sm">
                    <span class="material-symbols-outlined text-primary">barcode_scanner</span>
                </div>

                {{-- Suggestions --}}
                @if ($this->searchResults->isNotEmpty())
                    <div class="absolute left-0 right-0 top-full mt-1 bg-white border border-outline-variant rounded-xl shadow-lg z-30 max-h-72 overflow-y-auto custom-scrollbar">
                        @foreach ($this->searchResults as $r)
                            <button type="button" wire:click="addToCart({{ $r['batch_id'] }})"
                                    class="w-full text-left px-md py-sm hover:bg-surface-container-low flex items-center justify-between border-b border-outline-variant last:border-0">
                                <div>
                                    <div class="font-label-md text-on-surface flex items-center gap-sm">
                                        {{ $r['name'] }}
                                        @if ($r['prescription_required'])
                                            <span class="px-sm py-[1px] bg-secondary/10 text-secondary text-[10px] font-bold rounded">Rx</span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-outline">{{ $r['generic'] }} • {{ $r['manufacturer'] }} • Batch {{ $r['batch_no'] }} ({{ $r['expiry'] }})</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-label-md text-primary">Rs. {{ number_format($r['price'], 2) }}</div>
                                    <div class="text-[10px] text-outline">{{ $r['available'] }} in stock</div>
                                </div>
                            </button>
                        @endforeach
                    </div>
                @elseif (strlen(trim($search)) >= 2)
                    <div class="absolute left-0 right-0 top-full mt-1 bg-white border border-outline-variant rounded-xl shadow-lg z-30 px-md py-sm text-body-sm text-outline">
                        No sellable stock found for "{{ $search }}" in this branch.
                    </div>
                @endif
            </div>

            {{-- Cart --}}
            <div class="bg-white rounded-xl border border-outline-variant shadow-sm overflow-hidden flex flex-col min-h-[360px]">
                @if (count($cart))
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-surface-container-low">
                                <tr>
                                    <th class="px-md py-sm font-label-md text-on-surface-variant border-b border-outline-variant">Medicine</th>
                                    <th class="px-md py-sm font-label-md text-on-surface-variant border-b border-outline-variant">Batch / Exp</th>
                                    <th class="px-md py-sm font-label-md text-on-surface-variant border-b border-outline-variant">Qty</th>
                                    <th class="px-md py-sm font-label-md text-on-surface-variant border-b border-outline-variant">Price</th>
                                    <th class="px-md py-sm font-label-md text-on-surface-variant border-b border-outline-variant">Disc %</th>
                                    <th class="px-md py-sm font-label-md text-on-surface-variant border-b border-outline-variant">Total</th>
                                    <th class="px-md py-sm font-label-md text-on-surface-variant border-b border-outline-variant"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant">
                                @foreach ($cart as $batchId => $line)
                                    @php
                                        $lineSub = $line['price'] * $line['qty'];
                                        $lineDisc = round($lineSub * ($line['discount_percent'] ?? 0) / 100, 2);
                                        $lineTax = round(($lineSub - $lineDisc) * ($line['tax_percent'] ?? 0) / 100, 2);
                                        $lineTotal = $lineSub - $lineDisc + $lineTax;
                                    @endphp
                                    <tr wire:key="line-{{ $batchId }}" class="hover:bg-surface-container-low transition-colors">
                                        <td class="px-md py-md">
                                            <div class="font-label-md text-on-surface">{{ $line['name'] }}</div>
                                            <div class="text-[11px] text-outline">{{ $line['generic'] }}</div>
                                            @if ($line['prescription_required'])
                                                <span class="mt-1 inline-block px-sm py-[2px] bg-secondary/10 text-secondary text-[10px] font-bold rounded">Prescription Required</span>
                                            @endif
                                        </td>
                                        <td class="px-md py-md">
                                            <div class="text-body-sm">{{ $line['batch_no'] }}</div>
                                            <div class="text-[11px] text-on-surface-variant">{{ $line['expiry'] }}</div>
                                        </td>
                                        <td class="px-md py-md">
                                            <div class="flex items-center gap-xs">
                                                <button type="button" wire:click="changeQty({{ $batchId }}, -1)" class="w-7 h-7 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-low flex items-center justify-center">
                                                    <span class="material-symbols-outlined text-[16px]">remove</span>
                                                </button>
                                                <span class="w-8 text-center font-label-md">{{ $line['qty'] }}</span>
                                                <button type="button" wire:click="changeQty({{ $batchId }}, 1)" class="w-7 h-7 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container-low flex items-center justify-center">
                                                    <span class="material-symbols-outlined text-[16px]">add</span>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="px-md py-md text-body-sm">Rs. {{ number_format($line['price'], 2) }}</td>
                                        <td class="px-md py-md">
                                            <input type="number" min="0" max="100" wire:model.live.blur="cart.{{ $batchId }}.discount_percent"
                                                   class="w-16 border-outline-variant rounded-lg p-1.5 text-center text-body-sm focus:ring-primary">
                                        </td>
                                        <td class="px-md py-md font-label-md">Rs. {{ number_format($lineTotal, 2) }}</td>
                                        <td class="px-md py-md">
                                            <button type="button" wire:click="removeLine({{ $batchId }})" class="p-1.5 text-outline hover:text-error transition-colors">
                                                <span class="material-symbols-outlined">delete</span>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="flex-1 flex flex-col items-center justify-center opacity-40 py-2xl">
                        <span class="material-symbols-outlined text-[64px]">shopping_basket</span>
                        <p class="text-body-md mt-sm">No items in cart — search to add medicine</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- RIGHT: customer + checkout --}}
        <div class="lg:w-[35%] flex flex-col gap-md">
            {{-- Customer --}}
            <div class="bg-white p-md rounded-xl border border-outline-variant shadow-sm flex flex-col gap-sm">
                <h3 class="font-label-md text-on-surface-variant flex items-center gap-xs">
                    <span class="material-symbols-outlined text-[18px]">person</span> Customer Details
                </h3>
                <div class="relative">
                    <input wire:model.live.debounce.300ms="customerSearch" type="text"
                           class="w-full pl-10 pr-md py-sm bg-surface-container-low border-none rounded-lg focus:ring-2 focus:ring-primary text-body-sm"
                           placeholder="Search by mobile or name...">
                    <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
                    @if ($this->customerResults->isNotEmpty())
                        <div class="absolute left-0 right-0 top-full mt-1 bg-white border border-outline-variant rounded-lg shadow-lg z-30 max-h-48 overflow-y-auto">
                            @foreach ($this->customerResults as $c)
                                <button type="button" wire:click="selectCustomer({{ $c->id }})" class="w-full text-left px-md py-sm hover:bg-surface-container-low border-b border-outline-variant last:border-0">
                                    <div class="font-label-md text-on-surface">{{ $c->name }}</div>
                                    <div class="text-[11px] text-outline">{{ $c->phone ?? 'No phone' }} • {{ ucfirst($c->customer_type) }}</div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="flex items-center gap-md p-sm bg-primary/5 rounded-xl border border-primary/20">
                    <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined">account_circle</span>
                    </div>
                    <div class="flex-1">
                        <div class="font-label-md text-on-surface">{{ $customerName }}</div>
                        <div class="text-[11px] text-outline">{{ $customerId ? 'Registered customer' : 'Cash sale • walk-in' }}</div>
                    </div>
                    @if ($customerId)
                        <button type="button" wire:click="clearCustomer" class="text-outline hover:text-error"><span class="material-symbols-outlined text-[18px]">close</span></button>
                    @endif
                </div>
            </div>

            {{-- Billing summary --}}
            <div class="bg-white rounded-xl border border-outline-variant shadow-sm flex flex-col overflow-hidden">
                <div class="p-md space-y-sm border-b border-outline-variant">
                    <div class="flex justify-between text-body-sm"><span class="text-on-surface-variant">Subtotal</span><span class="font-medium">Rs. {{ number_format($this->subtotal, 2) }}</span></div>
                    <div class="flex justify-between text-body-sm"><span class="text-on-surface-variant">Discount</span><span class="font-medium text-error">- Rs. {{ number_format($this->discountTotal, 2) }}</span></div>
                    <div class="flex justify-between text-body-sm"><span class="text-on-surface-variant">Tax</span><span class="font-medium">Rs. {{ number_format($this->taxTotal, 2) }}</span></div>
                    <div class="flex justify-between items-end pt-sm border-t border-dashed border-outline-variant">
                        <span class="font-headline-md text-on-surface">Total</span>
                        <span class="font-headline-xl text-primary">Rs. {{ number_format($this->grandTotal, 2) }}</span>
                    </div>
                </div>

                {{-- Payment --}}
                <div class="p-md space-y-md">
                    <div>
                        <label class="text-label-sm text-outline block mb-sm uppercase tracking-wider">Payment Method</label>
                        <div class="grid grid-cols-{{ $customerId ? '4' : '3' }} gap-sm">
                            @foreach (['cash' => 'payments', 'card' => 'credit_card', 'bank' => 'account_balance'] as $method => $icon)
                                <button type="button" wire:click="setPayment('{{ $method }}')"
                                        class="p-sm border-2 rounded-xl flex flex-col items-center gap-xs transition-all {{ $paymentMethod === $method ? 'border-primary bg-primary/5 text-primary' : 'border-outline-variant text-on-surface-variant hover:border-primary/50' }}">
                                    <span class="material-symbols-outlined">{{ $icon }}</span>
                                    <span class="text-[11px] font-bold uppercase">{{ $method }}</span>
                                </button>
                            @endforeach
                            @if ($customerId)
                                <button type="button" wire:click="setPayment('credit')"
                                        class="p-sm border-2 rounded-xl flex flex-col items-center gap-xs transition-all {{ $paymentMethod === 'credit' ? 'border-primary bg-primary/5 text-primary' : 'border-outline-variant text-on-surface-variant hover:border-primary/50' }}">
                                    <span class="material-symbols-outlined">schedule</span>
                                    <span class="text-[11px] font-bold uppercase">Credit</span>
                                </button>
                            @endif
                        </div>
                    </div>

                    @if ($paymentMethod === 'cash')
                        <div class="grid grid-cols-2 gap-md">
                            <div>
                                <label class="text-label-sm text-outline block mb-xs">Cash Received</label>
                                <input wire:model.live="cashReceived" type="number" min="0" step="0.01"
                                       class="w-full text-lg font-bold border-outline-variant rounded-xl p-md focus:ring-primary focus:border-primary">
                            </div>
                            <div>
                                <label class="text-label-sm text-outline block mb-xs">Change Return</label>
                                <div class="w-full text-lg font-bold bg-surface-container-highest border border-outline-variant rounded-xl p-md text-secondary">
                                    Rs. {{ number_format($this->change, 2) }}
                                </div>
                            </div>
                        </div>
                    @endif

                    @error('payment') <p class="text-body-sm text-error">{{ $message }}</p> @enderror
                </div>

                {{-- Actions --}}
                <div class="p-md bg-surface-container-low flex flex-col gap-sm">
                    <button type="button" wire:click="completeSale" wire:loading.attr="disabled" @disabled(count($cart) === 0)
                            class="w-full py-4 bg-primary-container text-white rounded-xl font-headline-md flex items-center justify-center gap-md hover:opacity-90 active:scale-[0.98] transition-all shadow-md disabled:opacity-40 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="completeSale" class="flex items-center gap-md">
                            <span class="material-symbols-outlined">check_circle</span> Complete Sale
                        </span>
                        <span wire:loading wire:target="completeSale" class="flex items-center gap-md">
                            <span class="material-symbols-outlined animate-spin">progress_activity</span> Processing...
                        </span>
                    </button>
                    <button type="button" wire:click="cancelTransaction" class="w-full py-sm text-error font-label-md hover:bg-error/5 rounded-lg transition-colors">Cancel Transaction</button>
                </div>
            </div>
        </div>
    </div>
</div>
