<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\LedgerEntry;
use App\Models\MedicineBatch;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseReturnService
{
    /**
     * Return stock to a supplier: deduct the batch quantities, record the
     * return and reduce the supplier payable on the ledger.
     *
     * @param  array<int, array{batch_id:int, quantity:int}>  $lines
     */
    public function process(User $user, Purchase $purchase, array $lines, string $settlement, ?string $reason): PurchaseReturn
    {
        $lines = array_values(array_filter($lines, fn ($l) => (int) ($l['quantity'] ?? 0) > 0));
        if (empty($lines)) {
            throw ValidationException::withMessages(['items' => 'Enter a return quantity for at least one batch.']);
        }

        return DB::transaction(function () use ($user, $purchase, $lines, $settlement, $reason) {
            $subtotal = 0.0;
            $resolved = [];

            foreach ($lines as $line) {
                $batch = MedicineBatch::lockForUpdate()->find((int) $line['batch_id']);
                if (! $batch) {
                    continue;
                }
                $qty = (int) $line['quantity'];
                if ($qty > $batch->available_quantity) {
                    throw ValidationException::withMessages(['items' => "Cannot return {$qty} of batch {$batch->batch_no}; only {$batch->available_quantity} available."]);
                }

                $unit = (float) $batch->purchase_price;
                $lineTotal = round($unit * $qty, 2);
                $subtotal += $lineTotal;
                $resolved[] = compact('batch', 'qty', 'unit', 'lineTotal');
            }

            $amount = round($subtotal, 2);

            $return = PurchaseReturn::create([
                'return_no' => $this->nextNo($purchase->branch_id),
                'purchase_id' => $purchase->id,
                'supplier_id' => $purchase->supplier_id,
                'branch_id' => $purchase->branch_id,
                'return_date' => now()->toDateString(),
                'settlement_method' => $settlement,
                'subtotal' => $amount,
                'return_amount' => $amount,
                'status' => 'completed',
                'reason' => $reason,
                'created_by' => $user->id,
            ]);

            foreach ($resolved as $row) {
                $batch = $row['batch'];

                $return->items()->create([
                    'medicine_id' => $batch->medicine_id,
                    'batch_id' => $batch->id,
                    'quantity' => $row['qty'],
                    'unit_price' => $row['unit'],
                    'line_total' => $row['lineTotal'],
                ]);

                $batch->decrement('available_quantity', $row['qty']);

                StockMovement::create([
                    'medicine_id' => $batch->medicine_id,
                    'batch_id' => $batch->id,
                    'branch_id' => $purchase->branch_id,
                    'movement_type' => 'purchase_return_out',
                    'quantity_in' => 0,
                    'quantity_out' => $row['qty'],
                    'balance_after' => $batch->available_quantity,
                    'reference_type' => PurchaseReturn::class,
                    'reference_id' => $return->id,
                    'reason' => 'Purchase return '.$return->return_no,
                    'created_by' => $user->id,
                ]);
            }

            // Supplier ledger: returning goods reduces the payable (debit supplier).
            if ($purchase->supplier_id) {
                $supplier = Supplier::lockForUpdate()->find($purchase->supplier_id);
                $newBalance = round((float) $supplier->current_balance - $amount, 2);

                LedgerEntry::create([
                    'ledger_type' => 'supplier',
                    'supplier_id' => $supplier->id,
                    'branch_id' => $purchase->branch_id,
                    'voucher_no' => $return->return_no,
                    'voucher_type' => 'purchase_return',
                    'transaction_date' => now()->toDateString(),
                    'description' => 'Purchase return '.$return->return_no,
                    'debit' => $amount,
                    'credit' => 0,
                    'balance' => $newBalance,
                    'reference_type' => PurchaseReturn::class,
                    'reference_id' => $return->id,
                    'created_by' => $user->id,
                ]);

                $supplier->update(['current_balance' => $newBalance]);
            }

            return $return;
        });
    }

    private function nextNo(int $branchId): string
    {
        $code = Branch::whereKey($branchId)->value('code') ?: 'BR';
        $seq = PurchaseReturn::whereDate('created_at', today())->count() + 1;

        return 'PR-'.strtoupper($code).'-'.now()->format('ymd').'-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
