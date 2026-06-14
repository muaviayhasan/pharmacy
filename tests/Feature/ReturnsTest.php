<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PurchaseReturnService;
use App\Services\PurchaseService;
use App\Services\SaleReturnService;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReturnsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_return_restocks_batch(): void
    {
        $user = User::create(['name' => 'C', 'email' => 'c@test.com', 'password' => 'x', 'status' => 'active']);
        $branch = Branch::create(['name' => 'Main', 'code' => 'M1', 'status' => 'active']);
        $medicine = Medicine::create(['name' => 'Med', 'sale_price' => 10, 'purchase_price' => 6, 'status' => 'active']);
        $batch = MedicineBatch::create(['medicine_id' => $medicine->id, 'branch_id' => $branch->id, 'batch_no' => 'B1', 'expiry_date' => now()->addYear(), 'purchase_price' => 6, 'sale_price' => 10, 'quantity' => 100, 'available_quantity' => 100, 'status' => 'in_stock']);

        $sales = app(SaleService::class);
        $shift = $sales->ensureOpenShift($user, $branch->id);
        $sale = $sales->completeSale($user, $branch->id, $shift, null, 'cash', 1000, [['batch_id' => $batch->id, 'quantity' => 5]]);
        $this->assertEquals(95, $batch->fresh()->available_quantity);

        $item = $sale->items()->first();
        app(SaleReturnService::class)->process($user, $sale, [['sale_item_id' => $item->id, 'quantity' => 2, 'restock' => true]], 'cash', 'damaged');

        $this->assertEquals(97, $batch->fresh()->available_quantity);
        $this->assertEquals(2, $item->fresh()->returned_quantity);
        $this->assertEquals('partial', $sale->fresh()->return_status);
    }

    public function test_purchase_return_deducts_stock_and_reduces_payable(): void
    {
        $user = User::create(['name' => 'B', 'email' => 'b@test.com', 'password' => 'x', 'status' => 'active']);
        $branch = Branch::create(['name' => 'Main', 'code' => 'M1', 'status' => 'active']);
        $supplier = Supplier::create(['name' => 'ABC', 'current_balance' => 0, 'status' => 'active']);
        $medicine = Medicine::create(['name' => 'Med', 'sale_price' => 0, 'purchase_price' => 0, 'status' => 'active']);

        $purchase = app(PurchaseService::class)->createPurchase(
            $user, $branch->id, $supplier->id, ['invoice_date' => now()->toDateString()],
            [['medicine_id' => $medicine->id, 'batch_no' => 'PB1', 'expiry_date' => now()->addYear()->toDateString(), 'quantity' => 100, 'bonus_quantity' => 0, 'purchase_price' => 5, 'sale_price' => 8]],
            discount: 0, paidAmount: 0, paymentType: 'credit',
        );
        $batch = MedicineBatch::where('purchase_id', $purchase->id)->first();
        $this->assertEquals(500.0, (float) $supplier->fresh()->current_balance);

        app(PurchaseReturnService::class)->process($user, $purchase, [['batch_id' => $batch->id, 'quantity' => 10]], 'ledger_adjust', 'expired');

        $this->assertEquals(90, $batch->fresh()->available_quantity);
        $this->assertEquals(450.0, (float) $supplier->fresh()->current_balance); // 500 - (10*5)
    }
}
