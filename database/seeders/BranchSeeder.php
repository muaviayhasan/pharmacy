<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            ['name' => 'Main Branch', 'code' => 'MAIN', 'type' => 'main', 'city' => 'Lahore'],
            ['name' => 'North Wing Outlet', 'code' => 'NORTH', 'type' => 'outlet', 'city' => 'Lahore'],
        ];

        foreach ($branches as $data) {
            $branch = Branch::firstOrCreate(['code' => $data['code']], $data + [
                'phone' => '042-000000',
                'status' => 'active',
            ]);

            Account::firstOrCreate(
                ['branch_id' => $branch->id, 'name' => 'Cash Drawer'],
                ['type' => 'cash', 'opening_balance' => 0, 'current_balance' => 0]
            );
            Account::firstOrCreate(
                ['branch_id' => $branch->id, 'name' => 'Bank Account'],
                ['type' => 'bank', 'bank_name' => 'HBL', 'opening_balance' => 0, 'current_balance' => 0]
            );
        }
    }
}
