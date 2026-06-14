@extends('layouts.app')

@section('title', 'New Purchase Return')
@section('page-title', 'Purchase Return')

@section('content')
    <a href="{{ route('purchase-returns.index') }}" wire:navigate class="inline-flex items-center gap-xs text-primary text-label-md hover:underline">
        <span class="material-symbols-outlined text-[20px]">arrow_back</span> Back to Purchase Returns
    </a>
    @livewire('purchases.create-purchase-return')
@endsection
