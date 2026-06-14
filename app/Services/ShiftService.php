<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\PosShift;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShiftService
{
    /**
     * Open a new POS shift for the cashier. Only one open shift per
     * cashier + branch is allowed at a time.
     */
    public function open(User $user, int $branchId, ?int $counterId, float $openingCash, ?string $notes = null): PosShift
    {
        $existing = PosShift::where('cashier_id', $user->id)
            ->where('branch_id', $branchId)
            ->where('status', 'open')
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'shift' => "You already have an open shift ({$existing->shift_no}). Close it before opening a new one.",
            ]);
        }

        return PosShift::create([
            'shift_no' => $this->nextNo($branchId),
            'branch_id' => $branchId,
            'pos_counter_id' => $counterId,
            'cashier_id' => $user->id,
            'opening_cash' => $openingCash,
            'opened_at' => now(),
            'expected_cash' => $openingCash,
            'status' => 'open',
            'notes' => $notes,
        ]);
    }

    /**
     * Close a shift: compute expected cash from sales, capture the counted
     * cash and the shortage/excess, and send it for manager approval.
     */
    public function close(PosShift $shift, float $countedCash, ?string $notes = null): PosShift
    {
        if ($shift->status !== 'open') {
            throw ValidationException::withMessages(['shift' => 'Only an open shift can be closed.']);
        }

        return DB::transaction(function () use ($shift, $countedCash, $notes) {
            $expected = round($shift->opening_cash + $shift->cash_sales - $shift->refunds - $shift->expenses, 2);

            $shift->update([
                'closed_at' => now(),
                'expected_cash' => $expected,
                'counted_cash' => $countedCash,
                'cash_difference' => round($countedCash - $expected, 2),
                'status' => 'pending_approval',
                'notes' => $notes ?? $shift->notes,
            ]);

            return $shift->refresh();
        });
    }

    public function approve(PosShift $shift, User $approver): void
    {
        if ($shift->status !== 'pending_approval') {
            throw ValidationException::withMessages(['shift' => 'Only a closed shift awaiting approval can be approved.']);
        }

        $shift->update(['status' => 'approved', 'approved_by' => $approver->id]);
    }

    public function reject(PosShift $shift, User $approver): void
    {
        $shift->update(['status' => 'rejected', 'approved_by' => $approver->id]);
    }

    private function nextNo(int $branchId): string
    {
        $code = Branch::whereKey($branchId)->value('code') ?: 'BR';
        $seq = PosShift::where('branch_id', $branchId)->whereDate('created_at', today())->count() + 1;

        return 'SH-'.strtoupper($code).'-'.now()->format('ymd').'-'.str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
    }
}
