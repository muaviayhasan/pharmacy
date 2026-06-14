@extends('layouts.app')

@section('title', 'Add Medicine')
@section('page-title', 'Add Medicine')

@section('content')
    <a href="{{ route('medicines.index') }}" class="inline-flex items-center gap-xs text-primary text-label-md hover:underline">
        <span class="material-symbols-outlined text-[20px]">arrow_back</span> Back to Medicines
    </a>

    <form method="POST" action="{{ route('medicines.store') }}" class="space-y-lg">
        @csrf
        @include('medicines._form')
        <div class="flex justify-end gap-sm">
            <a href="{{ route('medicines.index') }}" class="px-lg py-2.5 rounded-lg text-label-md border border-outline-variant text-on-surface-variant hover:bg-surface-container-low">Cancel</a>
            <button type="submit" name="action" value="save_add_another" class="px-lg py-2.5 rounded-lg text-label-md bg-surface-container-highest text-primary hover:bg-surface-variant">Save & Add Another</button>
            <button type="submit" name="action" value="save" class="px-lg py-2.5 rounded-lg text-label-md bg-primary text-on-primary hover:opacity-90 flex items-center gap-sm">
                <span class="material-symbols-outlined text-[18px]">save</span> Save Medicine
            </button>
        </div>
    </form>
@endsection
