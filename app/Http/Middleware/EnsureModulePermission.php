<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Maps a route name to the permission required to access it. Super admins
 * bypass every check via the Gate::before hook in AppServiceProvider.
 */
class EnsureModulePermission
{
    /**
     * Route-name prefix => required permission.
     *
     * @var array<string, string>
     */
    private array $map = [
        'pos.' => 'view pos',
        'shifts.' => 'view shift_management',
        'inventory.' => 'view inventory',
        'expiry.' => 'view inventory',
        'low-stock.' => 'view inventory',
        'stock-adjustments.' => 'view stock_adjustments',
        'stock-transfers.' => 'view stock_transfers',
        'medicines.' => 'view medicines',
        'purchases.' => 'view purchases',
        'purchase-returns.' => 'view purchase_returns',
        'sales.' => 'view sales',
        'sale-returns.' => 'view sale_returns',
        'customers.' => 'view customers',
        'suppliers.' => 'view suppliers',
        'branches.' => 'view branch_management',
        'ledger.' => 'view customer_ledger',
        'expenses.' => 'view expenses',
        'reports.' => 'view reports',
        'users.' => 'view user_management',
        'roles.' => 'view roles_permissions',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $name = $request->route()?->getName();

        if (! $user || ! $name) {
            return $next($request);
        }

        foreach ($this->map as $prefix => $permission) {
            if (Str::startsWith($name, $prefix)) {
                abort_unless($user->can($permission), 403, 'You do not have permission to access this section.');
                break;
            }
        }

        return $next($request);
    }
}
