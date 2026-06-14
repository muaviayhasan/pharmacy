@extends('layouts.app')

@section('title', 'Edit Branch')
@section('page-title', 'Edit Branch')

@section('content')
    <a href="{{ route('branches.index') }}" class="inline-flex items-center gap-xs text-primary text-label-md hover:underline">
        <span class="material-symbols-outlined text-[20px]">arrow_back</span> Back to Branches
    </a>
    <form method="POST" action="{{ route('branches.update', $branch) }}">
        @csrf @method('PUT')
        @include('branches._form')
    </form>
@endsection
