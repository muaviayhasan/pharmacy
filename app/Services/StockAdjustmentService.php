<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\MedicineBatch;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockAdjustmentService
{
    /**
     * Create a pending stock adjustment with its line items. The actual stock
     * change is only applied on approval.
     *
     * @param  array<int, array{batch_id:int, counted_qty:int, reason?:string}>  $lines
     */
    public function create(User $user, int $branchId, string $type, ?string $reason, array $lines): StockAdjustment
    {
        if (empty($lines)) {
            throw ValidationException::withMessages(['items' => 'Add at least one batch to adjust.']);
        }

        return DB::transaction(function () use ($user, $branchId, $type, $reason, $lines) {
            $adjustment = StockAdjustment::create([
                'adjustment_no' => $this->nextNo($branchId),
                'branch_id' => $branchId,
                'adjustment_date' => now()->toDateString(),
                'adjustment_type' => $type,
                'reason' => $reason,
                'status' => 'pending',
                'created_by' => $user->id,
            ]);

            foreach ($lines as $line) {
                $batch = MedicineBatch::findOrFail($line['batch_id']);
                $before = (int) $batch->available_quantity;
                $after = max(0, (int) $line['counted_qty']);

                $adjustment->items()->create([
                    'medicine_id' => $batch->medicine_id,
                    'batch_id' => $batch->id,
                    'quantity_before' => $before,
                    'quantity_change' => $after - $before,
                    'quantity_after' => $after,
                    'reason' => $line['reason'] ?? null,
                ]);
            }

            return $adjustment;
        });
    }

    /**
     * Approve: apply each line to its batch and post a stock movement.
     */
    public function approve(StockAdjustment $adjustment, User $approver): void
    {
        if ($adjustment->status !== 'pending') {
            throw ValidationException::withMessages(['status' => 'Only pending adjustments can be approved.']);
        }

        DB::transaction(function () use ($adjustment, $approver) {
            foreach ($adjustment->items as $item) {
                $batch = MedicineBatch::lockForUpdate()->find($item->batch_id);
                if (! $batch) {
                    continue;
                }

                $batch->update(['available_quantity' => max(0, (int) $item->quantity_after)]);

                if ($item->quantity_change != 0) {
                    StockMovement::create([
                        'medicine_id' => $item->medicine_id,
                        'batch_id' => $batch->id,
                        'branch_id' => $adjustment->branch_id,
                        'movement_type' => $item->quantity_change > 0 ? 'adjustment_in' : 'adjustment_out',
                        'quantity_in' => $item->quantity_change > 0 ? $item->quantity_change : 0,
                        'quantity_out' => $item->quantity_change < 0 ? abs($item->quantity_change) : 0,
                        'balance_after' => $batch->available_quantity,
                        'reference_type' => StockAdjustment::class,
                        'reference_id' => $adjustment->id,
                        'reason' => 'Adjustment '.$adjustment->adjustment_no,
                        'created_by' => $approver->id,
                    ]);
                }
            }

            $adjustment->update(['status' => 'completed', 'approved_by' => $approver->id]);
        });
    }

    public function reject(StockAdjustment $adjustment, User $approver): void
    {
        $adjustment->update(['status' => 'rejected', 'approved_by' => $approver->id]);
    }

    private function nextNo(int $branchId): string
    {
        $code = Branch::whereKey($branchId)->value('code') ?: 'BR';
        $seq = StockAdjustment::where('branch_id', $branchId)->whereDate('created_at', today())->count() + 1;

        return 'ADJ-'.strtoupper($code).'-'.now()->format('ymd').'-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
