<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\ExpenseCategory;
use App\Models\Manufacturer;
use App\Models\Medicine;
use App\Models\MedicineCategory;
use App\Models\MedicineReorderSetting;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = ['Tablet', 'Syrup', 'Injection', 'Ointment', 'Capsule', 'Drops'];
        foreach ($categories as $name) {
            MedicineCategory::firstOrCreate(['name' => $name], ['status' => 'active']);
        }

        $manufacturers = ['GSK', 'Getz Pharma', 'Abbott', 'Searle', 'Highnoon'];
        foreach ($manufacturers as $name) {
            Manufacturer::firstOrCreate(['name' => $name], ['country' => 'Pakistan', 'status' => 'active']);
        }

        $expenseCategories = [
            ['name' => 'Rent', 'requires_receipt' => false],
            ['name' => 'Utilities', 'requires_receipt' => true],
            ['name' => 'Salaries', 'requires_receipt' => false],
            ['name' => 'Maintenance', 'requires_receipt' => true],
            ['name' => 'Miscellaneous', 'requires_receipt' => false],
        ];
        foreach ($expenseCategories as $c) {
            ExpenseCategory::firstOrCreate(['name' => $c['name']], $c + ['status' => 'active']);
        }

        $suppliers = [
            ['name' => 'ABC Pharma', 'contact_person' => 'Ali Khan', 'phone' => '0300-1111111', 'city' => 'Lahore', 'payment_terms' => 'Net 30'],
            ['name' => 'Helix Distribution', 'contact_person' => 'Sara Ahmed', 'phone' => '0300-2222222', 'city' => 'Karachi', 'payment_terms' => 'Net 15'],
        ];
        foreach ($suppliers as $s) {
            Supplier::firstOrCreate(['name' => $s['name']], $s + ['status' => 'active']);
        }

        Customer::firstOrCreate(
            ['name' => 'Walk-in Customer'],
            ['customer_type' => 'walk_in', 'status' => 'active']
        );
        Customer::firstOrCreate(
            ['name' => 'Regular Customer', 'phone' => '0301-3333333'],
            ['customer_type' => 'credit', 'credit_limit' => 50000, 'status' => 'active']
        );

        $catTablet = MedicineCategory::where('name', 'Tablet')->value('id');
        $catSyrup = MedicineCategory::where('name', 'Syrup')->value('id');
        $mfr = Manufacturer::first()?->id;
        $supplier = Supplier::first()?->id;

        $medicines = [
            ['name' => 'Panadol 500mg', 'generic_name' => 'Paracetamol', 'category_id' => $catTablet, 'dosage_form' => 'Tablet', 'strength' => '500', 'strength_unit' => 'mg', 'pack_size' => '10x10', 'barcode' => '8964000100011', 'purchase_price' => 1.50, 'sale_price' => 2.00, 'wholesale_price' => 1.80, 'min_stock_level' => 100, 'reorder_level' => 200, 'max_stock_level' => 1000],
            ['name' => 'Augmentin 625mg', 'generic_name' => 'Amoxicillin + Clavulanic Acid', 'category_id' => $catTablet, 'dosage_form' => 'Tablet', 'strength' => '625', 'strength_unit' => 'mg', 'pack_size' => '6 tabs', 'barcode' => '8964000100028', 'purchase_price' => 25.00, 'sale_price' => 32.00, 'wholesale_price' => 29.00, 'prescription_required' => true, 'min_stock_level' => 50, 'reorder_level' => 100, 'max_stock_level' => 500],
            ['name' => 'Brufen Syrup', 'generic_name' => 'Ibuprofen', 'category_id' => $catSyrup, 'dosage_form' => 'Syrup', 'strength' => '100', 'strength_unit' => 'ml', 'pack_size' => '90ml', 'barcode' => '8964000100035', 'purchase_price' => 60.00, 'sale_price' => 75.00, 'wholesale_price' => 68.00, 'min_stock_level' => 30, 'reorder_level' => 60, 'max_stock_level' => 300],
            ['name' => 'Lexotanil 3mg', 'generic_name' => 'Bromazepam', 'category_id' => $catTablet, 'dosage_form' => 'Tablet', 'strength' => '3', 'strength_unit' => 'mg', 'pack_size' => '30 tabs', 'barcode' => '8964000100042', 'purchase_price' => 80.00, 'sale_price' => 95.00, 'wholesale_price' => 88.00, 'prescription_required' => true, 'controlled_medicine' => true, 'min_stock_level' => 20, 'reorder_level' => 40, 'max_stock_level' => 200],
        ];

        $branchIds = Branch::pluck('id')->all();

        foreach ($medicines as $data) {
            $medicine = Medicine::firstOrCreate(
                ['name' => $data['name']],
                array_merge($data, [
                    'manufacturer_id' => $mfr,
                    'default_supplier_id' => $supplier,
                    'status' => 'active',
                ])
            );

            foreach ($branchIds as $branchId) {
                MedicineReorderSetting::firstOrCreate(
                    ['medicine_id' => $medicine->id, 'branch_id' => $branchId],
                    [
                        'preferred_supplier_id' => $supplier,
                        'min_stock' => $data['min_stock_level'],
                        'reorder_level' => $data['reorder_level'],
                        'max_stock' => $data['max_stock_level'],
                        'safety_stock' => (int) round($data['min_stock_level'] / 2),
                    ]
                );
            }
        }
    }
}
