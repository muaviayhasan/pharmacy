@extends('layouts.app')

@section('title', 'Low Stock & Reorder')
@section('page-title', 'Low Stock & Reorder')

@section('content')
    <div class="grid grid-cols-2 md:grid-cols-4 gap-md">
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Needs Reorder</p><h3 class="text-headline-md font-bold">{{ $stats['all'] }}</h3></div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Out of Stock</p><h3 class="text-headline-md font-bold text-error">{{ $stats['out'] }}</h3></div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Critical</p><h3 class="text-headline-md font-bold text-orange-600">{{ $stats['critical'] }}</h3></div>
        <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow"><p class="text-label-sm text-on-surface-variant uppercase">Est. Reorder Value</p><h3 class="text-headline-md font-bold text-primary">Rs. {{ number_format($stats['value'], 0) }}</h3></div>
    </div>

    <div class="bg-white rounded-xl border border-outline-variant custom-shadow overflow-hidden">
        <div class="flex border-b border-outline-variant px-md overflow-x-auto">
            @foreach (['all' => 'All Items', 'out' => 'Out of Stock', 'critical' => 'Critical', 'low' => 'Low Stock'] as $key => $label)
                <a href="{{ route('low-stock.index', array_filter(['tab' => $key, 'branch' => $branch])) }}"
                   class="px-lg py-md text-label-md whitespace-nowrap transition-colors {{ $tab === $key ? 'font-bold text-primary border-b-2 border-primary' : 'text-on-surface-variant hover:bg-surface-container-low' }}">{{ $label }}</a>
            @endforeach
            <form method="GET" class="ml-auto flex items-center gap-sm py-2">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <select name="branch" onchange="this.form.submit()" class="bg-surface-container-low border-none rounded-lg text-label-md px-3 py-1.5 focus:ring-1 focus:ring-primary">
                    <option value="">All Branches</option>
                    @foreach ($branches as $b)<option value="{{ $b->id }}" @selected((string) $branch === (string) $b->id)>{{ $b->name }}</option>@endforeach
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-low text-on-surface-variant text-label-sm uppercase">
                    <tr><th class="px-md py-3">Medicine & Generic</th><th class="px-md py-3 text-right">Available</th><th class="px-md py-3 text-right">Reorder Lvl</th><th class="px-md py-3 text-right">Suggested Qty</th><th class="px-md py-3 text-right">Est. Cost</th><th class="px-md py-3 text-center">Priority</th><th class="px-md py-3 text-right">Action</th></tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse ($rows as $r)
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="px-md py-md"><div class="font-label-md text-on-surface">{{ $r->name }}</div><div class="text-[11px] text-outline">{{ $r->generic_name ?? '—' }}</div></td>
                            <td class="px-md py-md text-right font-bold {{ $r->available <= 0 ? 'text-error' : 'text-on-surface' }}">{{ number_format($r->available) }}</td>
                            <td class="px-md py-md text-right text-body-sm">{{ $r->reorder_level }}</td>
                            <td class="px-md py-md text-right font-bold text-primary">{{ number_format($r->suggested) }}</td>
                            <td class="px-md py-md text-right text-body-sm">Rs. {{ number_format($r->reorder_value, 0) }}</td>
                            <td class="px-md py-md text-center">
                                @php $pb = match ($r->priority) { 'out' => 'bg-red-100 text-red-700', 'critical' => 'bg-orange-100 text-orange-700', default => 'bg-amber-100 text-amber-700' }; @endphp
                                <span class="px-2 py-0.5 {{ $pb }} rounded-full text-[10px] font-bold uppercase">{{ $r->priority === 'out' ? 'Out of Stock' : $r->priority }}</span>
                            </td>
                            <td class="px-md py-md text-right">
                                <a href="{{ route('purchases.create') }}" class="inline-flex items-center gap-xs px-3 py-1.5 bg-primary text-on-primary rounded text-[10px] font-bold uppercase hover:opacity-90"><span class="material-symbols-outlined text-[14px]">add_shopping_cart</span> Reorder</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-md py-10 text-center text-outline">Nothing needs reordering in this view. 🎉</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
