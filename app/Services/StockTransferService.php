<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\MedicineBatch;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockTransferService
{
    /**
     * Create a pending transfer with its line items.
     *
     * @param  array<int, array{batch_id:int, quantity:int}>  $lines
     */
    public function create(User $user, int $fromBranchId, int $toBranchId, ?string $reason, string $priority, array $lines): StockTransfer
    {
        if ($fromBranchId === $toBranchId) {
            throw ValidationException::withMessages(['to_branch' => 'Source and destination branches must be different.']);
        }
        if (empty($lines)) {
            throw ValidationException::withMessages(['items' => 'Add at least one batch to transfer.']);
        }

        return DB::transaction(function () use ($user, $fromBranchId, $toBranchId, $reason, $priority, $lines) {
            $transfer = StockTransfer::create([
                'transfer_no' => $this->nextNo($fromBranchId),
                'from_branch_id' => $fromBranchId,
                'to_branch_id' => $toBranchId,
                'transfer_date' => now()->toDateString(),
                'status' => 'pending',
                'requested_by' => $user->id,
                'notes' => trim(($priority ? "[{$priority}] " : '').($reason ?? '')) ?: null,
            ]);

            foreach ($lines as $line) {
                $batch = MedicineBatch::findOrFail($line['batch_id']);
                $qty = (int) $line['quantity'];

                if ($qty < 1) {
                    throw ValidationException::withMessages(['items' => "Quantity must be at least 1 for {$batch->batch_no}."]);
                }
                if ($qty > $batch->available_quantity) {
                    throw ValidationException::withMessages(['items' => "Only {$batch->available_quantity} available in batch {$batch->batch_no}."]);
                }

                $transfer->items()->create([
                    'medicine_id' => $batch->medicine_id,
                    'batch_id' => $batch->id,
                    'batch_no' => $batch->batch_no,
                    'expiry_date' => $batch->expiry_date,
                    'quantity' => $qty,
                ]);
            }

            return $transfer;
        });
    }

    /**
     * Dispatch: deduct from the source branch batches.
     */
    public function dispatch(StockTransfer $transfer, User $user): void
    {
        if ($transfer->status !== 'pending') {
            throw ValidationException::withMessages(['status' => 'Only pending transfers can be dispatched.']);
        }

        DB::transaction(function () use ($transfer, $user) {
            foreach ($transfer->items as $item) {
                $batch = MedicineBatch::lockForUpdate()->find($item->batch_id);
                if (! $batch || $batch->available_quantity < $item->quantity) {
                    throw ValidationException::withMessages(['items' => "Insufficient stock to dispatch batch {$item->batch_no}."]);
                }

                $batch->decrement('available_quantity', $item->quantity);

                StockMovement::create([
                    'medicine_id' => $item->medicine_id,
                    'batch_id' => $batch->id,
                    'branch_id' => $transfer->from_branch_id,
                    'movement_type' => 'transfer_out',
                    'quantity_in' => 0,
                    'quantity_out' => $item->quantity,
                    'balance_after' => $batch->available_quantity,
                    'reference_type' => StockTransfer::class,
                    'reference_id' => $transfer->id,
                    'reason' => 'Transfer '.$transfer->transfer_no,
                    'created_by' => $user->id,
                ]);
            }

            $transfer->update(['status' => 'dispatched', 'approved_by' => $user->id, 'dispatched_by' => $user->id]);
        });
    }

    /**
     * Receive: add the stock into the destination branch (new/merged batch).
     */
    public function receive(StockTransfer $transfer, User $user): void
    {
        if ($transfer->status !== 'dispatched') {
            throw ValidationException::withMessages(['status' => 'Only dispatched transfers can be received.']);
        }

        DB::transaction(function () use ($transfer, $user) {
            foreach ($transfer->items as $item) {
                $source = MedicineBatch::find($item->batch_id);

                $dest = MedicineBatch::lockForUpdate()->firstOrNew([
                    'medicine_id' => $item->medicine_id,
                    'branch_id' => $transfer->to_branch_id,
                    'batch_no' => $item->batch_no,
                ]);
                $dest->fill([
                    'supplier_id' => $source?->supplier_id,
                    'expiry_date' => $item->expiry_date,
                    'purchase_price' => $source?->purchase_price ?? 0,
                    'sale_price' => $source?->sale_price ?? 0,
                    'status' => 'in_stock',
                ]);
                $dest->quantity = (int) $dest->quantity + $item->quantity;
                $dest->available_quantity = (int) $dest->available_quantity + $item->quantity;
                $dest->save();

                $item->update(['received_quantity' => $item->quantity]);

                StockMovement::create([
                    'medicine_id' => $item->medicine_id,
                    'batch_id' => $dest->id,
                    'branch_id' => $transfer->to_branch_id,
                    'movement_type' => 'transfer_in',
                    'quantity_in' => $item->quantity,
                    'quantity_out' => 0,
                    'balance_after' => $dest->available_quantity,
                    'reference_type' => StockTransfer::class,
                    'reference_id' => $transfer->id,
                    'reason' => 'Transfer '.$transfer->transfer_no,
                    'created_by' => $user->id,
                ]);
            }

            $transfer->update(['status' => 'received', 'received_by' => $user->id]);
        });
    }

    private function nextNo(int $branchId): string
    {
        $code = Branch::whereKey($branchId)->value('code') ?: 'BR';
        $seq = StockTransfer::whereDate('created_at', today())->count() + 1;

        return 'TRF-'.strtoupper($code).'-'.now()->format('ymd').'-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
