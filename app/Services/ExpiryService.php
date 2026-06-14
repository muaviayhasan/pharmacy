<?php

namespace App\Services;

use App\Models\MedicineBatch;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ExpiryService
{
    /**
     * Dispose a batch: zero its available stock and post an expired-out movement.
     */
    public function dispose(MedicineBatch $batch, User $user): void
    {
        DB::transaction(function () use ($batch, $user) {
            $qty = (int) $batch->available_quantity;

            $batch->update(['available_quantity' => 0, 'status' => 'disposed']);

            StockMovement::create([
                'medicine_id' => $batch->medicine_id,
                'batch_id' => $batch->id,
                'branch_id' => $batch->branch_id,
                'movement_type' => 'expired_out',
                'quantity_in' => 0,
                'quantity_out' => $qty,
                'balance_after' => 0,
                'reference_type' => MedicineBatch::class,
                'reference_id' => $batch->id,
                'reason' => 'Disposed expired batch '.$batch->batch_no,
                'created_by' => $user->id,
            ]);
        });
    }

    /**
     * Quarantine a batch: removes it from sellable stock (FEFO scope requires
     * status = in_stock) while keeping the physical quantity for audit.
     */
    public function quarantine(MedicineBatch $batch, User $user): void
    {
        DB::transaction(function () use ($batch, $user) {
            $batch->update(['status' => 'quarantined']);

            StockMovement::create([
                'medicine_id' => $batch->medicine_id,
                'batch_id' => $batch->id,
                'branch_id' => $batch->branch_id,
                'movement_type' => 'quarantine',
                'quantity_in' => 0,
                'quantity_out' => 0,
                'balance_after' => $batch->available_quantity,
                'reference_type' => MedicineBatch::class,
                'reference_id' => $batch->id,
                'reason' => 'Quarantined batch '.$batch->batch_no,
                'created_by' => $user->id,
            ]);
        });
    }

    /**
     * Restore a quarantined batch back to sellable stock.
     */
    public function restore(MedicineBatch $batch): void
    {
        $batch->update(['status' => 'in_stock']);
    }
}
