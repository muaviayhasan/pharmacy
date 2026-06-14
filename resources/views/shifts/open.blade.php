@extends('layouts.app')

@section('title', 'Open Shift')
@section('page-title', 'Open POS Shift')

@section('content')
    <a href="{{ route('shifts.index') }}" class="inline-flex items-center gap-xs text-primary text-label-md hover:underline">
        <span class="material-symbols-outlined text-[20px]">arrow_back</span> Back to Shifts
    </a>
    @include('partials.flash')

    @if ($openShift)
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-md flex items-center justify-between max-w-3xl">
            <div class="flex items-center gap-sm">
                <span class="material-symbols-outlined text-amber-600">info</span>
                <p class="text-body-sm text-amber-900">You already have an open shift <strong>{{ $openShift->shift_no }}</strong>. Close it before opening a new one.</p>
            </div>
            <a href="{{ route('shifts.close', $openShift) }}" class="px-md py-2 bg-primary text-on-primary rounded-lg text-label-md hover:opacity-90">Close Shift</a>
        </div>
    @else
        <form method="POST" action="{{ route('shifts.store') }}" class="max-w-3xl">
            @csrf
            <div class="bg-white rounded-xl border border-outline-variant custom-shadow overflow-hidden">
                <div class="bg-surface-container-low px-md py-sm border-b border-outline-variant">
                    <span class="text-label-md font-bold text-primary flex items-center gap-xs"><span class="material-symbols-outlined text-[18px]">point_of_sale</span> Shift Information</span>
                </div>
                <div class="p-lg grid grid-cols-1 md:grid-cols-2 gap-lg">
                    <div class="space-y-sm">
                        <label class="block text-label-sm font-bold text-on-surface-variant">Branch <span class="text-error">*</span></label>
                        <select name="branch_id" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                            @foreach ($branches as $b)<option value="{{ $b->id }}" @selected((int) old('branch_id', $activeBranchId) === $b->id)>{{ $b->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="space-y-sm">
                        <label class="block text-label-sm font-bold text-on-surface-variant">POS Counter</label>
                        <select name="pos_counter_id" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                            <option value="">No specific counter</option>
                            @foreach ($counters as $c)<option value="{{ $c->id }}" @selected((string) old('pos_counter_id') === (string) $c->id)>{{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="space-y-sm">
                        <label class="block text-label-sm font-bold text-on-surface-variant">Cashier</label>
                        <input type="text" disabled value="{{ auth()->user()->name }}" class="w-full border border-outline-variant rounded-lg p-md text-body-sm bg-surface-container-low text-outline">
                    </div>
                    <div class="space-y-sm">
                        <label class="block text-label-sm font-bold text-on-surface-variant">Opening Time</label>
                        <input type="text" disabled value="{{ now()->format('d M Y, h:i A') }}" class="w-full border border-outline-variant rounded-lg p-md text-body-sm bg-surface-container-low text-outline">
                    </div>
                    <div class="space-y-sm md:col-span-2">
                        <label class="block text-label-sm font-bold text-on-surface-variant">Opening Cash (float) <span class="text-error">*</span></label>
                        <input name="opening_cash" type="number" step="0.01" min="0" value="{{ old('opening_cash', 0) }}" required class="w-full border border-outline-variant rounded-lg p-md text-headline-md font-bold focus:ring-2 focus:ring-primary outline-none">
                    </div>
                    <div class="space-y-sm md:col-span-2">
                        <label class="block text-label-sm font-bold text-on-surface-variant">Notes</label>
                        <textarea name="notes" rows="2" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">{{ old('notes') }}</textarea>
                    </div>
                </div>
                <div class="px-lg py-md bg-surface-container-low flex justify-end gap-sm">
                    <a href="{{ route('shifts.index') }}" class="px-lg py-2.5 rounded-lg text-label-md border border-outline-variant text-on-surface-variant hover:bg-surface-container">Cancel</a>
                    <button type="submit" class="px-lg py-2.5 rounded-lg text-label-md bg-primary text-on-primary hover:opacity-90 flex items-center gap-sm"><span class="material-symbols-outlined text-[18px]">play_circle</span> Open Shift</button>
                </div>
            </div>
        </form>
    @endif
@endsection
