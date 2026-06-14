<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    private array $actions = ['view', 'create', 'edit', 'delete', 'approve', 'reject', 'export', 'print', 'verify'];

    public function index()
    {
        $roles = Role::withCount('permissions', 'users')->orderBy('name')->get();

        return view('roles.index', ['roles' => $roles]);
    }

    public function edit(Role $role)
    {
        [$matrix, $sensitive] = $this->groupedPermissions();

        return view('roles.edit', [
            'role' => $role,
            'matrix' => $matrix,
            'sensitive' => $sensitive,
            'assigned' => $role->permissions->pluck('name')->all(),
            'actions' => $this->actions,
            'protected' => $role->name === 'super_admin',
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        if ($role->name === 'super_admin') {
            return back()->with('error', 'The Super Admin role cannot be modified — it always has full access.');
        }

        $permissions = collect($request->input('permissions', []))
            ->filter(fn ($v) => Permission::where('name', $v)->exists())
            ->values()->all();

        $role->syncPermissions($permissions);

        return redirect()->route('roles.index')->with('status', 'Permissions updated for the "'.Str::headline($role->name).'" role.');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60', 'unique:roles,name'],
        ]);

        Role::findOrCreate(Str::of($data['name'])->lower()->snake()->value(), 'web');

        return redirect()->route('roles.index')->with('status', 'Role created. Assign its permissions next.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if (in_array($role->name, ['super_admin', 'business_owner', 'branch_manager', 'pharmacist', 'cashier', 'inventory_manager', 'purchase_manager', 'accountant', 'auditor'])) {
            return back()->with('error', 'Built-in roles cannot be deleted.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('status', 'Role deleted.');
    }

    /**
     * Reconstruct the module x action matrix from the permission names,
     * returning [matrix, sensitivePermissions].
     */
    private function groupedPermissions(): array
    {
        $matrix = [];
        $sensitive = [];

        foreach (Permission::orderBy('name')->get() as $permission) {
            $parts = explode(' ', $permission->name, 2);
            if (count($parts) === 2 && in_array($parts[0], $this->actions, true) && ! str_contains($parts[1], ' ')) {
                $matrix[$parts[1]][$parts[0]] = $permission->name;
            } else {
                $sensitive[] = $permission->name;
            }
        }

        ksort($matrix);

        return [$matrix, $sensitive];
    }
}
