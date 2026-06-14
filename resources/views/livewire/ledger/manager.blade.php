<div class="space-y-lg" x-data="{ toast: null }"
     x-on:ledger-posted.window="toast = 'Voucher posted successfully'; setTimeout(() => toast = null, 3500)">

    <div x-show="toast" x-transition x-cloak class="fixed top-20 right-8 z-50 bg-inverse-surface text-white px-md py-sm rounded-lg shadow-xl flex items-center gap-sm">
        <span class="material-symbols-outlined text-primary-fixed">check_circle</span>
        <span class="text-body-sm" x-text="toast"></span>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-md">
        @php
            $cards = [
                ['Total Receivable', $this->summary['receivable'], 'account_balance', 'text-primary', 'bg-primary-fixed'],
                ['Total Payable', $this->summary['payable'], 'outbox', 'text-error', 'bg-error-container'],
                ['Cash in Hand', $this->summary['cash'], 'payments', 'text-secondary', 'bg-secondary-fixed'],
                ['Bank Balance', $this->summary['bank'], 'account_balance_wallet', 'text-tertiary', 'bg-tertiary-fixed'],
                ['Today Payments', $this->summary['today_payments'], 'schedule_send', 'text-outline', 'bg-surface-container-high'],
                ['Today Receipts', $this->summary['today_receipts'], 'assignment_returned', 'text-primary', 'bg-primary-fixed'],
            ];
        @endphp
        @foreach ($cards as [$label, $value, $icon, $color, $bg])
            <div class="bg-white p-md rounded-xl border border-outline-variant/40 custom-shadow flex flex-col justify-between">
                <span class="material-symbols-outlined {{ $color }} {{ $bg }} p-1.5 rounded-lg w-fit">{{ $icon }}</span>
                <div class="mt-sm">
                    <p class="text-label-sm text-on-surface-variant">{{ $label }}</p>
                    <p class="text-headline-md font-bold text-on-surface">Rs. {{ number_format($value, 0) }}</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Tabs + filters --}}
    <div class="bg-white rounded-xl border border-outline-variant/40 custom-shadow overflow-hidden">
        <div class="flex border-b border-outline-variant/30 px-md overflow-x-auto">
            @foreach (['customer' => 'Customer Ledger', 'supplier' => 'Supplier Ledger', 'cash' => 'Cash Ledger', 'bank' => 'Bank Ledger', 'expense' => 'Expense Ledger'] as $key => $label)
                <button wire:click="setTab('{{ $key }}')"
                        class="px-lg py-md text-label-md whitespace-nowrap transition-colors {{ $tab === $key ? 'font-bold text-primary border-b-2 border-primary' : 'text-on-surface-variant hover:bg-surface-container-low' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="p-lg grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-md items-end">
            @if (in_array($tab, ['customer', 'supplier', 'cash', 'bank']))
                <div class="flex flex-col gap-xs">
                    <label class="text-label-sm text-outline">{{ ucfirst($tab) }}</label>
                    <select wire:model.live="partyId" class="bg-surface-container border border-outline-variant/50 rounded-lg text-body-sm px-md py-2 outline-none focus:border-primary">
                        <option value="">All</option>
                        @foreach ($this->parties as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="flex flex-col gap-xs">
                <label class="text-label-sm text-outline">Branch</label>
                <select wire:model.live="branchFilter" class="bg-surface-container border border-outline-variant/50 rounded-lg text-body-sm px-md py-2 outline-none focus:border-primary">
                    <option value="">All Branches</option>
                    @foreach ($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-col gap-xs">
                <label class="text-label-sm text-outline">Voucher</label>
                <input wire:model.live.debounce.400ms="voucher" type="text" placeholder="PV-..." class="bg-surface-container border border-outline-variant/50 rounded-lg text-body-sm px-md py-2 outline-none focus:border-primary">
            </div>
            <div class="flex flex-col gap-xs">
                <label class="text-label-sm text-outline">From</label>
                <input wire:model.live="fromDate" type="date" class="bg-surface-container border border-outline-variant/50 rounded-lg text-body-sm px-md py-2 outline-none focus:border-primary">
            </div>
            <div class="flex flex-col gap-xs">
                <label class="text-label-sm text-outline">To</label>
                <input wire:model.live="toDate" type="date" class="bg-surface-container border border-outline-variant/50 rounded-lg text-body-sm px-md py-2 outline-none focus:border-primary">
            </div>
            <button wire:click="resetFilters" class="bg-surface-variant text-on-surface-variant px-md py-2 rounded-lg flex items-center justify-center gap-xs hover:bg-outline-variant/30">
                <span class="material-symbols-outlined text-[18px]">restart_alt</span> Reset
            </button>
        </div>
    </div>

    {{-- Table + summary --}}
    <div class="flex flex-col xl:flex-row gap-lg items-start">
        <div class="xl:w-[68%] w-full bg-white rounded-xl border border-outline-variant/40 custom-shadow overflow-hidden">
            <div class="px-lg py-md border-b border-outline-variant/30 flex justify-between items-center">
                <h3 class="text-headline-md text-on-surface font-semibold">{{ ucfirst($tab) }} Transactions</h3>
                <p class="text-label-sm text-outline">{{ $this->entries->total() }} entries</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-surface-container-low border-b border-outline-variant/30 text-label-md text-on-surface-variant">
                        <tr>
                            <th class="px-lg py-md">Date</th>
                            <th class="px-lg py-md">Voucher</th>
                            <th class="px-lg py-md">Description</th>
                            <th class="px-lg py-md text-right">Debit</th>
                            <th class="px-lg py-md text-right">Credit</th>
                            <th class="px-lg py-md text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/20">
                        @forelse ($this->entries as $entry)
                            <tr class="hover:bg-surface-container-low transition-colors">
                                <td class="px-lg py-md text-body-sm">{{ $entry->transaction_date?->format('d M Y') }}</td>
                                <td class="px-lg py-md">
                                    <span class="text-label-md font-bold text-on-surface">{{ $entry->voucher_no ?? '—' }}</span>
                                    @if ($entry->voucher_type)
                                        <p class="text-[10px] uppercase text-outline tracking-wider">{{ $entry->voucher_type }}</p>
                                    @endif
                                </td>
                                <td class="px-lg py-md text-body-sm text-on-surface">{{ $entry->description }}</td>
                                <td class="px-lg py-md text-right font-bold text-primary">{{ $entry->debit > 0 ? 'Rs. '.number_format($entry->debit, 2) : '—' }}</td>
                                <td class="px-lg py-md text-right font-bold text-error">{{ $entry->credit > 0 ? 'Rs. '.number_format($entry->credit, 2) : '—' }}</td>
                                <td class="px-lg py-md text-right font-bold text-on-surface">Rs. {{ number_format($entry->balance, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-lg py-2xl text-center text-outline">No ledger entries found for this view.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-lg py-md border-t border-outline-variant/30">{{ $this->entries->links() }}</div>
        </div>

        {{-- Selected party summary --}}
        <div class="xl:w-[32%] w-full bg-inverse-surface text-surface-bright rounded-xl shadow-xl p-lg flex flex-col gap-lg">
            @if ($this->partySummary)
                <div class="border-b border-surface-variant/20 pb-md">
                    <h3 class="text-label-sm text-primary-fixed-dim uppercase tracking-wider">{{ $this->partySummary['name'] }}</h3>
                    <p class="text-headline-md font-bold">Ledger Summary</p>
                </div>
                <div class="space-y-md">
                    <div class="flex justify-between text-body-sm"><span class="opacity-70">Opening Balance</span><span class="font-bold">Rs. {{ number_format($this->partySummary['opening'], 2) }}</span></div>
                    <div class="flex justify-between text-body-sm"><span class="opacity-70">Total Debit</span><span class="font-bold text-primary-fixed-dim">Rs. {{ number_format($this->partySummary['debit'], 2) }}</span></div>
                    <div class="flex justify-between text-body-sm"><span class="opacity-70">Total Credit</span><span class="font-bold text-error-container">Rs. {{ number_format($this->partySummary['credit'], 2) }}</span></div>
                    <div class="pt-md border-t border-surface-variant/20 flex justify-between items-center">
                        <span class="text-label-md font-bold">Current Balance</span>
                        <span class="text-headline-md font-bold text-primary-fixed-dim">Rs. {{ number_format($this->partySummary['current'], 2) }}</span>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-sm pt-lg">
                    @if ($tab === 'customer')
                        <button wire:click="openModal('receipt')" class="bg-primary text-on-primary py-sm rounded-lg text-label-md font-bold hover:opacity-90 flex items-center justify-center gap-xs">
                            <span class="material-symbols-outlined text-[18px]">request_quote</span> Add Customer Receipt
                        </button>
                    @elseif ($tab === 'supplier')
                        <button wire:click="openModal('payment')" class="bg-primary text-on-primary py-sm rounded-lg text-label-md font-bold hover:opacity-90 flex items-center justify-center gap-xs">
                            <span class="material-symbols-outlined text-[18px]">add_card</span> Add Supplier Payment
                        </button>
                    @endif
                </div>
            @else
                <div class="flex flex-col items-center justify-center text-center py-2xl opacity-70">
                    <span class="material-symbols-outlined text-[48px] text-primary-fixed-dim">account_balance_wallet</span>
                    <p class="text-body-md mt-sm">Select a {{ $tab }} from the filter to view its ledger summary and record vouchers.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Receipt / Payment modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-md" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
                <div class="px-lg py-md bg-surface-container-low border-b border-outline-variant flex items-center justify-between">
                    <h4 class="text-headline-md font-semibold">{{ $modalType === 'receipt' ? 'Customer Receipt' : 'Supplier Payment' }}</h4>
                    <button wire:click="$set('showModal', false)" class="text-outline hover:text-error"><span class="material-symbols-outlined">close</span></button>
                </div>
                <div class="p-lg space-y-md">
                    <p class="text-body-sm text-on-surface-variant">{{ $this->selectedParty?->name }}</p>
                    <div>
                        <label class="block text-label-md text-on-surface-variant mb-xs">Amount <span class="text-error">*</span></label>
                        <input wire:model="amount" type="number" step="0.01" min="0" class="w-full border border-outline-variant rounded-lg p-md focus:ring-2 focus:ring-primary bg-background">
                        @error('amount') <p class="text-body-sm text-error mt-xs">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-md">
                        <div>
                            <label class="block text-label-md text-on-surface-variant mb-xs">Method</label>
                            <select wire:model="method" class="w-full border border-outline-variant rounded-lg p-md focus:ring-2 focus:ring-primary bg-background">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="bank">Bank</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-label-md text-on-surface-variant mb-xs">Account</label>
                            <select wire:model="accountId" class="w-full border border-outline-variant rounded-lg p-md focus:ring-2 focus:ring-primary bg-background">
                                <option value="">— None —</option>
                                @foreach ($this->accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->name }} ({{ ucfirst($acc->type) }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-label-md text-on-surface-variant mb-xs">Reference</label>
                        <input wire:model="reference" type="text" placeholder="Cheque no, txn id..." class="w-full border border-outline-variant rounded-lg p-md focus:ring-2 focus:ring-primary bg-background">
                    </div>
                </div>
                <div class="px-lg py-md bg-surface-container-low flex justify-end gap-sm">
                    <button wire:click="$set('showModal', false)" class="px-lg py-2.5 rounded-lg text-label-md border border-outline-variant text-on-surface-variant hover:bg-surface-container">Cancel</button>
                    <button wire:click="save" wire:loading.attr="disabled" class="px-lg py-2.5 rounded-lg text-label-md bg-primary text-on-primary hover:opacity-90 flex items-center gap-sm">
                        <span class="material-symbols-outlined text-[18px]">check</span> Post Voucher
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
