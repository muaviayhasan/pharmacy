@extends('layouts.guest')

@section('title', 'Login')

@section('content')
    <div class="bg-surface-container-lowest w-full rounded-xl border border-outline-variant p-xl shadow-[0_1px_3px_rgba(0,0,0,0.05)]">
        <h2 class="text-headline-md mb-xs font-semibold">Welcome back</h2>
        <p class="text-body-md text-on-surface-variant mb-lg">Sign in to your PharmaCore account to continue.</p>

        @if ($errors->any())
            <div class="mb-lg bg-error-container text-on-error-container p-md rounded-lg flex gap-sm items-start text-body-sm">
                <span class="material-symbols-outlined text-error text-[20px]">error</span>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-lg">
            @csrf
            <div>
                <label class="block text-label-md mb-xs text-on-surface">Email address</label>
                <input name="email" type="email" value="{{ old('email') }}" required autofocus
                       class="w-full h-12 px-md border border-outline-variant rounded-lg text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"
                       placeholder="you@pharmacore.com">
            </div>
            <div>
                <div class="flex items-center justify-between mb-xs">
                    <label class="block text-label-md text-on-surface">Password</label>
                    <a href="{{ route('password.request') }}" class="text-label-md text-primary hover:underline">Forgot password?</a>
                </div>
                <div class="relative">
                    <input name="password" type="password" required
                           class="w-full h-12 pl-md pr-12 border border-outline-variant rounded-lg text-body-md focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                    <button type="button" onclick="const i=this.parentElement.querySelector('input'); const s=this.querySelector('span'); if(i.type==='password'){i.type='text';s.textContent='visibility_off';}else{i.type='password';s.textContent='visibility';}"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant">
                        <span class="material-symbols-outlined">visibility</span>
                    </button>
                </div>
            </div>
            <label class="flex items-center gap-sm text-body-sm text-on-surface-variant cursor-pointer">
                <input type="checkbox" name="remember" class="w-4 h-4 text-primary border-outline-variant rounded focus:ring-primary">
                Remember me on this device
            </label>
            <button type="submit"
                    class="w-full h-12 bg-primary-container hover:bg-primary-container/90 text-on-primary-container text-label-md rounded-lg transition-all active:scale-[0.98] flex items-center justify-center gap-sm">
                <span class="material-symbols-outlined text-[18px]">login</span> Sign In
            </button>
        </form>

        <div class="bg-surface-container-low p-md rounded-lg border border-outline-variant/30 flex gap-sm items-start mt-lg">
            <span class="material-symbols-outlined text-primary text-[20px]">info</span>
            <p class="text-[13px] text-on-surface-variant leading-relaxed">
                Accounts are created by your administrator. Contact your branch manager if you need access.
            </p>
        </div>
    </div>
@endsection
