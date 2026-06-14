<!DOCTYPE html>
<html class="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>@yield('title', 'Secure Portal') | {{ config('app.name', 'PharmaCore') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-background text-on-surface min-h-screen">
    <main class="flex min-h-screen">
        {{-- Left: brand panel --}}
        <section class="hidden lg:flex w-5/12 bg-primary-container relative overflow-hidden items-center justify-center">
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-primary-fixed opacity-10 blur-3xl rounded-full"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-on-primary-container opacity-10 blur-3xl rounded-full"></div>

            <div class="relative z-10 p-2xl max-w-xl text-on-primary">
                <div class="flex items-center gap-sm mb-xl">
                    <span class="material-symbols-outlined text-primary-fixed text-4xl" style="font-variation-settings: 'FILL' 1;">medical_services</span>
                    <h1 class="text-headline-lg font-bold tracking-tight">{{ config('app.name', 'PharmaCore') }}</h1>
                </div>
                <div class="space-y-md">
                    <h2 class="text-headline-xl leading-tight">Clinical precision in every digital interaction.</h2>
                    <p class="text-body-lg opacity-90 max-w-md">
                        Multi-factor authentication and audit-protected access keep sensitive pharmaceutical
                        data secure while keeping your team's workflow fast.
                    </p>
                </div>
                <div class="mt-2xl flex flex-wrap gap-lg items-center opacity-80">
                    <span class="flex items-center gap-1.5 text-label-sm"><span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">verified_user</span> Encrypted</span>
                    <span class="flex items-center gap-1.5 text-label-sm"><span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">security</span> OTP Protected</span>
                    <span class="flex items-center gap-1.5 text-label-sm"><span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">fact_check</span> Audit Logged</span>
                </div>
            </div>
        </section>

        {{-- Right: interaction panel --}}
        <section class="flex-1 flex items-center justify-center p-md lg:p-2xl bg-surface">
            <div class="w-full max-w-md space-y-xl">
                {{-- Mobile brand --}}
                <div class="lg:hidden flex flex-col items-center gap-xs">
                    <div class="w-12 h-12 bg-primary flex items-center justify-center rounded-lg shadow-sm">
                        <span class="material-symbols-outlined text-white text-[32px]">medical_services</span>
                    </div>
                    <h1 class="text-headline-lg text-primary tracking-tight font-bold">{{ config('app.name', 'PharmaCore') }}</h1>
                    <p class="text-label-md text-on-surface-variant uppercase tracking-widest">Pharmacy ERP</p>
                </div>

                @if (session('status'))
                    <div class="bg-primary-fixed/40 border border-primary text-on-primary-fixed p-md rounded-lg flex gap-sm items-start text-body-sm">
                        <span class="material-symbols-outlined text-primary text-[20px]">check_circle</span>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @yield('content')

                <p class="text-center text-label-sm text-on-surface-variant/70">
                    &copy; {{ now()->year }} {{ config('app.name', 'PharmaCore') }} ERP. All security interactions are logged for compliance.
                </p>
            </div>
        </section>
    </main>

    @stack('scripts')
</body>
</html>
