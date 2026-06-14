<div class="space-y-lg">
    @push('styles')
    <style>
        @media print {
            aside, header, footer, .no-print { display: none !important; }
            main { padding-left: 0 !important; }
            section { overflow: visible !important; padding: 0 !important; }
            .label-sheet { display: grid !important; grid-template-columns: repeat(4, 1fr); gap: 8px; }
            .label-cell { border: 1px dashed #999; break-inside: avoid; }
        }
    </style>
    @endpush

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-lg items-start no-print">
        <div class="lg:col-span-2 space-y-lg">
            {{-- Search --}}
            <div class="bg-white rounded-xl border border-outline-variant custom-shadow p-md">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">search</span>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search medicine to generate barcode labels"
                           class="w-full pl-12 pr-4 py-3 border-2 border-outline-variant rounded-xl text-body-md focus:border-primary outline-none">
                    @if ($this->searchResults->isNotEmpty())
                        <div class="absolute left-0 right-0 top-full mt-1 bg-white border border-outline-variant rounded-xl shadow-lg z-30 max-h-72 overflow-y-auto">
                            @foreach ($this->searchResults as $m)
                                <button type="button" wire:click="addToQueue({{ $m->id }})" class="w-full text-left px-md py-sm hover:bg-surface-container-low flex items-center justify-between border-b border-outline-variant last:border-0">
                                    <div><div class="font-label-md">{{ $m->name }}</div><div class="text-[11px] text-outline">{{ $m->generic_name }} • {{ $m->barcode ?? 'no barcode' }}</div></div>
                                    <span class="material-symbols-outlined text-primary">add_circle</span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Queue --}}
            <div class="bg-white rounded-xl border border-outline-variant custom-shadow overflow-hidden">
                <div class="px-md py-sm bg-surface-container-low border-b border-outline-variant flex justify-between items-center">
                    <span class="text-label-md font-bold text-primary">Label Queue ({{ count($this->labels) }} labels)</span>
                    @if (count($queue))<button wire:click="clearQueue" class="text-error text-label-sm hover:underline">Clear All</button>@endif
                </div>
                <table class="w-full text-left text-body-sm">
                    <thead class="bg-surface-container-low text-on-surface-variant text-label-sm uppercase">
                        <tr><th class="px-md py-sm">Product</th><th class="px-md py-sm">Barcode</th><th class="px-md py-sm text-right">Price</th><th class="px-md py-sm text-right">Qty</th><th class="px-md py-sm"></th></tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        @forelse ($queue as $id => $item)
                            <tr wire:key="bc-{{ $id }}">
                                <td class="px-md py-sm font-label-md">{{ $item['name'] }}</td>
                                <td class="px-md py-sm font-mono text-[11px]">{{ $item['barcode'] }}</td>
                                <td class="px-md py-sm text-right">Rs. {{ number_format($item['price'], 2) }}</td>
                                <td class="px-md py-sm text-right"><input wire:model.live="queue.{{ $id }}.qty" type="number" min="1" max="200" class="w-16 border border-outline-variant rounded px-2 py-1 text-right focus:border-primary outline-none"></td>
                                <td class="px-md py-sm text-right"><button wire:click="removeFromQueue({{ $id }})" class="text-error hover:bg-error/10 p-1 rounded"><span class="material-symbols-outlined text-[18px]">delete</span></button></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-md py-lg text-center text-outline">Search above to add medicines to the print queue.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-xl border-2 border-primary/20 shadow-lg overflow-hidden">
            <div class="bg-primary text-on-primary px-md py-sm flex justify-between items-center"><span class="text-label-md font-bold">Print Job</span><span class="material-symbols-outlined">barcode</span></div>
            <div class="p-md space-y-md">
                <div class="flex justify-between text-body-sm"><span class="text-on-surface-variant">Products</span><span class="font-bold">{{ count($queue) }}</span></div>
                <div class="flex justify-between text-body-sm"><span class="text-on-surface-variant">Total Labels</span><span class="font-bold">{{ count($this->labels) }}</span></div>
                <button onclick="window.print()" @disabled(count($queue) === 0)
                        class="w-full py-3 bg-primary-container text-white rounded-lg font-bold hover:brightness-110 active:scale-95 transition-all disabled:opacity-40 flex items-center justify-center gap-sm">
                    <span class="material-symbols-outlined">print</span> Print Labels
                </button>
                <p class="text-[11px] text-outline text-center">Labels render as QR codes (max 200 per job).</p>
            </div>
        </div>
    </div>

    {{-- Label sheet (also the print area) --}}
    @if (count($this->labels))
        <div class="bg-white rounded-xl border border-outline-variant custom-shadow p-lg">
            <p class="text-label-md font-bold mb-md no-print">Label Preview</p>
            <div class="label-sheet grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-sm">
                @foreach ($this->labels as $label)
                    <div class="label-cell border border-outline-variant rounded-lg p-2 flex flex-col items-center text-center">
                        <p class="text-[10px] font-bold leading-tight">{{ \Illuminate\Support\Str::limit($label['name'], 22) }}</p>
                        <p class="text-[9px] text-outline">{{ $label['strength'] }}</p>
                        <div class="my-1">{!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(70)->margin(0)->generate($label['barcode']) !!}</div>
                        <p class="text-[9px] font-mono">{{ $label['barcode'] }}</p>
                        <p class="text-[11px] font-bold">Rs. {{ number_format($label['price'], 2) }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
