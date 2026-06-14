@extends('layouts.app')

@section('title', 'Add Branch')
@section('page-title', 'Add Branch')

@section('content')
    <a href="{{ route('branches.index') }}" class="inline-flex items-center gap-xs text-primary text-label-md hover:underline">
        <span class="material-symbols-outlined text-[20px]">arrow_back</span> Back to Branches
    </a>
    <form method="POST" action="{{ route('branches.store') }}">
        @csrf
        @include('branches._form')
    </form>
@endsection
