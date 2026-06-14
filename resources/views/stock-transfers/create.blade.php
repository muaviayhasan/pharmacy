@extends('layouts.app')

@section('title', 'New Stock Transfer')
@section('page-title', 'Stock Transfer')

@section('content')
    <a href="{{ route('stock-transfers.index') }}" wire:navigate class="inline-flex items-center gap-xs text-primary text-label-md hover:underline">
        <span class="material-symbols-outlined text-[20px]">arrow_back</span> Back to Transfers
    </a>
    @livewire('inventory.create-transfer')
@endsection
