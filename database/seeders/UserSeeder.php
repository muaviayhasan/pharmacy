<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $mainBranch = Branch::where('code', 'MAIN')->first();
        $allBranchIds = Branch::pluck('id')->all();

        $users = [
            ['name' => 'Super Admin', 'email' => 'admin@pharmacore.test', 'role' => 'super_admin', 'branches' => $allBranchIds],
            ['name' => 'Business Owner', 'email' => 'owner@pharmacore.test', 'role' => 'business_owner', 'branches' => $allBranchIds],
            ['name' => 'Branch Manager', 'email' => 'manager@pharmacore.test', 'role' => 'branch_manager', 'branches' => [$mainBranch->id]],
            ['name' => 'John Pharmacist', 'email' => 'pharmacist@pharmacore.test', 'role' => 'pharmacist', 'branches' => [$mainBranch->id]],
            ['name' => 'Cashier One', 'email' => 'cashier@pharmacore.test', 'role' => 'cashier', 'branches' => [$mainBranch->id]],
            ['name' => 'Inventory Manager', 'email' => 'inventory@pharmacore.test', 'role' => 'inventory_manager', 'branches' => [$mainBranch->id]],
            ['name' => 'Purchase Manager', 'email' => 'purchase@pharmacore.test', 'role' => 'purchase_manager', 'branches' => [$mainBranch->id]],
            ['name' => 'Accountant', 'email' => 'accountant@pharmacore.test', 'role' => 'accountant', 'branches' => $allBranchIds],
            ['name' => 'Auditor', 'email' => 'auditor@pharmacore.test', 'role' => 'auditor', 'branches' => $allBranchIds],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'status' => 'active',
                    'default_branch_id' => $data['branches'][0] ?? null,
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$data['role']]);
            $user->branches()->syncWithoutDetaching(
                collect($data['branches'])->mapWithKeys(fn ($id) => [
                    $id => ['access_level' => 'full', 'status' => 'active'],
                ])->all()
            );

            // Assign branch manager to the main branch.
            if ($data['role'] === 'branch_manager' && $mainBranch && ! $mainBranch->manager_id) {
                $mainBranch->update(['manager_id' => $user->id]);
            }
        }
    }
}
