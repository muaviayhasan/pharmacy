@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    {{-- Row 1: KPIs --}}
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-gutter">
        <div class="bg-white p-md rounded-xl custom-shadow border border-outline-variant hover:border-primary transition-colors">
            <div class="flex items-center justify-between mb-2">
                <span class="material-symbols-outlined text-primary-container" style="font-variation-settings: 'FILL' 1;">payments</span>
                <span class="text-[10px] font-bold text-green-600 bg-green-50 px-2 py-0.5 rounded-full">+12%</span>
            </div>
            <p class="text-label-sm text-outline uppercase tracking-wider mb-1">Today Sales</p>
            <h3 class="text-headline-md font-bold text-on-surface">$4,520</h3>
        </div>
        <div class="bg-white p-md rounded-xl custom-shadow border border-outline-variant hover:border-primary transition-colors">
            <div class="flex items-center justify-between mb-2">
                <span class="material-symbols-outlined text-secondary-container" style="font-variation-settings: 'FILL' 1;">trending_up</span>
                <span class="text-[10px] font-bold text-green-600 bg-green-50 px-2 py-0.5 rounded-full">+5%</span>
            </div>
            <p class="text-label-sm text-outline uppercase tracking-wider mb-1">Total Profit</p>
            <h3 class="text-headline-md font-bold text-on-surface">$1,280</h3>
        </div>
        <div class="bg-white p-md rounded-xl custom-shadow border border-outline-variant hover:border-primary transition-colors">
            <div class="flex items-center justify-between mb-2">
                <span class="material-symbols-outlined text-tertiary-container" style="font-variation-settings: 'FILL' 1;">shopping_cart</span>
                <span class="text-[10px] font-medium text-outline">Last 24h</span>
            </div>
            <p class="text-label-sm text-outline uppercase tracking-wider mb-1">Total Purchases</p>
            <h3 class="text-headline-md font-bold text-on-surface">$2,100</h3>
        </div>
        <div class="bg-white p-md rounded-xl custom-shadow border border-error bg-error-container/10 transition-colors">
            <div class="flex items-center justify-between mb-2">
                <span class="material-symbols-outlined text-error" style="font-variation-settings: 'FILL' 1;">inventory_2</span>
                <span class="text-[10px] font-bold text-error uppercase">Warning</span>
            </div>
            <p class="text-label-sm text-outline uppercase tracking-wider mb-1">Low Stock</p>
            <h3 class="text-headline-md font-bold text-error">12</h3>
        </div>
        <div class="bg-white p-md rounded-xl custom-shadow border border-error bg-error-container/20 transition-colors">
            <div class="flex items-center justify-between mb-2">
                <span class="material-symbols-outlined text-error" style="font-variation-settings: 'FILL' 1;">event_busy</span>
                <span class="text-[10px] font-bold text-error uppercase">Danger</span>
            </div>
            <p class="text-label-sm text-outline uppercase tracking-wider mb-1">Near Expiry</p>
            <h3 class="text-headline-md font-bold text-error">8</h3>
        </div>
        <div class="bg-white p-md rounded-xl custom-shadow border border-primary-container bg-primary-container/5 transition-colors">
            <div class="flex items-center justify-between mb-2">
                <span class="material-symbols-outlined text-primary-container" style="font-variation-settings: 'FILL' 1;">point_of_sale</span>
                <span class="text-[10px] font-bold text-primary-container uppercase">Success</span>
            </div>
            <p class="text-label-sm text-outline uppercase tracking-wider mb-1">Active Shifts</p>
            <h3 class="text-headline-md font-bold text-primary-container">5</h3>
        </div>
    </div>

    {{-- Row 2: Sales overview + branches --}}
    <div class="grid grid-cols-12 gap-gutter">
        <div class="col-span-12 lg:col-span-7 bg-white rounded-xl border border-outline-variant p-lg custom-shadow">
            <div class="flex items-center justify-between mb-lg">
                <h4 class="text-headline-md font-bold text-on-surface">Sales Overview</h4>
                <div class="flex gap-sm">
                    <button class="text-label-sm px-3 py-1 rounded bg-surface-container-high">Weekly</button>
                    <button class="text-label-sm px-3 py-1 rounded hover:bg-surface-container-low transition-colors">Monthly</button>
                </div>
            </div>
            <div class="h-64 w-full relative overflow-hidden">
                <div class="absolute inset-0 flex flex-col justify-between pointer-events-none opacity-20">
                    <div class="border-t border-outline"></div>
                    <div class="border-t border-outline"></div>
                    <div class="border-t border-outline"></div>
                    <div class="border-t border-outline"></div>
                </div>
                <svg class="w-full h-full fill-none stroke-primary stroke-[3]" viewBox="0 0 1000 250" preserveAspectRatio="none">
                    <path d="M0,200 Q150,180 300,100 T600,150 T900,40" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M0,200 Q150,180 300,100 T600,150 T900,40 V250 H0 Z" fill="url(#salesGradient)" opacity="0.1" stroke="none"></path>
                    <defs>
                        <linearGradient id="salesGradient" x1="0" x2="0" y1="0" y2="1">
                            <stop offset="0%" stop-color="#0F766E"></stop>
                            <stop offset="100%" stop-color="#0F766E" stop-opacity="0"></stop>
                        </linearGradient>
                    </defs>
                </svg>
            </div>
            <div class="flex justify-between mt-4 text-label-sm text-outline px-4">
                <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-5 bg-white rounded-xl border border-outline-variant p-lg custom-shadow flex flex-col">
            <h4 class="text-headline-md font-bold text-on-surface mb-lg">Active Branches</h4>
            <div class="flex-1 space-y-lg overflow-y-auto pr-2">
                @foreach ([['Main Branch', 'location_on', 5000, 85], ['City Center', 'location_on', 3500, 62], ['Hospital Branch', 'local_hospital', 8000, 94], ['East Mall', 'location_on', 2800, 41]] as [$name, $icon, $target, $pct])
                    <div class="space-y-sm">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-sm">
                                <div class="w-8 h-8 rounded bg-surface-container-low flex items-center justify-center text-primary">
                                    <span class="material-symbols-outlined text-lg">{{ $icon }}</span>
                                </div>
                                <span class="font-label-md text-on-surface">{{ $name }}</span>
                            </div>
                            <span class="text-[10px] px-2 py-0.5 bg-green-100 text-green-700 rounded-full font-bold">OPERATIONAL</span>
                        </div>
                        <div class="w-full bg-surface-container-high h-1.5 rounded-full overflow-hidden">
                            <div class="bg-primary h-full rounded-full" style="width: {{ $pct }}%"></div>
                        </div>
                        <div class="flex justify-between text-label-sm text-outline">
                            <span>Daily Target: ${{ number_format($target) }}</span>
                            <span class="font-bold text-on-surface">{{ $pct }}%</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Row 3: POS shift summary + system alerts --}}
    <div class="grid grid-cols-12 gap-gutter">
        <div class="col-span-12 lg:col-span-8 bg-white rounded-xl border border-outline-variant custom-shadow overflow-hidden">
            <div class="px-lg py-md border-b border-outline-variant flex justify-between items-center">
                <h4 class="text-headline-md font-bold text-on-surface">POS Shift Summary</h4>
                <button class="text-primary font-label-md hover:underline">View All Shifts</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-body-sm">
                    <thead class="bg-surface-container-low text-outline uppercase text-[11px] font-bold tracking-wider">
                        <tr>
                            <th class="px-lg py-3">Branch</th>
                            <th class="px-lg py-3">POS Counter</th>
                            <th class="px-lg py-3">Cashier</th>
                            <th class="px-lg py-3">Opening Cash</th>
                            <th class="px-lg py-3">Sales</th>
                            <th class="px-lg py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        @foreach ([['Main Branch', 'Counter 01', 'John Doe', '500.00', '1,240.50', true], ['City Center', 'Counter 03', 'Anna Smith', '300.00', '890.20', true], ['Hospital Branch', 'Counter 01', 'Robert Wilson', '1,200.00', '3,450.00', false], ['East Mall', 'Counter 02', 'Mark Evans', '200.00', '540.75', true]] as [$branch, $counter, $cashier, $open, $sales, $active])
                            <tr class="hover:bg-surface-container-low transition-colors cursor-pointer">
                                <td class="px-lg py-4 font-label-md">{{ $branch }}</td>
                                <td class="px-lg py-4">{{ $counter }}</td>
                                <td class="px-lg py-4">
                                    <span class="flex items-center gap-sm">
                                        <span class="w-6 h-6 rounded-full bg-surface-container-high"></span>
                                        {{ $cashier }}
                                    </span>
                                </td>
                                <td class="px-lg py-4">${{ $open }}</td>
                                <td class="px-lg py-4 font-bold text-primary">${{ $sales }}</td>
                                <td class="px-lg py-4">
                                    @if ($active)
                                        <span class="flex items-center gap-1.5 text-primary">
                                            <span class="w-2 h-2 bg-primary rounded-full"></span> Active
                                        </span>
                                    @else
                                        <span class="text-outline">Closed</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-4 bg-white rounded-xl border border-outline-variant p-lg custom-shadow flex flex-col">
            <h4 class="text-headline-md font-bold text-on-surface mb-lg">System Alerts</h4>
            <div class="space-y-md">
                <div class="p-sm rounded-lg border-l-4 border-error bg-error-container/10 flex gap-md items-start">
                    <span class="material-symbols-outlined text-error" style="font-variation-settings: 'FILL' 1;">warning</span>
                    <div class="flex-1">
                        <p class="text-label-md text-on-surface font-bold">Low Stock Warning</p>
                        <p class="text-body-sm text-on-surface-variant">Panadol 500mg: Only 5 strips remaining in Main Branch.</p>
                    </div>
                </div>
                <div class="p-sm rounded-lg border-l-4 border-orange-500 bg-orange-50 flex gap-md items-start">
                    <span class="material-symbols-outlined text-orange-500" style="font-variation-settings: 'FILL' 1;">update</span>
                    <div class="flex-1">
                        <p class="text-label-md text-on-surface font-bold">Near Expiry Alert</p>
                        <p class="text-body-sm text-on-surface-variant">Augmentin 625mg: 12 boxes expiring in 15 days.</p>
                    </div>
                </div>
                <div class="p-sm rounded-lg border-l-4 border-secondary bg-surface-container flex gap-md items-start">
                    <span class="material-symbols-outlined text-secondary" style="font-variation-settings: 'FILL' 1;">info</span>
                    <div class="flex-1">
                        <p class="text-label-md text-on-surface font-bold">Cash Shortage Detected</p>
                        <p class="text-body-sm text-on-surface-variant">Counter 2 (City Center): Reported cash $50 less than system.</p>
                    </div>
                </div>
                <div class="p-sm rounded-lg border-l-4 border-primary bg-primary-container/10 flex gap-md items-start">
                    <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                    <div class="flex-1">
                        <p class="text-label-md text-on-surface font-bold">Payment Due Today</p>
                        <p class="text-body-sm text-on-surface-variant">Supplier: PharmaLife Ltd. Invoice #PF-9912. Total: $1,400.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Row 4: Top sellers + smart recommendations --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-gutter pb-lg">
        <div class="bg-white rounded-xl border border-outline-variant p-lg custom-shadow">
            <h4 class="text-headline-md font-bold text-on-surface mb-lg">Top Selling Medicines</h4>
            <div class="space-y-1">
                @foreach ([['01', 'Panadol 500mg', 'Acetaminophen | 240 Units', '1,200.00', '+15% ↑', 'green'], ['02', 'Augmentin 625mg', 'Amoxicillin/Clavulanate | 112 Units', '890.00', '+8% ↑', 'green'], ['03', 'Brufen 400mg', 'Ibuprofen | 85 Units', '450.00', '+12% ↑', 'green'], ['04', 'Calpol Syrup', 'Pediatric Suspension | 64 Units', '320.00', '-2% ↓', 'outline'], ['05', 'Cetrizine 10mg', 'Antihistamine | 45 Units', '210.00', '+4% ↑', 'green']] as [$rank, $name, $meta, $amount, $delta, $color])
                    <div class="flex items-center justify-between py-3 border-b border-outline-variant last:border-0 hover:bg-surface-container-low px-2 rounded-lg transition-colors">
                        <div class="flex items-center gap-md">
                            <span class="text-headline-md font-bold text-outline">{{ $rank }}</span>
                            <div>
                                <p class="font-label-md text-on-surface">{{ $name }}</p>
                                <p class="text-[11px] text-outline">{{ $meta }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-primary">${{ $amount }}</p>
                            <p class="text-[10px] font-bold {{ $color === 'green' ? 'text-green-600' : 'text-outline' }}">{{ $delta }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-xl border border-outline-variant p-lg custom-shadow flex flex-col">
            <div class="flex items-center gap-sm mb-lg">
                <span class="material-symbols-outlined text-secondary" style="font-variation-settings: 'FILL' 1;">psychology</span>
                <h4 class="text-headline-md font-bold text-on-surface">Smart Recommendations</h4>
            </div>
            <div class="flex-1 space-y-md">
                <div class="flex items-center justify-between p-md bg-surface-container-low rounded-xl border border-outline-variant">
                    <div>
                        <p class="font-label-md text-on-surface">Reorder Required</p>
                        <p class="text-body-sm text-on-surface-variant">Panadol 500mg stock is below safety limit.</p>
                    </div>
                    <button class="bg-primary text-white text-label-md px-4 py-2 rounded-lg hover:opacity-90 transition-opacity">Reorder Now</button>
                </div>
                <div class="flex items-center justify-between p-md bg-surface-container-low rounded-xl border border-outline-variant">
                    <div>
                        <p class="font-label-md text-on-surface">Internal Transfer</p>
                        <p class="text-body-sm text-on-surface-variant">Move surplus Cetrizine from Main to Hospital.</p>
                    </div>
                    <button class="bg-primary-container text-white text-label-md px-4 py-2 rounded-lg hover:opacity-90 transition-opacity">Transfer</button>
                </div>
                <div class="flex items-center justify-between p-md bg-surface-container-low rounded-xl border border-outline-variant">
                    <div>
                        <p class="font-label-md text-on-surface">Slow Moving Stock</p>
                        <p class="text-body-sm text-on-surface-variant">Review 4 items with high expiry risk (&gt;6 mo).</p>
                    </div>
                    <button class="border border-primary text-primary text-label-md px-4 py-2 rounded-lg hover:bg-primary/5 transition-colors">Review</button>
                </div>
                <div class="flex items-center justify-between p-md bg-surface-container-low rounded-xl border border-outline-variant">
                    <div>
                        <p class="font-label-md text-on-surface">Price Comparison</p>
                        <p class="text-body-sm text-on-surface-variant">Augmentin prices updated by 3 suppliers.</p>
                    </div>
                    <button class="border border-primary text-primary text-label-md px-4 py-2 rounded-lg hover:bg-primary/5 transition-colors">Compare</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('fab')
    <button class="fixed bottom-16 right-margin-desktop w-14 h-14 bg-primary-container text-white rounded-full flex items-center justify-center shadow-xl hover:scale-105 active:scale-95 transition-all z-50 group">
        <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">add</span>
        <span class="absolute right-full mr-4 bg-inverse-surface text-white px-3 py-1.5 rounded-lg text-label-md whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">New Sale / Transaction</span>
    </button>
@endsection
