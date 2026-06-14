@extends('layouts.app')

@section('title', 'Medicines')
@section('page-title', 'Medicine & Product Management')

@section('content')
    {{-- KPIs --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-md">
        @php
            $kpis = [
                ['Total Medicines', $stats['total'], 'inventory_2', 'text-primary', 'bg-primary-fixed/30'],
                ['Active', $stats['active'], 'check_circle', 'text-green-600', 'bg-green-50'],
                ['Prescription', $stats['prescription'], 'prescriptions', 'text-orange-600', 'bg-orange-50'],
                ['Controlled', $stats['controlled'], 'warning', 'text-red-600', 'bg-red-50'],
                ['Low Stock', $stats['low'], 'trending_down', 'text-yellow-600', 'bg-yellow-50'],
                ['Out of Stock', $stats['out'], 'block', 'text-on-error-container', 'bg-error-container'],
            ];
        @endphp
        @foreach ($kpis as [$label, $value, $icon, $color, $bg])
            <div class="bg-white p-md rounded-xl border border-outline-variant custom-shadow">
                <div class="flex justify-between items-start mb-2">
                    <span class="text-label-sm text-on-surface-variant">{{ $label }}</span>
                    <span class="material-symbols-outlined {{ $color }} {{ $bg }} p-1 rounded text-[20px]">{{ $icon }}</span>
                </div>
                <div class="text-headline-md font-bold text-on-surface">{{ number_format($value) }}</div>
            </div>
        @endforeach
    </div>

    <div class="bg-white rounded-xl border border-outline-variant custom-shadow">
        <form method="GET" class="p-md flex flex-col lg:flex-row lg:items-center gap-md border-b border-outline-variant">
            <div class="relative flex-1">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[20px]">search</span>
                <input name="search" value="{{ $filters['search'] ?? '' }}" type="text" placeholder="Search name, generic, or barcode..."
                       class="w-full pl-10 pr-4 py-2 bg-surface-container-low border-none rounded-lg text-body-sm focus:ring-1 focus:ring-primary">
            </div>
            <select name="category" class="bg-surface-container-low border-none rounded-lg text-label-md px-3 py-2 focus:ring-1 focus:ring-primary">
                <option value="">All Categories</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->id }}" @selected((string) ($filters['category'] ?? '') === (string) $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
            <select name="status" class="bg-surface-container-low border-none rounded-lg text-label-md px-3 py-2 focus:ring-1 focus:ring-primary">
                <option value="">All Status</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
            </select>
            <label class="flex items-center gap-sm text-body-sm text-on-surface-variant whitespace-nowrap">
                <input type="checkbox" name="prescription" value="1" @checked(! empty($filters['prescription'])) class="w-4 h-4 text-primary rounded border-outline-variant focus:ring-primary"> Rx only
            </label>
            <div class="flex gap-2">
                <button type="submit" class="bg-surface-container-highest text-on-surface-variant px-4 py-2 rounded-lg text-label-md hover:bg-outline-variant transition-all">Filter</button>
                <a href="{{ route('medicines.create') }}" class="bg-primary text-white px-4 py-2 rounded-lg text-label-md flex items-center gap-2 hover:opacity-90 transition-all">
                    <span class="material-symbols-outlined text-[18px]">add</span> Add Medicine
                </a>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-low text-on-surface-variant text-label-sm uppercase">
                    <tr>
                        <th class="px-md py-3">Medicine</th>
                        <th class="px-md py-3">Generic / Brand</th>
                        <th class="px-md py-3">Category</th>
                        <th class="px-md py-3">Strength</th>
                        <th class="px-md py-3">Pack</th>
                        <th class="px-md py-3 text-right">Stock</th>
                        <th class="px-md py-3 text-center">Flags</th>
                        <th class="px-md py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse ($medicines as $m)
                        @php $stock = (int) ($m->stock ?? 0); @endphp
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="px-md py-md">
                                <div class="font-label-md text-on-surface">{{ $m->name }}</div>
                                <div class="text-[10px] text-outline font-mono">{{ $m->barcode ?? 'No barcode' }}</div>
                            </td>
                            <td class="px-md py-md">
                                <div class="text-body-sm">{{ $m->generic_name ?? '—' }}</div>
                                <div class="text-label-sm text-outline">{{ $m->manufacturer?->name }}</div>
                            </td>
                            <td class="px-md py-md text-body-sm">{{ $m->category?->name ?? '—' }}</td>
                            <td class="px-md py-md text-body-sm">{{ $m->strength }}{{ $m->strength_unit }}</td>
                            <td class="px-md py-md text-body-sm">{{ $m->pack_size ?? '—' }}</td>
                            <td class="px-md py-md text-right font-bold {{ $stock <= 0 ? 'text-error' : ($stock <= $m->reorder_level ? 'text-yellow-600' : 'text-on-surface') }}">{{ number_format($stock) }}</td>
                            <td class="px-md py-md">
                                <div class="flex flex-wrap gap-1 justify-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $m->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-surface-variant text-on-surface-variant' }}">{{ $m->status }}</span>
                                    @if ($m->prescription_required)<span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-orange-100 text-orange-700">Rx</span>@endif
                                    @if ($m->controlled_medicine)<span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-red-100 text-red-700">Ctrl</span>@endif
                                </div>
                            </td>
                            <td class="px-md py-md">
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('medicines.show', $m) }}" class="p-1.5 text-outline hover:text-primary" title="View"><span class="material-symbols-outlined text-[18px]">visibility</span></a>
                                    <a href="{{ route('medicines.edit', $m) }}" class="p-1.5 text-outline hover:text-primary" title="Edit"><span class="material-symbols-outlined text-[18px]">edit</span></a>
                                    <form method="POST" action="{{ route('medicines.destroy', $m) }}" onsubmit="return confirm('Delete this medicine?');">
                                        @csrf @method('DELETE')
                                        <button class="p-1.5 text-outline hover:text-error" title="Delete"><span class="material-symbols-outlined text-[18px]">delete</span></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-md py-10 text-center text-outline">No medicines found. Click "Add Medicine" to create one.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-md border-t border-outline-variant">{{ $medicines->links() }}</div>
    </div>
@endsection
