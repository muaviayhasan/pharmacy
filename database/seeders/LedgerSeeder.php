<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\LedgerEntry;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class LedgerSeeder extends Seeder
{
    /**
     * Seed a small set of opening balances and ledger entries so the Ledger
     * Management screen has representative data on a fresh install.
     */
    public function run(): void
    {
        $branchId = Branch::where('code', 'MAIN')->value('id') ?? Branch::value('id');

        // Supplier payable ledger.
        $supplier = Supplier::first();
        if ($supplier && ! LedgerEntry::where('ledger_type', 'supplier')->where('supplier_id', $supplier->id)->exists()) {
            $rows = [
                ['Opening balance', 0, 50000, 50000, 'OB-1001', 'opening', 20],
                ['Credit purchase PINV-2601', 0, 200000, 250000, 'PINV-2601', 'purchase', 12],
                ['Supplier payment', 80000, 0, 170000, 'PV-260601-0001', 'payment', 4],
            ];
            foreach ($rows as [$desc, $debit, $credit, $balance, $voucher, $type, $daysAgo]) {
                LedgerEntry::create([
                    'ledger_type' => 'supplier',
                    'supplier_id' => $supplier->id,
                    'branch_id' => $branchId,
                    'voucher_no' => $voucher,
                    'voucher_type' => $type,
                    'transaction_date' => now()->subDays($daysAgo)->toDateString(),
                    'description' => $desc,
                    'debit' => $debit,
                    'credit' => $credit,
                    'balance' => $balance,
                ]);
            }
            $supplier->update(['opening_balance' => 50000, 'current_balance' => 170000]);
        }

        // Customer receivable ledger.
        $customer = Customer::where('customer_type', 'credit')->first() ?? Customer::skip(1)->first();
        if ($customer && ! LedgerEntry::where('ledger_type', 'customer')->where('customer_id', $customer->id)->exists()) {
            LedgerEntry::create([
                'ledger_type' => 'customer',
                'customer_id' => $customer->id,
                'branch_id' => $branchId,
                'voucher_no' => 'SL-260601-0009',
                'voucher_type' => 'sale',
                'transaction_date' => now()->subDays(3)->toDateString(),
                'description' => 'Credit sale on account',
                'debit' => 12500,
                'credit' => 0,
                'balance' => 12500,
            ]);
            $customer->update(['current_balance' => 12500]);
        }

        // Cash account opening ledger.
        $cash = Account::where('branch_id', $branchId)->where('type', 'cash')->first();
        if ($cash && ! LedgerEntry::where('ledger_type', 'cash')->where('account_id', $cash->id)->exists()) {
            LedgerEntry::create([
                'ledger_type' => 'cash',
                'account_id' => $cash->id,
                'branch_id' => $branchId,
                'voucher_no' => 'OB-CASH',
                'voucher_type' => 'opening',
                'transaction_date' => now()->subDays(20)->toDateString(),
                'description' => 'Opening cash float',
                'debit' => 10000,
                'credit' => 0,
                'balance' => 10000,
            ]);
            $cash->update(['opening_balance' => 10000, 'current_balance' => 10000]);
        }
    }
}
