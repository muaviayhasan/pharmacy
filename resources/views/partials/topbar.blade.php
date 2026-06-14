<header class="flex justify-between items-center h-16 w-full px-margin-desktop bg-surface border-b border-outline-variant z-40 shrink-0">
    {{-- Left: page title + global search --}}
    <div class="flex items-center gap-lg">
        <h2 class="text-headline-md font-headline-md text-on-surface">@yield('page-title', 'Dashboard')</h2>
        <div class="relative w-96 hidden lg:block">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">search</span>
            <input type="text"
                   class="w-full h-10 pl-10 pr-4 bg-surface-container-low border border-outline-variant rounded-full text-body-sm focus:ring-2 focus:ring-secondary-container focus:outline-none transition-shadow"
                   placeholder="Search medicines, orders, customers...">
        </div>
    </div>

    {{-- Right: branch selector, date, notifications, user --}}
    <div class="flex items-center gap-md">
        <div class="flex items-center gap-xs px-md py-1.5 bg-surface-container-high rounded-full border border-outline-variant cursor-pointer hover:bg-surface-container-highest transition-colors">
            <span class="material-symbols-outlined text-sm">store</span>
            <span class="text-label-md">All Branches</span>
            <span class="material-symbols-outlined text-sm">expand_more</span>
        </div>

        <div class="hidden md:flex items-center gap-xs px-md py-1.5 bg-primary-container text-white rounded-full cursor-pointer hover:opacity-90 transition-opacity">
            <span class="material-symbols-outlined text-sm">calendar_today</span>
            <span class="text-label-md">{{ now()->format('d M Y') }}</span>
        </div>

        <button class="relative w-10 h-10 flex items-center justify-center rounded-full hover:bg-surface-container-low transition-all">
            <span class="material-symbols-outlined text-on-surface-variant">notifications</span>
            <span class="absolute top-2 right-2 w-2 h-2 bg-error rounded-full"></span>
        </button>

        @auth
            <form method="POST" action="{{ Route::has('logout') ? route('logout') : '#' }}">
                @csrf
                <button type="submit" title="Logout"
                        class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-surface-container-low transition-all">
                    <span class="material-symbols-outlined text-on-surface-variant">logout</span>
                </button>
            </form>
        @endauth
    </div>
</header>
