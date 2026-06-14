<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Sale;
use App\Models\User;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SaleServiceTest extends TestCase
{
    use RefreshDatabase;

    private function scenario(int $available = 100): array
    {
        $user = User::create(['name' => 'Cashier', 'email' => 'c@test.com', 'password' => 'x', 'status' => 'active']);
        $branch = Branch::create(['name' => 'Main', 'code' => 'M1', 'status' => 'active']);
        $medicine = Medicine::create(['name' => 'Panadol', 'sale_price' => 10, 'purchase_price' => 6, 'tax_percent' => 0, 'max_discount_percent' => 50, 'status' => 'active']);
        $batch = MedicineBatch::create([
            'medicine_id' => $medicine->id, 'branch_id' => $branch->id, 'batch_no' => 'B1',
            'expiry_date' => now()->addYear(), 'purchase_price' => 6, 'sale_price' => 10,
            'quantity' => $available, 'available_quantity' => $available, 'status' => 'in_stock',
        ]);

        return [$user, $branch, $medicine, $batch];
    }

    public function test_cash_sale_deducts_stock_and_records_movement(): void
    {
        [$user, $branch, $medicine, $batch] = $this->scenario(100);
        $svc = app(SaleService::class);
        $shift = $svc->ensureOpenShift($user, $branch->id);

        $sale = $svc->completeSale($user, $branch->id, $shift, null, 'cash', 1000, [
            ['batch_id' => $batch->id, 'quantity' => 3, 'discount_percent' => 0],
        ]);

        $this->assertEquals(30.0, (float) $sale->grand_total);
        $this->assertEquals('paid', $sale->payment_status);
        $this->assertEquals(97, $batch->fresh()->available_quantity);
        $this->assertDatabaseHas('stock_movements', [
            'reference_type' => Sale::class, 'reference_id' => $sale->id, 'movement_type' => 'sale_out', 'quantity_out' => 3,
        ]);
        $this->assertEquals(30.0, (float) $shift->fresh()->cash_sales);
    }

    public function test_credit_sale_posts_customer_ledger(): void
    {
        [$user, $branch, $medicine, $batch] = $this->scenario(50);
        $customer = Customer::create(['name' => 'Acme', 'customer_type' => 'credit', 'current_balance' => 0, 'status' => 'active']);
        $svc = app(SaleService::class);
        $shift = $svc->ensureOpenShift($user, $branch->id);

        $sale = $svc->completeSale($user, $branch->id, $shift, $customer->id, 'credit', 0, [
            ['batch_id' => $batch->id, 'quantity' => 2, 'discount_percent' => 0],
        ]);

        $this->assertEquals('unpaid', $sale->payment_status);
        $this->assertEquals(20.0, (float) $customer->fresh()->current_balance);
        $this->assertDatabaseHas('ledger_entries', [
            'ledger_type' => 'customer', 'customer_id' => $customer->id, 'debit' => 20, 'voucher_type' => 'sale',
        ]);
    }

    public function test_sale_rejects_insufficient_stock(): void
    {
        [$user, $branch, $medicine, $batch] = $this->scenario(2);
        $svc = app(SaleService::class);
        $shift = $svc->ensureOpenShift($user, $branch->id);

        $this->expectException(ValidationException::class);
        $svc->completeSale($user, $branch->id, $shift, null, 'cash', 1000, [
            ['batch_id' => $batch->id, 'quantity' => 5, 'discount_percent' => 0],
        ]);
    }

    public function test_cash_sale_requires_sufficient_cash(): void
    {
        [$user, $branch, $medicine, $batch] = $this->scenario(50);
        $svc = app(SaleService::class);
        $shift = $svc->ensureOpenShift($user, $branch->id);

        $this->expectException(ValidationException::class);
        $svc->completeSale($user, $branch->id, $shift, null, 'cash', 5, [
            ['batch_id' => $batch->id, 'quantity' => 3, 'discount_percent' => 0],
        ]);
    }
}
