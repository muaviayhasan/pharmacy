@extends('layouts.app')

@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
    <div class="max-w-4xl">
        <a href="{{ route('users.index') }}" class="inline-flex items-center gap-xs text-primary text-label-md hover:underline mb-md">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span> Back to Users
        </a>
        <div class="bg-surface-container-lowest rounded-xl border border-outline-variant custom-shadow overflow-hidden">
            <div class="px-lg py-md bg-surface-container-low border-b border-outline-variant flex items-center gap-sm">
                <span class="material-symbols-outlined text-primary">manage_accounts</span>
                <h4 class="text-headline-md text-on-surface font-semibold">Edit {{ $user->name }}</h4>
            </div>
            <form method="POST" action="{{ route('users.update', $user) }}" class="p-lg space-y-lg">
                @csrf
                @method('PUT')
                @include('users._form')
            </form>
        </div>
    </div>
@endsection
