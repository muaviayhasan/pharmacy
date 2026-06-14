@php
    // Navigation definition. `route` is a named route; when it does not exist
    // yet the link falls back to "#" so the layout works before every screen
    // is built.
    $user = auth()->user();
    $can = fn (?string $perm) => ! $perm || ($user && $user->can($perm));

    $mainNav = array_filter([
        ['icon' => 'dashboard', 'label' => 'Dashboard', 'route' => 'dashboard', 'perm' => 'view dashboard'],
        ['icon' => 'point_of_sale', 'label' => 'POS', 'route' => 'pos.index', 'perm' => 'view pos'],
        ['icon' => 'schedule', 'label' => 'Shift Management', 'route' => 'shifts.index', 'perm' => 'view shift_management'],
        ['icon' => 'inventory_2', 'label' => 'Inventory', 'route' => 'inventory.index', 'perm' => 'view inventory'],
        ['icon' => 'medication', 'label' => 'Medicines', 'route' => 'medicines.index', 'perm' => 'view medicines'],
        ['icon' => 'shopping_cart', 'label' => 'Purchases', 'route' => 'purchases.index', 'perm' => 'view purchases'],
        ['icon' => 'payments', 'label' => 'Sales', 'route' => 'sales.index', 'perm' => 'view sales'],
        ['icon' => 'assignment_return', 'label' => 'Sale Returns', 'route' => 'sale-returns.index', 'perm' => 'view sale_returns'],
        ['icon' => 'keyboard_return', 'label' => 'Purchase Returns', 'route' => 'purchase-returns.index', 'perm' => 'view purchase_returns'],
        ['icon' => 'swap_horiz', 'label' => 'Stock Transfers', 'route' => 'stock-transfers.index', 'perm' => 'view stock_transfers'],
        ['icon' => 'tune', 'label' => 'Stock Adjustments', 'route' => 'stock-adjustments.index', 'perm' => 'view stock_adjustments'],
        ['icon' => 'event_busy', 'label' => 'Expiry Management', 'route' => 'expiry.index', 'perm' => 'view inventory'],
        ['icon' => 'trending_down', 'label' => 'Low Stock / Reorder', 'route' => 'low-stock.index', 'perm' => 'view inventory'],
        ['icon' => 'local_shipping', 'label' => 'Suppliers', 'route' => 'suppliers.index', 'perm' => 'view suppliers'],
        ['icon' => 'groups', 'label' => 'Customers', 'route' => 'customers.index', 'perm' => 'view customers'],
        ['icon' => 'account_balance_wallet', 'label' => 'Ledger', 'route' => 'ledger.index', 'perm' => 'view customer_ledger'],
        ['icon' => 'receipt_long', 'label' => 'Expenses', 'route' => 'expenses.index', 'perm' => 'view expenses'],
        ['icon' => 'assessment', 'label' => 'Reports', 'route' => 'reports.index', 'perm' => 'view reports'],
    ], fn ($i) => $can($i['perm'] ?? null));

    $bottomNav = array_filter([
        ['icon' => 'storefront', 'label' => 'Branches', 'route' => 'branches.index', 'perm' => 'view branch_management'],
        ['icon' => 'group', 'label' => 'Users', 'route' => 'users.index', 'perm' => 'view user_management'],
        ['icon' => 'admin_panel_settings', 'label' => 'Roles & Permissions', 'route' => 'roles.index', 'perm' => 'view roles_permissions'],
        ['icon' => 'notifications_active', 'label' => 'Alerts', 'route' => 'alerts.index', 'perm' => 'view alerts'],
        ['icon' => 'settings', 'label' => 'Settings', 'route' => 'settings.index', 'perm' => 'view settings'],
    ], fn ($i) => $can($i['perm'] ?? null));

    $renderItem = function (array $item) {
        $href = ($item['route'] && \Illuminate\Support\Facades\Route::has($item['route']))
            ? route($item['route'])
            : '#';
        $active = $item['route'] && request()->routeIs($item['route']);

        return [$href, $active];
    };

    $userName = $user?->name ?? 'Guest User';
    $userRole = $user ? \Illuminate\Support\Str::headline($user->getRoleNames()->first() ?? 'No Role') : 'System Administrator';
    $initials = \Illuminate\Support\Str::of($userName)->explode(' ')->take(2)->map(fn ($p) => \Illuminate\Support\Str::substr($p, 0, 1))->implode('');
@endphp

<aside class="fixed left-0 top-0 h-full w-[260px] flex flex-col py-md bg-inverse-surface z-50">
    {{-- Brand --}}
    <div class="px-lg pb-xl flex items-center gap-sm">
        <div class="w-10 h-10 rounded-lg bg-primary-container flex items-center justify-center">
            <span class="material-symbols-outlined text-white" style="font-variation-settings: 'FILL' 1;">medical_services</span>
        </div>
        <div>
            <h1 class="text-headline-lg font-bold text-primary-fixed leading-tight">PharmaCore</h1>
            <p class="text-label-sm text-surface-variant/70 uppercase tracking-widest">Pharmacy Management</p>
        </div>
    </div>

    {{-- Primary navigation --}}
    <nav class="flex-1 space-y-1 overflow-y-auto scrollbar-hide px-2">
        @foreach ($mainNav as $item)
            @php([$href, $active] = $renderItem($item))
            <a href="{{ $href }}"
               class="flex items-center gap-md px-md py-3 rounded-lg mx-2 transition-all active:scale-95 duration-100 {{ $active ? 'bg-primary-container text-white' : 'text-surface-variant hover:text-on-primary-fixed-variant hover:bg-surface-variant/10' }}">
                <span class="material-symbols-outlined">{{ $item['icon'] }}</span>
                <span class="font-label-md text-label-md">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    {{-- Secondary navigation + user --}}
    <div class="mt-auto px-2 space-y-1">
        @foreach ($bottomNav as $item)
            @php([$href, $active] = $renderItem($item))
            <a href="{{ $href }}"
               class="flex items-center gap-md px-md py-3 rounded-lg mx-2 transition-colors {{ $active ? 'bg-primary-container text-white' : 'text-surface-variant hover:text-on-primary-fixed-variant hover:bg-surface-variant/10' }}">
                <span class="material-symbols-outlined">{{ $item['icon'] }}</span>
                <span class="font-label-md text-label-md">{{ $item['label'] }}</span>
            </a>
        @endforeach

        <a href="{{ \Illuminate\Support\Facades\Route::has('profile.edit') ? route('profile.edit') : '#' }}"
           class="px-md pt-lg flex items-center gap-md hover:opacity-90 transition-opacity">
            <div class="w-10 h-10 rounded-full border-2 border-primary-fixed-dim bg-primary-container flex items-center justify-center text-white font-bold text-label-md uppercase">
                {{ $initials ?: 'PC' }}
            </div>
            <div>
                <p class="text-label-md text-on-primary-fixed font-bold">{{ $userName }}</p>
                <p class="text-[10px] text-surface-variant uppercase tracking-tighter">{{ $userRole }}</p>
            </div>
        </a>
    </div>
</aside>
