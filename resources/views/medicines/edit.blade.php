@extends('layouts.app')

@section('title', 'Edit Medicine')
@section('page-title', 'Edit Medicine')

@section('content')
    <a href="{{ route('medicines.index') }}" class="inline-flex items-center gap-xs text-primary text-label-md hover:underline">
        <span class="material-symbols-outlined text-[20px]">arrow_back</span> Back to Medicines
    </a>

    <form method="POST" action="{{ route('medicines.update', $medicine) }}" class="space-y-lg">
        @csrf
        @method('PUT')
        @include('medicines._form')
        <div class="flex justify-end gap-sm">
            <a href="{{ route('medicines.show', $medicine) }}" class="px-lg py-2.5 rounded-lg text-label-md border border-outline-variant text-on-surface-variant hover:bg-surface-container-low">Cancel</a>
            <button type="submit" class="px-lg py-2.5 rounded-lg text-label-md bg-primary text-on-primary hover:opacity-90 flex items-center gap-sm">
                <span class="material-symbols-outlined text-[18px]">save</span> Update Medicine
            </button>
        </div>
    </form>
@endsection
