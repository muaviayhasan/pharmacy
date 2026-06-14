<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\LedgerEntry;
use App\Models\MedicineBatch;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleReturnService
{
    /**
     * Process a customer sale return: restock batches, record the return and
     * (for ledger refunds) credit the customer ledger.
     *
     * @param  array<int, array{sale_item_id:int, quantity:int, restock?:bool}>  $lines
     */
    public function process(User $user, Sale $sale, array $lines, string $refundMethod, ?string $reason): SaleReturn
    {
        $lines = array_values(array_filter($lines, fn ($l) => (int) ($l['quantity'] ?? 0) > 0));
        if (empty($lines)) {
            throw ValidationException::withMessages(['items' => 'Enter a return quantity for at least one item.']);
        }

        return DB::transaction(function () use ($user, $sale, $lines, $refundMethod, $reason) {
            $items = $sale->items()->get()->keyBy('id');
            $subtotal = 0.0;
            $resolved = [];

            foreach ($lines as $line) {
                $item = $items->get((int) $line['sale_item_id']);
                if (! $item) {
                    continue;
                }
                $qty = (int) $line['quantity'];
                $remaining = $item->quantity - $item->returned_quantity;

                if ($qty > $remaining) {
                    throw ValidationException::withMessages(['items' => "Cannot return {$qty} of {$item->medicine?->name}; only {$remaining} remaining."]);
                }

                $unit = $item->quantity > 0 ? ($item->line_total / $item->quantity) : 0;
                $lineTotal = round($unit * $qty, 2);
                $subtotal += $lineTotal;

                $resolved[] = [
                    'item' => $item,
                    'qty' => $qty,
                    'unit_price' => (float) $item->unit_price,
                    'line_total' => $lineTotal,
                    'restock' => (bool) ($line['restock'] ?? true),
                ];
            }

            $refund = round($subtotal, 2);

            $return = SaleReturn::create([
                'return_no' => $this->nextNo($sale->branch_id),
                'sale_id' => $sale->id,
                'customer_id' => $sale->customer_id,
                'branch_id' => $sale->branch_id,
                'return_date' => now()->toDateString(),
                'refund_method' => $refundMethod,
                'subtotal' => $refund,
                'refund_amount' => $refund,
                'status' => 'completed',
                'reason' => $reason,
                'created_by' => $user->id,
            ]);

            foreach ($resolved as $row) {
                $item = $row['item'];

                $return->items()->create([
                    'sale_item_id' => $item->id,
                    'medicine_id' => $item->medicine_id,
                    'batch_id' => $item->batch_id,
                    'quantity' => $row['qty'],
                    'unit_price' => $row['unit_price'],
                    'line_total' => $row['line_total'],
                    'restock' => $row['restock'],
                ]);

                $item->increment('returned_quantity', $row['qty']);

                if ($row['restock'] && $item->batch_id) {
                    $batch = MedicineBatch::lockForUpdate()->find($item->batch_id);
                    if ($batch) {
                        $batch->increment('available_quantity', $row['qty']);
                        StockMovement::create([
                            'medicine_id' => $item->medicine_id,
                            'batch_id' => $batch->id,
                            'branch_id' => $sale->branch_id,
                            'movement_type' => 'sale_return_in',
                            'quantity_in' => $row['qty'],
                            'quantity_out' => 0,
                            'balance_after' => $batch->available_quantity,
                            'reference_type' => SaleReturn::class,
                            'reference_id' => $return->id,
                            'reason' => 'Sale return '.$return->return_no,
                            'created_by' => $user->id,
                        ]);
                    }
                }
            }

            // Refund handling.
            if ($refundMethod === 'ledger_credit' && $sale->customer_id) {
                $customer = Customer::lockForUpdate()->find($sale->customer_id);
                $newBalance = round((float) $customer->current_balance - $refund, 2);

                LedgerEntry::create([
                    'ledger_type' => 'customer',
                    'customer_id' => $customer->id,
                    'branch_id' => $sale->branch_id,
                    'voucher_no' => $return->return_no,
                    'voucher_type' => 'sale_return',
                    'transaction_date' => now()->toDateString(),
                    'description' => 'Sale return '.$return->return_no,
                    'debit' => 0,
                    'credit' => $refund,
                    'balance' => $newBalance,
                    'reference_type' => SaleReturn::class,
                    'reference_id' => $return->id,
                    'created_by' => $user->id,
                ]);

                $customer->update(['current_balance' => $newBalance]);
            }

            // Update sale return status.
            $fullyReturned = $sale->items()->get()->every(fn ($i) => $i->returned_quantity >= $i->quantity);
            $sale->update(['return_status' => $fullyReturned ? 'returned' : 'partial']);

            return $return;
        });
    }

    private function nextNo(int $branchId): string
    {
        $code = Branch::whereKey($branchId)->value('code') ?: 'BR';
        $seq = SaleReturn::whereDate('created_at', today())->count() + 1;

        return 'SR-'.strtoupper($code).'-'.now()->format('ymd').'-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
