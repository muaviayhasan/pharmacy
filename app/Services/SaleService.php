<?php

namespace App\Services;

use App\Events\SaleCompleted;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\LedgerEntry;
use App\Models\MedicineBatch;
use App\Models\PosShift;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    /**
     * Find the cashier's open shift for the branch, opening one automatically
     * if none exists so the terminal is immediately usable.
     */
    public function ensureOpenShift(User $user, int $branchId): PosShift
    {
        $shift = PosShift::where('cashier_id', $user->id)
            ->where('branch_id', $branchId)
            ->where('status', 'open')
            ->latest('opened_at')
            ->first();

        if ($shift) {
            return $shift;
        }

        return PosShift::create([
            'shift_no' => $this->nextShiftNo($branchId),
            'branch_id' => $branchId,
            'cashier_id' => $user->id,
            'opening_cash' => 0,
            'opened_at' => now(),
            'status' => 'open',
        ]);
    }

    /**
     * Complete a POS sale inside a single transaction:
     * stock deduction, sale + items, stock movements, shift totals and
     * (for credit sales) customer ledger posting.
     *
     * @param  array<int, array{batch_id:int, quantity:int, discount_percent?:float}>  $lines
     */
    public function completeSale(
        User $user,
        int $branchId,
        PosShift $shift,
        ?int $customerId,
        string $paymentMethod,
        float $cashReceived,
        array $lines,
    ): Sale {
        if (empty($lines)) {
            throw ValidationException::withMessages(['cart' => 'The cart is empty.']);
        }

        if ($paymentMethod === 'credit' && ! $customerId) {
            throw ValidationException::withMessages(['payment' => 'A registered customer is required for credit sales.']);
        }

        return DB::transaction(function () use ($user, $branchId, $shift, $customerId, $paymentMethod, $cashReceived, $lines) {
            $subtotal = 0.0;
            $discountTotal = 0.0;
            $taxTotal = 0.0;
            $prescriptionRequired = false;
            $resolved = [];

            foreach ($lines as $line) {
                /** @var MedicineBatch $batch */
                $batch = MedicineBatch::with('medicine')->lockForUpdate()->findOrFail($line['batch_id']);
                $qty = max(1, (int) $line['quantity']);

                if ($batch->isExpired() || $batch->status !== 'in_stock') {
                    throw ValidationException::withMessages(['cart' => "{$batch->medicine->name} batch {$batch->batch_no} is expired or unavailable."]);
                }

                if ($batch->available_quantity < $qty) {
                    throw ValidationException::withMessages(['cart' => "Only {$batch->available_quantity} units available for {$batch->medicine->name} (batch {$batch->batch_no})."]);
                }

                $unitPrice = (float) ($batch->sale_price ?: $batch->medicine->sale_price);
                $maxDiscount = (float) $batch->medicine->max_discount_percent;
                $discountPercent = min((float) ($line['discount_percent'] ?? 0), $maxDiscount ?: 100);
                $taxPercent = (float) $batch->medicine->tax_percent;

                $lineSubtotal = $unitPrice * $qty;
                $lineDiscount = round($lineSubtotal * $discountPercent / 100, 2);
                $taxable = $lineSubtotal - $lineDiscount;
                $lineTax = round($taxable * $taxPercent / 100, 2);
                $lineTotal = round($taxable + $lineTax, 2);

                $subtotal += $lineSubtotal;
                $discountTotal += $lineDiscount;
                $taxTotal += $lineTax;
                $prescriptionRequired = $prescriptionRequired || $batch->medicine->prescription_required;

                $resolved[] = compact('batch', 'qty', 'unitPrice', 'lineDiscount', 'lineTax', 'lineTotal');
            }

            $grandTotal = round($subtotal - $discountTotal + $taxTotal, 2);

            if ($paymentMethod === 'cash' && $cashReceived < $grandTotal) {
                throw ValidationException::withMessages([
                    'payment' => 'Cash received is less than the total amount due.',
                ]);
            }

            $isCredit = $paymentMethod === 'credit';
            $paidAmount = $isCredit ? 0.0 : $grandTotal;
            $dueAmount = round($grandTotal - $paidAmount, 2);

            $sale = Sale::create([
                'sale_no' => $this->nextSaleNo($branchId),
                'branch_id' => $branchId,
                'pos_counter_id' => $shift->pos_counter_id,
                'shift_id' => $shift->id,
                'customer_id' => $customerId,
                'sale_date' => now(),
                'subtotal' => round($subtotal, 2),
                'discount' => round($discountTotal, 2),
                'tax' => round($taxTotal, 2),
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'payment_method' => $paymentMethod,
                'payment_status' => $isCredit ? 'unpaid' : 'paid',
                'invoice_status' => 'completed',
                'prescription_status' => $prescriptionRequired ? 'pending' : 'not_required',
                'created_by' => $user->id,
            ]);

            foreach ($resolved as $row) {
                /** @var MedicineBatch $batch */
                $batch = $row['batch'];

                $sale->items()->create([
                    'medicine_id' => $batch->medicine_id,
                    'batch_id' => $batch->id,
                    'batch_no' => $batch->batch_no,
                    'expiry_date' => $batch->expiry_date,
                    'quantity' => $row['qty'],
                    'unit_price' => $row['unitPrice'],
                    'discount' => $row['lineDiscount'],
                    'tax' => $row['lineTax'],
                    'line_total' => $row['lineTotal'],
                ]);

                $batch->decrement('available_quantity', $row['qty']);

                StockMovement::create([
                    'medicine_id' => $batch->medicine_id,
                    'batch_id' => $batch->id,
                    'branch_id' => $branchId,
                    'movement_type' => 'sale_out',
                    'quantity_in' => 0,
                    'quantity_out' => $row['qty'],
                    'balance_after' => $batch->available_quantity,
                    'reference_type' => Sale::class,
                    'reference_id' => $sale->id,
                    'reason' => 'POS sale '.$sale->sale_no,
                    'created_by' => $user->id,
                ]);
            }

            $this->applyToShift($shift, $paymentMethod, $grandTotal);

            if ($isCredit && $customerId) {
                $this->postCustomerLedger($sale, $customerId, $grandTotal, $branchId, $user->id);
            }

            $sale->load('creator', 'items');
            SaleCompleted::dispatch($sale);

            \App\Support\Audit::log('sales', 'create', "Sale {$sale->sale_no} for Rs. ".number_format($grandTotal, 2), [
                'reference' => $sale,
                'risk' => $isCredit ? 'medium' : 'low',
            ]);

            return $sale;
        });
    }

    private function applyToShift(PosShift $shift, string $method, float $amount): void
    {
        $column = match ($method) {
            'cash' => 'cash_sales',
            'card' => 'card_sales',
            'bank' => 'bank_sales',
            'credit' => 'credit_sales',
            default => 'cash_sales',
        };

        $shift->increment($column, $amount);
        $shift->refresh();
        $shift->update([
            'expected_cash' => round($shift->opening_cash + $shift->cash_sales - $shift->refunds - $shift->expenses, 2),
        ]);
    }

    private function postCustomerLedger(Sale $sale, int $customerId, float $amount, int $branchId, int $userId): void
    {
        $customer = Customer::lockForUpdate()->find($customerId);
        $balance = round(((float) $customer->current_balance) + $amount, 2);

        LedgerEntry::create([
            'ledger_type' => 'customer',
            'customer_id' => $customerId,
            'branch_id' => $branchId,
            'voucher_no' => $sale->sale_no,
            'voucher_type' => 'sale',
            'transaction_date' => now()->toDateString(),
            'description' => 'Credit sale '.$sale->sale_no,
            'debit' => $amount,
            'credit' => 0,
            'balance' => $balance,
            'reference_type' => Sale::class,
            'reference_id' => $sale->id,
            'created_by' => $userId,
        ]);

        $customer->update(['current_balance' => $balance]);
    }

    private function nextSaleNo(int $branchId): string
    {
        $code = Branch::whereKey($branchId)->value('code') ?: 'BR';
        $seq = Sale::where('branch_id', $branchId)->whereDate('created_at', today())->count() + 1;

        return 'SL-'.strtoupper($code).'-'.now()->format('ymd').'-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    private function nextShiftNo(int $branchId): string
    {
        $code = Branch::whereKey($branchId)->value('code') ?: 'BR';
        $seq = PosShift::where('branch_id', $branchId)->whereDate('created_at', today())->count() + 1;

        return 'SH-'.strtoupper($code).'-'.now()->format('ymd').'-'.str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
    }
}
