@extends('layouts.guest')

@section('title', 'Select Branch')

@section('content')
    <div class="bg-surface-container-lowest w-full rounded-xl border border-outline-variant p-xl shadow-[0_1px_3px_rgba(0,0,0,0.05)]">
        <div class="text-center mb-lg">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-primary-fixed/30 text-primary mb-md">
                <span class="material-symbols-outlined text-3xl">store</span>
            </div>
            <h2 class="text-headline-md font-semibold">Select Your Branch</h2>
            <p class="text-body-md text-on-surface-variant mt-xs">You have access to multiple branches. Choose the one to work in.</p>
        </div>

        @if ($errors->any())
            <div class="mb-lg bg-error-container text-on-error-container p-md rounded-lg flex gap-sm items-start text-body-sm">
                <span class="material-symbols-outlined text-error text-[20px]">error</span>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('branch.select.store') }}" class="space-y-sm">
            @csrf
            @foreach ($branches as $branch)
                <button type="submit" name="branch_id" value="{{ $branch->id }}"
                        class="w-full flex items-center justify-between p-md rounded-lg border border-outline-variant hover:border-primary hover:bg-surface-container-low transition-all text-left group">
                    <div class="flex items-center gap-md">
                        <div class="w-10 h-10 rounded-lg bg-primary-container text-on-primary-container flex items-center justify-center">
                            <span class="material-symbols-outlined">apartment</span>
                        </div>
                        <div>
                            <p class="text-label-md text-on-surface">{{ $branch->name }}</p>
                            <p class="text-label-sm text-outline uppercase tracking-wider">{{ $branch->code }} &middot; {{ ucfirst($branch->type) }}</p>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-outline group-hover:text-primary">chevron_right</span>
                </button>
            @endforeach
        </form>

        <form method="POST" action="{{ route('logout') }}" class="mt-lg text-center">
            @csrf
            <button type="submit" class="text-label-md text-on-surface-variant hover:text-primary">Sign in as a different user</button>
        </form>
    </div>
@endsection
