<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Customer;
use App\Models\LedgerEntry;
use App\Models\Payment;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class LedgerService
{
    /**
     * Record a payment received from a customer (reduces receivable).
     */
    public function recordCustomerReceipt(
        Customer $customer,
        float $amount,
        ?int $accountId,
        string $method,
        ?string $reference,
        int $branchId,
        int $userId,
    ): Payment {
        return DB::transaction(function () use ($customer, $amount, $accountId, $method, $reference, $branchId, $userId) {
            $customer = Customer::lockForUpdate()->find($customer->id);
            $newBalance = round((float) $customer->current_balance - $amount, 2);

            $voucherNo = $this->nextVoucherNo('RC', $branchId);

            $payment = Payment::create([
                'payment_no' => $voucherNo,
                'branch_id' => $branchId,
                'direction' => 'in',
                'customer_id' => $customer->id,
                'account_id' => $accountId,
                'payment_date' => now()->toDateString(),
                'amount' => $amount,
                'method' => $method,
                'reference_no' => $reference,
                'created_by' => $userId,
            ]);

            LedgerEntry::create([
                'ledger_type' => 'customer',
                'customer_id' => $customer->id,
                'branch_id' => $branchId,
                'voucher_no' => $voucherNo,
                'voucher_type' => 'receipt',
                'transaction_date' => now()->toDateString(),
                'description' => 'Customer receipt'.($reference ? " ({$reference})" : ''),
                'debit' => 0,
                'credit' => $amount,
                'balance' => $newBalance,
                'reference_type' => Payment::class,
                'reference_id' => $payment->id,
                'created_by' => $userId,
            ]);

            $customer->update(['current_balance' => $newBalance]);

            $this->applyToAccount($accountId, $amount, 'in', $branchId, $voucherNo, $userId, 'Customer receipt');

            return $payment;
        });
    }

    /**
     * Record a payment made to a supplier (reduces payable).
     */
    public function recordSupplierPayment(
        Supplier $supplier,
        float $amount,
        ?int $accountId,
        string $method,
        ?string $reference,
        int $branchId,
        int $userId,
    ): Payment {
        return DB::transaction(function () use ($supplier, $amount, $accountId, $method, $reference, $branchId, $userId) {
            $supplier = Supplier::lockForUpdate()->find($supplier->id);
            $newBalance = round((float) $supplier->current_balance - $amount, 2);

            $voucherNo = $this->nextVoucherNo('PV', $branchId);

            $payment = Payment::create([
                'payment_no' => $voucherNo,
                'branch_id' => $branchId,
                'direction' => 'out',
                'supplier_id' => $supplier->id,
                'account_id' => $accountId,
                'payment_date' => now()->toDateString(),
                'amount' => $amount,
                'method' => $method,
                'reference_no' => $reference,
                'created_by' => $userId,
            ]);

            LedgerEntry::create([
                'ledger_type' => 'supplier',
                'supplier_id' => $supplier->id,
                'branch_id' => $branchId,
                'voucher_no' => $voucherNo,
                'voucher_type' => 'payment',
                'transaction_date' => now()->toDateString(),
                'description' => 'Supplier payment'.($reference ? " ({$reference})" : ''),
                'debit' => $amount,
                'credit' => 0,
                'balance' => $newBalance,
                'reference_type' => Payment::class,
                'reference_id' => $payment->id,
                'created_by' => $userId,
            ]);

            $supplier->update(['current_balance' => $newBalance]);

            $this->applyToAccount($accountId, $amount, 'out', $branchId, $voucherNo, $userId, 'Supplier payment');

            return $payment;
        });
    }

    /**
     * Mirror a money movement on the cash/bank account ledger.
     */
    private function applyToAccount(?int $accountId, float $amount, string $direction, int $branchId, string $voucherNo, int $userId, string $description): void
    {
        if (! $accountId) {
            return;
        }

        $account = Account::lockForUpdate()->find($accountId);
        if (! $account) {
            return;
        }

        $newBalance = round((float) $account->current_balance + ($direction === 'in' ? $amount : -$amount), 2);

        LedgerEntry::create([
            'ledger_type' => $account->type === 'bank' ? 'bank' : 'cash',
            'account_id' => $account->id,
            'branch_id' => $branchId,
            'voucher_no' => $voucherNo,
            'voucher_type' => $direction === 'in' ? 'receipt' : 'payment',
            'transaction_date' => now()->toDateString(),
            'description' => $description,
            'debit' => $direction === 'in' ? $amount : 0,
            'credit' => $direction === 'in' ? 0 : $amount,
            'balance' => $newBalance,
            'created_by' => $userId,
        ]);

        $account->update(['current_balance' => $newBalance]);
    }

    private function nextVoucherNo(string $prefix, int $branchId): string
    {
        $seq = LedgerEntry::where('voucher_no', 'like', $prefix.'-%')->count() + 1;

        return $prefix.'-'.now()->format('ymd').'-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
