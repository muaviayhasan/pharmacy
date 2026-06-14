@extends('layouts.app')

@section('title', 'New Sale Return')
@section('page-title', 'Sale Return')

@section('content')
    <a href="{{ route('sale-returns.index') }}" wire:navigate class="inline-flex items-center gap-xs text-primary text-label-md hover:underline">
        <span class="material-symbols-outlined text-[20px]">arrow_back</span> Back to Sale Returns
    </a>
    @livewire('sales.create-sale-return')
@endsection
