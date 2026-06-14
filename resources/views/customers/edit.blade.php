@extends('layouts.app')

@section('title', 'Edit Customer')
@section('page-title', 'Edit Customer')

@section('content')
    <a href="{{ route('customers.index') }}" class="inline-flex items-center gap-xs text-primary text-label-md hover:underline">
        <span class="material-symbols-outlined text-[20px]">arrow_back</span> Back to Customers
    </a>
    <form method="POST" action="{{ route('customers.update', $customer) }}">
        @csrf @method('PUT')
        @include('customers._form')
    </form>
@endsection
