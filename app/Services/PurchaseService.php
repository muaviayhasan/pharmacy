<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\LedgerEntry;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    public function __construct(private LedgerService $ledger) {}

    /**
     * Create a purchase invoice in a single transaction:
     * invoice + items, batch-wise stock-in, stock movements, supplier ledger
     * (credit purchase) and an optional supplier payment for the paid amount.
     *
     * @param  array<int, array{medicine_id:int, batch_no:string, expiry_date:string, quantity:int, bonus_quantity?:int, purchase_price:float, sale_price:float, tax_percent?:float}>  $lines
     */
    public function createPurchase(
        User $user,
        int $branchId,
        int $supplierId,
        array $meta,
        array $lines,
        float $discount,
        float $paidAmount,
        string $paymentType,
    ): Purchase {
        if (empty($lines)) {
            throw ValidationException::withMessages(['items' => 'Add at least one medicine to the purchase.']);
        }

        return DB::transaction(function () use ($user, $branchId, $supplierId, $meta, $lines, $discount, $paidAmount, $paymentType) {
            $subtotal = 0.0;
            $taxTotal = 0.0;
            $resolved = [];

            foreach ($lines as $line) {
                $medicine = Medicine::findOrFail($line['medicine_id']);
                $qty = max(1, (int) $line['quantity']);
                $bonus = max(0, (int) ($line['bonus_quantity'] ?? 0));
                $purchasePrice = (float) $line['purchase_price'];
                $salePrice = (float) $line['sale_price'];
                $taxPercent = (float) ($line['tax_percent'] ?? 0);

                if (empty($line['batch_no']) || empty($line['expiry_date'])) {
                    throw ValidationException::withMessages(['items' => "Batch number and expiry date are required for {$medicine->name}."]);
                }

                $lineBase = $purchasePrice * $qty;
                $lineTax = round($lineBase * $taxPercent / 100, 2);
                $lineTotal = round($lineBase + $lineTax, 2);

                $subtotal += $lineBase;
                $taxTotal += $lineTax;

                $resolved[] = compact('medicine', 'qty', 'bonus', 'purchasePrice', 'salePrice', 'taxPercent', 'lineTax', 'lineTotal', 'line');
            }

            $grandTotal = round($subtotal + $taxTotal - $discount, 2);
            $paid = min($paidAmount, $grandTotal);
            $due = round($grandTotal - $paid, 2);

            $purchase = Purchase::create([
                'purchase_no' => $this->nextPurchaseNo($branchId),
                'supplier_id' => $supplierId,
                'branch_id' => $branchId,
                'invoice_date' => $meta['invoice_date'] ?? now()->toDateString(),
                'supplier_invoice_no' => $meta['supplier_invoice_no'] ?? null,
                'due_date' => $meta['due_date'] ?? null,
                'subtotal' => round($subtotal, 2),
                'discount' => round($discount, 2),
                'tax' => round($taxTotal, 2),
                'grand_total' => $grandTotal,
                'paid_amount' => $paid,
                'due_amount' => $due,
                'payment_type' => $paymentType,
                'payment_status' => $due <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'),
                'status' => 'completed',
                'notes' => $meta['notes'] ?? null,
                'created_by' => $user->id,
            ]);

            foreach ($resolved as $row) {
                /** @var Medicine $medicine */
                $medicine = $row['medicine'];
                $received = $row['qty'] + $row['bonus'];

                $purchase->items()->create([
                    'medicine_id' => $medicine->id,
                    'batch_no' => $row['line']['batch_no'],
                    'expiry_date' => $row['line']['expiry_date'],
                    'quantity' => $row['qty'],
                    'bonus_quantity' => $row['bonus'],
                    'purchase_price' => $row['purchasePrice'],
                    'sale_price' => $row['salePrice'],
                    'discount' => 0,
                    'tax' => $row['lineTax'],
                    'line_total' => $row['lineTotal'],
                ]);

                $batch = MedicineBatch::lockForUpdate()->firstOrNew([
                    'medicine_id' => $medicine->id,
                    'branch_id' => $branchId,
                    'batch_no' => $row['line']['batch_no'],
                ]);

                $batch->fill([
                    'supplier_id' => $supplierId,
                    'purchase_id' => $purchase->id,
                    'expiry_date' => $row['line']['expiry_date'],
                    'purchase_price' => $row['purchasePrice'],
                    'sale_price' => $row['salePrice'],
                    'status' => 'in_stock',
                ]);
                $batch->quantity = (int) $batch->quantity + $received;
                $batch->available_quantity = (int) $batch->available_quantity + $received;
                $batch->save();

                // Keep the medicine's reference prices fresh.
                $medicine->update(['purchase_price' => $row['purchasePrice'], 'sale_price' => $row['salePrice']]);

                StockMovement::create([
                    'medicine_id' => $medicine->id,
                    'batch_id' => $batch->id,
                    'branch_id' => $branchId,
                    'movement_type' => 'purchase_in',
                    'quantity_in' => $received,
                    'quantity_out' => 0,
                    'balance_after' => $batch->available_quantity,
                    'reference_type' => Purchase::class,
                    'reference_id' => $purchase->id,
                    'reason' => 'Purchase '.$purchase->purchase_no,
                    'created_by' => $user->id,
                ]);
            }

            // Supplier ledger: credit purchase increases payable.
            $supplier = Supplier::lockForUpdate()->find($supplierId);
            $newPayable = round((float) $supplier->current_balance + $grandTotal, 2);

            LedgerEntry::create([
                'ledger_type' => 'supplier',
                'supplier_id' => $supplierId,
                'branch_id' => $branchId,
                'voucher_no' => $purchase->purchase_no,
                'voucher_type' => 'purchase',
                'transaction_date' => $purchase->invoice_date,
                'description' => 'Credit purchase '.($purchase->supplier_invoice_no ? "({$purchase->supplier_invoice_no})" : $purchase->purchase_no),
                'debit' => 0,
                'credit' => $grandTotal,
                'balance' => $newPayable,
                'reference_type' => Purchase::class,
                'reference_id' => $purchase->id,
                'created_by' => $user->id,
            ]);
            $supplier->update(['current_balance' => $newPayable]);

            // Immediate payment, if any, reduces the payable.
            if ($paid > 0) {
                $this->ledger->recordSupplierPayment(
                    $supplier->refresh(),
                    $paid,
                    null,
                    $paymentType === 'credit' ? 'cash' : $paymentType,
                    $purchase->purchase_no,
                    $branchId,
                    $user->id,
                );
            }

            return $purchase;
        });
    }

    private function nextPurchaseNo(int $branchId): string
    {
        $code = Branch::whereKey($branchId)->value('code') ?: 'BR';
        $seq = Purchase::where('branch_id', $branchId)->whereDate('created_at', today())->count() + 1;

        return 'PINV-'.strtoupper($code).'-'.now()->format('ymd').'-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
