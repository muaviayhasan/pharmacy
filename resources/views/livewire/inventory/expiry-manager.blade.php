<div class="space-y-lg" x-data="{ toast: null }" x-on:toast.window="toast = $event.detail.message; setTimeout(() => toast = null, 3500)">
    <div x-show="toast" x-transition x-cloak class="fixed top-20 right-8 z-50 bg-inverse-surface text-white px-md py-sm rounded-lg shadow-xl flex items-center gap-sm">
        <span class="material-symbols-outlined text-primary-fixed">check_circle</span><span class="text-body-sm" x-text="toast"></span>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-md">
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">inventory_2</span> At-Risk Batches</p><h3 class="text-headline-md font-extrabold mt-1">{{ $this->counts['all'] }}</h3></div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant flex items-center gap-1"><span class="material-symbols-outlined text-[16px] text-error">dangerous</span> Expired</p><h3 class="text-headline-md font-extrabold mt-1 text-error">{{ $this->counts['expired'] }}</h3></div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant flex items-center gap-1"><span class="material-symbols-outlined text-[16px] text-orange-600">event_busy</span> ≤ 30 Days</p><h3 class="text-headline-md font-extrabold mt-1 text-orange-600">{{ $this->counts['d30'] }}</h3></div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">account_balance_wallet</span> Value at Risk</p><h3 class="text-headline-md font-extrabold mt-1">Rs. {{ number_format($this->counts['value'], 0) }}</h3></div>
    </div>

    {{-- Tabs + filters --}}
    <div class="bg-white rounded-xl border border-outline-variant custom-shadow overflow-hidden">
        <div class="flex border-b border-outline-variant px-md overflow-x-auto">
            @foreach (['all' => 'All At Risk', 'expired' => 'Expired', 'd30' => '≤ 30 Days', 'd60' => '31–60 Days', 'd90' => '61–90 Days'] as $key => $label)
                <button wire:click="$set('bucket', '{{ $key }}')" class="px-lg py-md text-label-md whitespace-nowrap transition-colors {{ $bucket === $key ? 'font-bold text-primary border-b-2 border-primary' : 'text-on-surface-variant hover:bg-surface-container-low' }}">{{ $label }}</button>
            @endforeach
        </div>
        <div class="p-md flex flex-col lg:flex-row gap-md">
            <div class="relative flex-1">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[20px]">search</span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search medicine..." class="w-full pl-10 pr-4 py-2 bg-surface-container-low border-none rounded-lg text-body-sm focus:ring-1 focus:ring-primary">
            </div>
            <select wire:model.live="branchFilter" class="bg-surface-container-low border-none rounded-lg text-label-md px-3 py-2 focus:ring-1 focus:ring-primary">
                <option value="">All Branches</option>
                @foreach ($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-low text-on-surface-variant text-label-sm uppercase">
                    <tr><th class="px-md py-3">Medicine</th><th class="px-md py-3">Batch</th><th class="px-md py-3">Expiry</th><th class="px-md py-3 text-center">Days Left</th><th class="px-md py-3">Branch</th><th class="px-md py-3 text-right">Qty</th><th class="px-md py-3 text-right">Value</th><th class="px-md py-3 text-center">Risk</th><th class="px-md py-3 text-right">Action</th></tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse ($this->batches as $b)
                        @php
                            $days = (int) now()->startOfDay()->diffInDays($b->expiry_date, false);
                            $risk = $days < 0 ? ['Expired', 'bg-red-100 text-red-700'] : ($days <= 30 ? ['Critical', 'bg-orange-100 text-orange-700'] : ($days <= 60 ? ['High', 'bg-amber-100 text-amber-700'] : ['Watch', 'bg-blue-100 text-blue-700']));
                        @endphp
                        <tr class="hover:bg-surface-container-low transition-colors {{ $b->status === 'quarantined' ? 'bg-surface-container-low/50' : '' }}">
                            <td class="px-md py-md"><div class="font-label-md text-on-surface">{{ $b->medicine?->name }}</div><div class="text-[11px] text-outline">{{ $b->medicine?->generic_name }}</div></td>
                            <td class="px-md py-md font-mono text-body-sm">{{ $b->batch_no }}</td>
                            <td class="px-md py-md text-body-sm {{ $days < 0 ? 'text-error font-bold' : '' }}">{{ $b->expiry_date?->format('d M Y') }}</td>
                            <td class="px-md py-md text-center text-body-sm font-bold {{ $days < 0 ? 'text-error' : '' }}">{{ $days < 0 ? abs($days).'d ago' : $days.'d' }}</td>
                            <td class="px-md py-md text-body-sm">{{ $b->branch?->name }}</td>
                            <td class="px-md py-md text-right font-bold">{{ number_format($b->available_quantity) }}</td>
                            <td class="px-md py-md text-right text-body-sm">Rs. {{ number_format($b->available_quantity * $b->purchase_price, 0) }}</td>
                            <td class="px-md py-md text-center"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $risk[1] }}">{{ $risk[0] }}</span>@if ($b->status === 'quarantined')<span class="block mt-1 text-[9px] text-outline uppercase">Quarantined</span>@endif</td>
                            <td class="px-md py-md text-right">
                                <div class="flex justify-end gap-1">
                                    @if ($b->status === 'quarantined')
                                        <button wire:click="restore({{ $b->id }})" class="p-1.5 text-outline hover:text-primary" title="Restore to stock"><span class="material-symbols-outlined text-[18px]">restart_alt</span></button>
                                    @else
                                        <button wire:click="quarantine({{ $b->id }})" class="p-1.5 text-outline hover:text-amber-600" title="Quarantine"><span class="material-symbols-outlined text-[18px]">block</span></button>
                                    @endif
                                    <button wire:click="dispose({{ $b->id }})" wire:confirm="Dispose this batch? Stock will be zeroed." class="p-1.5 text-outline hover:text-error" title="Dispose"><span class="material-symbols-outlined text-[18px]">delete_forever</span></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-md py-10 text-center text-outline">No at-risk batches in this view. 🎉</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-md border-t border-outline-variant">{{ $this->batches->links() }}</div>
    </div>
</div>
