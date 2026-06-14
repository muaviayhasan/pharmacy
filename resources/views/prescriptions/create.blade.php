@extends('layouts.app')

@section('title', 'Log Prescription')
@section('page-title', 'Log Prescription')

@section('content')
    <a href="{{ route('prescriptions.index') }}" class="inline-flex items-center gap-xs text-primary text-label-md hover:underline">
        <span class="material-symbols-outlined text-[20px]">arrow_back</span> Back to Prescriptions
    </a>
    @include('partials.flash')

    <form method="POST" action="{{ route('prescriptions.store') }}" class="max-w-3xl">
        @csrf
        <div class="bg-white rounded-xl border border-outline-variant custom-shadow overflow-hidden">
            <div class="bg-surface-container-low px-md py-sm border-b border-outline-variant">
                <span class="text-label-md font-bold text-primary flex items-center gap-xs"><span class="material-symbols-outlined text-[18px]">prescriptions</span> Prescription Details</span>
            </div>
            <div class="p-lg grid grid-cols-1 md:grid-cols-2 gap-lg">
                <div class="space-y-sm">
                    <label class="block text-label-sm font-bold text-on-surface-variant">Branch</label>
                    <select name="branch_id" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                        @foreach ($branches as $b)<option value="{{ $b->id }}" @selected((int) old('branch_id', $activeBranchId) === $b->id)>{{ $b->name }}</option>@endforeach
                    </select>
                </div>
                <div class="space-y-sm">
                    <label class="block text-label-sm font-bold text-on-surface-variant">Link to Sale (optional)</label>
                    <select name="sale_id" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                        <option value="">No linked sale</option>
                        @foreach ($pendingSales as $s)<option value="{{ $s->id }}" @selected((string) old('sale_id', $saleId) === (string) $s->id)>{{ $s->sale_no }} — {{ $s->sale_date?->format('d M') }}</option>@endforeach
                    </select>
                </div>
                <div class="space-y-sm">
                    <label class="block text-label-sm font-bold text-on-surface-variant">Customer</label>
                    <select name="customer_id" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                        <option value="">Walk-in</option>
                        @foreach ($customers as $c)<option value="{{ $c->id }}" @selected((string) old('customer_id') === (string) $c->id)>{{ $c->name }}</option>@endforeach
                    </select>
                </div>
                <div class="space-y-sm">
                    <label class="block text-label-sm font-bold text-on-surface-variant">Prescription Date</label>
                    <input name="prescription_date" type="date" value="{{ old('prescription_date', now()->toDateString()) }}" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                </div>
                <div class="space-y-sm">
                    <label class="block text-label-sm font-bold text-on-surface-variant">Doctor Name <span class="text-error">*</span></label>
                    <input name="doctor_name" type="text" value="{{ old('doctor_name') }}" required class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                </div>
                <div class="space-y-sm">
                    <label class="block text-label-sm font-bold text-on-surface-variant">Doctor Registration #</label>
                    <input name="doctor_registration_no" type="text" value="{{ old('doctor_registration_no') }}" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                </div>
                <div class="space-y-sm">
                    <label class="block text-label-sm font-bold text-on-surface-variant">Clinic / Hospital</label>
                    <input name="clinic_name" type="text" value="{{ old('clinic_name') }}" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                </div>
                <div class="space-y-sm">
                    <label class="block text-label-sm font-bold text-on-surface-variant">Attachment Reference</label>
                    <input name="attachment_path" type="text" value="{{ old('attachment_path') }}" placeholder="Scan URL / file reference" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">
                </div>
                <div class="space-y-sm md:col-span-2">
                    <label class="block text-label-sm font-bold text-on-surface-variant">Notes</label>
                    <textarea name="notes" rows="2" class="w-full border border-outline-variant rounded-lg p-md text-body-sm focus:ring-2 focus:ring-primary outline-none">{{ old('notes') }}</textarea>
                </div>
            </div>
            <div class="px-lg py-md bg-surface-container-low flex justify-end gap-sm">
                <a href="{{ route('prescriptions.index') }}" class="px-lg py-2.5 rounded-lg text-label-md border border-outline-variant text-on-surface-variant hover:bg-surface-container">Cancel</a>
                <button type="submit" class="px-lg py-2.5 rounded-lg text-label-md bg-primary text-on-primary hover:opacity-90 flex items-center gap-sm"><span class="material-symbols-outlined text-[18px]">save</span> Log for Verification</button>
            </div>
        </div>
    </form>
@endsection
