@extends('layouts.guest')

@section('title', 'Reset Password')

@section('content')
    <div class="bg-surface-container-lowest w-full rounded-xl border border-outline-variant p-xl shadow-[0_1px_3px_rgba(0,0,0,0.05)]">
        <h2 class="text-headline-md mb-xs font-semibold">Create New Password</h2>
        <p class="text-body-md text-on-surface-variant mb-lg">Your identity has been verified. Please set a strong new password.</p>

        @if ($errors->any())
            <div class="mb-lg bg-error-container text-on-error-container p-md rounded-lg flex gap-sm items-start text-body-sm">
                <span class="material-symbols-outlined text-error text-[20px]">error</span>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="space-y-md">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">
            <div>
                <label class="block text-label-md mb-xs text-on-surface">Email address</label>
                <input name="email" type="email" value="{{ old('email', $request->email) }}" required
                       class="w-full h-12 px-md border border-outline-variant rounded-lg text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
            </div>
            <div class="relative">
                <label class="block text-label-md mb-xs text-on-surface">New Password</label>
                <input name="password" type="password" required
                       class="w-full h-12 pl-md pr-12 border border-outline-variant rounded-lg focus:ring-1 focus:ring-primary outline-none">
                <button type="button" onclick="const i=this.parentElement.querySelector('input'); const s=this.querySelector('span'); if(i.type==='password'){i.type='text';s.textContent='visibility_off';}else{i.type='password';s.textContent='visibility';}"
                        class="absolute right-4 top-10 text-on-surface-variant">
                    <span class="material-symbols-outlined">visibility</span>
                </button>
            </div>
            <div>
                <label class="block text-label-md mb-xs text-on-surface">Confirm Password</label>
                <input name="password_confirmation" type="password" required
                       class="w-full h-12 px-md border border-outline-variant rounded-lg focus:ring-1 focus:ring-primary outline-none">
            </div>
            <div class="grid grid-cols-2 gap-y-2 text-[13px] text-on-surface-variant py-sm">
                <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px] text-outline-variant">check_circle</span> 8+ characters</div>
                <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px] text-outline-variant">check_circle</span> Uppercase</div>
                <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px] text-outline-variant">check_circle</span> Number</div>
                <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px] text-outline-variant">check_circle</span> Special char</div>
            </div>
            <button type="submit" class="w-full h-12 bg-primary-container text-on-primary-container text-label-md rounded-lg">
                Reset Password
            </button>
        </form>
    </div>
@endsection
