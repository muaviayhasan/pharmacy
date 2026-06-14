<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Branch;
use App\Models\Expense;
use App\Models\LedgerEntry;
use App\Models\PosShift;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExpenseService
{
    /**
     * Record a new expense voucher (pending approval).
     *
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data): Expense
    {
        $amount = (float) $data['amount'];
        $tax = (float) ($data['tax'] ?? 0);

        return Expense::create([
            'expense_no' => $this->nextNo((int) $data['branch_id']),
            'branch_id' => $data['branch_id'],
            'category_id' => $data['category_id'] ?? null,
            'expense_date' => $data['expense_date'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'amount' => $amount,
            'tax' => $tax,
            'total_amount' => round($amount + $tax, 2),
            'payment_method' => $data['payment_method'] ?? 'cash',
            'payment_account_id' => $data['payment_account_id'] ?? null,
            'related_shift_id' => $data['related_shift_id'] ?? null,
            'status' => 'active',
            'approval_status' => 'pending',
            'created_by' => $user->id,
        ]);
    }

    /**
     * Approve an expense: post it to the expense ledger, reduce the paying
     * account, and (if linked) add it to the POS shift's expense total.
     */
    public function approve(Expense $expense, User $approver): void
    {
        if ($expense->approval_status !== 'pending') {
            throw ValidationException::withMessages(['status' => 'Only pending expenses can be approved.']);
        }

        DB::transaction(function () use ($expense, $approver) {
            $total = (float) $expense->total_amount;

            LedgerEntry::create([
                'ledger_type' => 'expense',
                'account_id' => $expense->payment_account_id,
                'branch_id' => $expense->branch_id,
                'voucher_no' => $expense->expense_no,
                'voucher_type' => 'expense',
                'transaction_date' => $expense->expense_date,
                'description' => $expense->title,
                'debit' => $total,
                'credit' => 0,
                'balance' => $total,
                'reference_type' => Expense::class,
                'reference_id' => $expense->id,
                'created_by' => $approver->id,
            ]);

            if ($expense->payment_account_id) {
                $account = Account::lockForUpdate()->find($expense->payment_account_id);
                if ($account) {
                    $newBalance = round((float) $account->current_balance - $total, 2);

                    LedgerEntry::create([
                        'ledger_type' => $account->type === 'bank' ? 'bank' : 'cash',
                        'account_id' => $account->id,
                        'branch_id' => $expense->branch_id,
                        'voucher_no' => $expense->expense_no,
                        'voucher_type' => 'expense',
                        'transaction_date' => $expense->expense_date,
                        'description' => 'Expense: '.$expense->title,
                        'debit' => 0,
                        'credit' => $total,
                        'balance' => $newBalance,
                        'reference_type' => Expense::class,
                        'reference_id' => $expense->id,
                        'created_by' => $approver->id,
                    ]);

                    $account->update(['current_balance' => $newBalance]);
                }
            }

            if ($expense->related_shift_id) {
                $shift = PosShift::find($expense->related_shift_id);
                if ($shift && $shift->status === 'open') {
                    $shift->increment('expenses', $total);
                    $shift->update(['expected_cash' => round($shift->opening_cash + $shift->cash_sales - $shift->refunds - $shift->expenses, 2)]);
                }
            }

            $expense->update(['approval_status' => 'approved', 'approved_by' => $approver->id]);
        });
    }

    public function reject(Expense $expense, User $approver): void
    {
        $expense->update(['approval_status' => 'rejected', 'approved_by' => $approver->id]);
    }

    private function nextNo(int $branchId): string
    {
        $code = Branch::whereKey($branchId)->value('code') ?: 'BR';
        $seq = Expense::where('branch_id', $branchId)->whereDate('created_at', today())->count() + 1;

        return 'EXP-'.strtoupper($code).'-'.now()->format('ymd').'-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
