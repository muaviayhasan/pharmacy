<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Modules and the CRUD-style actions that apply to each.
     */
    private array $modules = [
        'dashboard' => ['view', 'export'],
        'pos' => ['view', 'create', 'print'],
        'sales' => ['view', 'create', 'edit', 'delete', 'export', 'print'],
        'sale_returns' => ['view', 'create', 'approve', 'reject', 'print'],
        'purchases' => ['view', 'create', 'edit', 'delete', 'export', 'print'],
        'purchase_returns' => ['view', 'create', 'approve', 'reject', 'print'],
        'inventory' => ['view', 'export'],
        'medicines' => ['view', 'create', 'edit', 'delete', 'export'],
        'stock_adjustments' => ['view', 'create', 'approve', 'reject'],
        'stock_transfers' => ['view', 'create', 'approve', 'reject'],
        'suppliers' => ['view', 'create', 'edit', 'delete'],
        'customers' => ['view', 'create', 'edit', 'delete'],
        'customer_ledger' => ['view', 'create', 'export'],
        'supplier_ledger' => ['view', 'create', 'export'],
        'expenses' => ['view', 'create', 'edit', 'delete', 'approve', 'reject'],
        'reports' => ['view', 'export', 'print'],
        'alerts' => ['view', 'edit'],
        'shift_management' => ['view', 'create', 'approve'],
        'barcode_management' => ['view', 'create', 'print'],
        'user_management' => ['view', 'create', 'edit', 'delete'],
        'roles_permissions' => ['view', 'create', 'edit', 'delete'],
        'branch_management' => ['view', 'create', 'edit', 'delete'],
        'settings' => ['view', 'edit'],
        'audit_logs' => ['view', 'export'],
        'prescriptions' => ['view', 'verify', 'approve', 'reject'],
    ];

    /**
     * High-risk permissions that should be granted sparingly.
     */
    private array $sensitive = [
        'delete sale invoice',
        'delete purchase invoice',
        'edit completed sale',
        'edit completed purchase',
        'change medicine price',
        'allow negative stock sale',
        'approve controlled medicine sale',
        'delete stock movement',
        'approve stock adjustment',
        'approve shift cash shortage',
        'manage users',
        'manage roles',
        'change system settings',
        'view profit reports',
        'export financial reports',
        'access audit logs',
        'delete audit logs',
        'backup and restore system',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Build the full permission list.
        $permissions = [];
        foreach ($this->modules as $module => $actions) {
            foreach ($actions as $action) {
                $permissions[] = "{$action} {$module}";
            }
        }
        $permissions = array_merge($permissions, $this->sensitive);

        foreach ($permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }

        // Helper closures for granting permissions by module group.
        $byModules = fn (array $mods) => collect($mods)
            ->flatMap(fn ($m) => collect($this->modules[$m])->map(fn ($a) => "{$a} {$m}"))
            ->all();

        $roles = [
            'super_admin' => Permission::pluck('name')->all(), // everything
            'business_owner' => array_merge(
                $byModules(['dashboard', 'sales', 'purchases', 'inventory', 'medicines', 'suppliers', 'customers', 'customer_ledger', 'supplier_ledger', 'expenses', 'reports', 'alerts', 'branch_management']),
                ['view profit reports', 'export financial reports', 'access audit logs']
            ),
            'branch_manager' => array_merge(
                $byModules(['dashboard', 'pos', 'sales', 'sale_returns', 'purchases', 'purchase_returns', 'inventory', 'medicines', 'stock_adjustments', 'stock_transfers', 'suppliers', 'customers', 'expenses', 'reports', 'alerts', 'shift_management', 'barcode_management', 'prescriptions']),
                ['approve stock adjustment', 'approve shift cash shortage', 'approve controlled medicine sale', 'view profit reports']
            ),
            'pharmacist' => array_merge(
                $byModules(['dashboard', 'pos', 'medicines', 'inventory', 'prescriptions', 'alerts']),
                ['approve controlled medicine sale']
            ),
            'cashier' => $byModules(['dashboard', 'pos', 'sales', 'sale_returns', 'customers', 'shift_management', 'barcode_management']),
            'inventory_manager' => array_merge(
                $byModules(['dashboard', 'inventory', 'medicines', 'stock_adjustments', 'stock_transfers', 'barcode_management', 'alerts', 'reports']),
                ['change medicine price']
            ),
            'purchase_manager' => $byModules(['dashboard', 'purchases', 'purchase_returns', 'suppliers', 'supplier_ledger', 'reports', 'alerts']),
            'accountant' => array_merge(
                $byModules(['dashboard', 'customer_ledger', 'supplier_ledger', 'expenses', 'reports']),
                ['view profit reports', 'export financial reports']
            ),
            'auditor' => array_merge(
                $byModules(['dashboard', 'audit_logs', 'reports']),
                ['access audit logs', 'view profit reports']
            ),
        ];

        foreach ($roles as $roleName => $perms) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions(array_values(array_unique($perms)));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
