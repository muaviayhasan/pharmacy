<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class StockSeeder extends Seeder
{
    /**
     * Seed sellable batch stock for every medicine in every branch so the POS
     * terminal has inventory to sell out of the box.
     */
    public function run(): void
    {
        $branches = Branch::all();
        $supplierId = Supplier::value('id');

        foreach (Medicine::all() as $i => $medicine) {
            foreach ($branches as $branch) {
                $batchNo = 'B-'.str_pad((string) $medicine->id, 3, '0', STR_PAD_LEFT).'-'.$branch->code;
                $expiry = now()->addMonths(8 + ($i % 12));
                $quantity = 150;

                $batch = MedicineBatch::firstOrCreate(
                    ['medicine_id' => $medicine->id, 'branch_id' => $branch->id, 'batch_no' => $batchNo],
                    [
                        'supplier_id' => $supplierId,
                        'expiry_date' => $expiry,
                        'purchase_price' => $medicine->purchase_price,
                        'sale_price' => $medicine->sale_price,
                        'quantity' => $quantity,
                        'available_quantity' => $quantity,
                        'status' => 'in_stock',
                    ]
                );

                if ($batch->wasRecentlyCreated) {
                    StockMovement::create([
                        'medicine_id' => $medicine->id,
                        'batch_id' => $batch->id,
                        'branch_id' => $branch->id,
                        'movement_type' => 'purchase_in',
                        'quantity_in' => $quantity,
                        'quantity_out' => 0,
                        'balance_after' => $quantity,
                        'reason' => 'Opening stock (seed)',
                    ]);
                }
            }
        }
    }
}
