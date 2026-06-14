<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PurchaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_adds_batch_stock_and_supplier_ledger(): void
    {
        $user = User::create(['name' => 'Buyer', 'email' => 'b@test.com', 'password' => 'x', 'status' => 'active']);
        $branch = Branch::create(['name' => 'Main', 'code' => 'M1', 'status' => 'active']);
        $supplier = Supplier::create(['name' => 'ABC', 'current_balance' => 0, 'status' => 'active']);
        $medicine = Medicine::create(['name' => 'Brufen', 'sale_price' => 0, 'purchase_price' => 0, 'status' => 'active']);

        $purchase = app(PurchaseService::class)->createPurchase(
            $user, $branch->id, $supplier->id,
            ['invoice_date' => now()->toDateString(), 'supplier_invoice_no' => 'SI-1'],
            [[
                'medicine_id' => $medicine->id, 'batch_no' => 'PB-1', 'expiry_date' => now()->addYear()->toDateString(),
                'quantity' => 100, 'bonus_quantity' => 10, 'purchase_price' => 5, 'sale_price' => 8, 'tax_percent' => 0,
            ]],
            discount: 0, paidAmount: 200, paymentType: 'cash',
        );

        // 100 + 10 bonus received into a new batch.
        $batch = MedicineBatch::where('purchase_id', $purchase->id)->first();
        $this->assertNotNull($batch);
        $this->assertEquals(110, $batch->available_quantity);

        // Grand total = 100 * 5 = 500; payable = 500 - 200 paid = 300.
        $this->assertEquals(500.0, (float) $purchase->grand_total);
        $this->assertEquals(300.0, (float) $supplier->fresh()->current_balance);

        $this->assertDatabaseHas('stock_movements', [
            'reference_type' => Purchase::class, 'reference_id' => $purchase->id, 'movement_type' => 'purchase_in', 'quantity_in' => 110,
        ]);
        $this->assertDatabaseHas('ledger_entries', [
            'ledger_type' => 'supplier', 'supplier_id' => $supplier->id, 'voucher_type' => 'purchase', 'credit' => 500,
        ]);
    }
}
