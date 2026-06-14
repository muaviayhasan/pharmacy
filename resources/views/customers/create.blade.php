@extends('layouts.app')

@section('title', 'Add Customer')
@section('page-title', 'Add Customer')

@section('content')
    <a href="{{ route('customers.index') }}" class="inline-flex items-center gap-xs text-primary text-label-md hover:underline">
        <span class="material-symbols-outlined text-[20px]">arrow_back</span> Back to Customers
    </a>
    <form method="POST" action="{{ route('customers.store') }}">
        @csrf
        @include('customers._form')
    </form>
@endsection
