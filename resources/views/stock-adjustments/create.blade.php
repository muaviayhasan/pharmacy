@extends('layouts.app')

@section('title', 'New Stock Adjustment')
@section('page-title', 'Stock Adjustment')

@section('content')
    <a href="{{ route('stock-adjustments.index') }}" wire:navigate class="inline-flex items-center gap-xs text-primary text-label-md hover:underline">
        <span class="material-symbols-outlined text-[20px]">arrow_back</span> Back to Adjustments
    </a>
    @livewire('inventory.create-adjustment')
@endsection
