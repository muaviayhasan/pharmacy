@extends('layouts.app')

@section('title', $medicine->name)
@section('page-title', 'Medicine Details')

@php
    $margin = $medicine->sale_price > 0
        ? round(($medicine->sale_price - $medicine->purchase_price) / $medicine->sale_price * 100, 1)
        : 0;
    $barcodeValue = $medicine->barcode ?: ('MED-'.$medicine->id);
@endphp

@section('content')
    <div class="flex items-center justify-between">
        <a href="{{ route('medicines.index') }}" class="inline-flex items-center gap-xs text-primary text-label-md hover:underline">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span> Back to Medicines
        </a>
        <a href="{{ route('medicines.edit', $medicine) }}" class="px-md py-2 bg-primary text-on-primary rounded-lg text-label-md hover:opacity-90 flex items-center gap-xs">
            <span class="material-symbols-outlined text-[18px]">edit</span> Edit Master Data
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-lg">
        {{-- Main --}}
        <div class="lg:col-span-2 space-y-lg">
            <div class="bg-white rounded-xl border border-outline-variant custom-shadow overflow-hidden">
                <div class="bg-primary text-on-primary p-lg">
                    <div class="flex flex-wrap gap-2 mb-2">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $medicine->status === 'active' ? 'bg-white/20' : 'bg-white/10' }}">{{ $medicine->status }}</span>
                        @if ($medicine->prescription_required)<span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-orange-400/40">Rx Required</span>@endif
                        @if ($medicine->controlled_medicine)<span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-red-400/40">Controlled</span>@endif
                    </div>
                    <h2 class="text-headline-md font-bold">{{ $medicine->name }}</h2>
                    <p class="text-body-sm opacity-80">{{ $medicine->generic_name }} {{ $medicine->manufacturer ? '• '.$medicine->manufacturer->name : '' }}</p>
                </div>
                <div class="p-lg grid grid-cols-2 md:grid-cols-4 gap-lg text-body-sm">
                    <div><p class="text-[10px] uppercase text-outline font-bold">Category</p><p class="font-medium">{{ $medicine->category?->name ?? '—' }}</p></div>
                    <div><p class="text-[10px] uppercase text-outline font-bold">Dosage Form</p><p class="font-medium">{{ $medicine->dosage_form ?? '—' }}</p></div>
                    <div><p class="text-[10px] uppercase text-outline font-bold">Strength</p><p class="font-medium">{{ $medicine->strength }}{{ $medicine->strength_unit }}</p></div>
                    <div><p class="text-[10px] uppercase text-outline font-bold">Pack Size</p><p class="font-medium">{{ $medicine->pack_size ?? '—' }}</p></div>
                    <div><p class="text-[10px] uppercase text-outline font-bold">Rack / Shelf</p><p class="font-medium">{{ $medicine->rack_shelf ?? '—' }}</p></div>
                    <div><p class="text-[10px] uppercase text-outline font-bold">Preferred Supplier</p><p class="font-medium">{{ $medicine->defaultSupplier?->name ?? '—' }}</p></div>
                    <div><p class="text-[10px] uppercase text-outline font-bold">Reorder Level</p><p class="font-medium">{{ $medicine->reorder_level }}</p></div>
                    <div><p class="text-[10px] uppercase text-outline font-bold">Max Discount</p><p class="font-medium">{{ $medicine->max_discount_percent }}%</p></div>
                </div>
            </div>

            {{-- Pricing --}}
            <div class="bg-white rounded-xl border border-outline-variant custom-shadow p-lg">
                <h4 class="text-label-sm font-bold text-on-surface-variant uppercase tracking-widest mb-md">Pricing & Margin</h4>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-md">
                    <div class="bg-surface-container-low p-md rounded-lg border border-outline-variant"><p class="text-[10px] text-outline uppercase font-bold">Purchase</p><p class="text-headline-md font-bold">Rs. {{ number_format($medicine->purchase_price, 2) }}</p></div>
                    <div class="bg-surface-container-low p-md rounded-lg border border-outline-variant"><p class="text-[10px] text-outline uppercase font-bold">Sale (MRP)</p><p class="text-headline-md font-bold text-primary">Rs. {{ number_format($medicine->sale_price, 2) }}</p></div>
                    <div class="bg-surface-container-low p-md rounded-lg border border-outline-variant"><p class="text-[10px] text-outline uppercase font-bold">Wholesale</p><p class="text-headline-md font-bold">Rs. {{ number_format($medicine->wholesale_price, 2) }}</p></div>
                </div>
                <div class="mt-md flex items-center justify-between p-md bg-green-50 rounded-lg border border-green-100">
                    <span class="text-body-sm font-medium text-green-800">Net Profit Margin</span>
                    <span class="text-body-md font-bold text-green-800">{{ $margin }}%</span>
                </div>
            </div>

            {{-- Batches --}}
            <div class="bg-white rounded-xl border border-outline-variant custom-shadow overflow-hidden">
                <div class="px-lg py-md border-b border-outline-variant flex justify-between items-center">
                    <h4 class="text-label-md font-bold">Active Batches</h4>
                    <span class="text-label-sm text-outline">Total on hand: <span class="font-bold text-on-surface">{{ number_format($totalStock) }} units</span></span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-body-sm">
                        <thead class="bg-surface-container-low text-on-surface-variant text-label-sm uppercase">
                            <tr><th class="px-lg py-sm">Batch</th><th class="px-lg py-sm">Branch</th><th class="px-lg py-sm">Expiry</th><th class="px-lg py-sm text-right">Available</th><th class="px-lg py-sm text-center">Status</th></tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            @forelse ($medicine->batches->sortBy('expiry_date') as $batch)
                                <tr class="{{ $batch->isExpired() ? 'bg-error-container/20' : '' }}">
                                    <td class="px-lg py-sm font-mono">{{ $batch->batch_no }}</td>
                                    <td class="px-lg py-sm">{{ $batch->branch?->name }}</td>
                                    <td class="px-lg py-sm {{ $batch->isExpired() ? 'text-error font-bold' : '' }}">{{ $batch->expiry_date?->format('m/Y') }}</td>
                                    <td class="px-lg py-sm text-right font-bold">{{ number_format($batch->available_quantity) }}</td>
                                    <td class="px-lg py-sm text-center"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $batch->isExpired() ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">{{ $batch->isExpired() ? 'Expired' : $batch->status }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-lg py-lg text-center text-outline">No batch stock yet — add stock via a purchase.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Side --}}
        <div class="space-y-lg">
            <div class="bg-white rounded-xl border border-outline-variant custom-shadow p-lg">
                <h4 class="text-label-sm font-bold text-on-surface-variant uppercase tracking-widest mb-md">Inventory Summary</h4>
                <div class="space-y-sm text-body-sm">
                    <div class="flex justify-between"><span class="text-on-surface-variant">Total On Hand</span><span class="font-bold">{{ number_format($totalStock) }} units</span></div>
                    <div class="flex justify-between"><span class="text-on-surface-variant">Min Reorder Level</span><span class="font-bold">{{ $medicine->reorder_level }} units</span></div>
                    <div class="flex justify-between"><span class="text-on-surface-variant">Earliest Expiry</span><span class="font-bold text-orange-600">{{ $earliestExpiry ? \Illuminate\Support\Carbon::parse($earliestExpiry)->format('m/Y') : '—' }}</span></div>
                </div>
                <div class="mt-md pt-md border-t border-outline-variant space-y-sm">
                    <p class="text-[10px] uppercase text-outline font-bold">Branch-wise Availability</p>
                    @forelse ($branchStock as $bs)
                        <div class="flex justify-between text-body-sm"><span>{{ $bs['branch'] }}</span><span class="font-bold">{{ number_format($bs['qty']) }}</span></div>
                    @empty
                        <p class="text-body-sm text-outline">No stock across branches.</p>
                    @endforelse
                </div>
            </div>

            {{-- Barcode (QR via simple-qrcode) --}}
            <div class="bg-white rounded-xl border border-outline-variant custom-shadow p-lg flex flex-col items-center">
                <h4 class="text-label-sm font-bold text-on-surface-variant uppercase tracking-widest mb-md self-start">Barcode / QR</h4>
                <div class="bg-white p-2 rounded border border-outline-variant">
                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(150)->margin(1)->generate($barcodeValue) !!}
                </div>
                <p class="text-[12px] font-mono mt-sm">{{ $barcodeValue }}</p>
                <button onclick="window.print()" class="mt-md w-full bg-surface-container-highest text-primary py-2 rounded-lg text-label-sm hover:bg-surface-variant flex items-center justify-center gap-xs">
                    <span class="material-symbols-outlined text-[18px]">print</span> Print Label
                </button>
            </div>
        </div>
    </div>
@endsection
