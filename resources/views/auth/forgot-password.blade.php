@extends('layouts.guest')

@section('title', 'Forgot Password')

@section('content')
    <div class="bg-surface-container-lowest w-full rounded-xl border border-outline-variant p-xl shadow-[0_1px_3px_rgba(0,0,0,0.05)]">
        <h2 class="text-headline-md mb-xs font-semibold">Forgot Password?</h2>
        <p class="text-body-md text-on-surface-variant mb-lg">Enter your registered email address and we will send you a secure password reset link.</p>

        @if ($errors->any())
            <div class="mb-lg bg-error-container text-on-error-container p-md rounded-lg flex gap-sm items-start text-body-sm">
                <span class="material-symbols-outlined text-error text-[20px]">error</span>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-lg">
            @csrf
            <div>
                <label class="block text-label-md mb-xs text-on-surface">Email address</label>
                <input name="email" type="email" value="{{ old('email') }}" required autofocus
                       class="w-full h-12 px-md border border-outline-variant rounded-lg text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"
                       placeholder="pharmacist@pharmacore.com">
            </div>
            <button type="submit"
                    class="w-full h-12 bg-primary-container hover:bg-primary-container/90 text-on-primary-container text-label-md rounded-lg transition-all active:scale-[0.98]">
                Send Reset Link
            </button>
            <div class="flex items-center justify-between text-label-md pt-sm">
                <a href="{{ route('login') }}" class="text-primary hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back to Login
                </a>
            </div>
            <div class="bg-surface-container-low p-md rounded-lg border border-outline-variant/30 flex gap-sm items-start">
                <span class="material-symbols-outlined text-primary text-[20px]">info</span>
                <p class="text-[13px] text-on-surface-variant leading-relaxed">
                    Security Note: Reset links expire within 60 minutes of being generated for your protection.
                </p>
            </div>
        </form>
    </div>
@endsection
