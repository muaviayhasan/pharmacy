<!DOCTYPE html>
<html class="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>@yield('title', 'Dashboard') | {{ config('app.name', 'PharmaCore') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="bg-background text-on-surface overflow-hidden">
    @include('partials.sidebar')

    {{-- Main Content Wrapper --}}
    <main class="pl-[260px] h-screen flex flex-col overflow-hidden">
        @include('partials.topbar')

        {{-- Scrollable Page Content --}}
        <section class="flex-1 overflow-y-auto p-margin-desktop space-y-lg bg-background">
            @include('partials.flash')
            @yield('content')
        </section>

        @include('partials.footer')
    </main>

    @yield('fab')

    {{-- Realtime critical-alert toast (broadcast over Reverb) --}}
    <div id="critical-alert-toast" class="hidden fixed top-20 right-8 z-[60] bg-error text-on-error px-md py-sm rounded-lg shadow-xl flex items-center gap-sm max-w-sm">
        <span class="material-symbols-outlined">warning</span>
        <span id="critical-alert-text" class="text-body-sm"></span>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (! window.Echo) return;
            window.Echo.channel('alerts').listen('.CriticalAlertRaised', function (e) {
                var toast = document.getElementById('critical-alert-toast');
                document.getElementById('critical-alert-text').textContent =
                    e.count + ' critical alert' + (e.count > 1 ? 's' : '') + (e.latest ? ': ' + e.latest : '');
                toast.classList.remove('hidden');
                setTimeout(function () { toast.classList.add('hidden'); }, 8000);
            });
        });
    </script>

    @livewireScripts
    @stack('scripts')
</body>
</html>
