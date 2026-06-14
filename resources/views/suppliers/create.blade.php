@extends('layouts.app')

@section('title', 'Add Supplier')
@section('page-title', 'Add Supplier')

@section('content')
    <a href="{{ route('suppliers.index') }}" class="inline-flex items-center gap-xs text-primary text-label-md hover:underline">
        <span class="material-symbols-outlined text-[20px]">arrow_back</span> Back to Suppliers
    </a>
    <form method="POST" action="{{ route('suppliers.store') }}">
        @csrf
        @include('suppliers._form')
    </form>
@endsection
