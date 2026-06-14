<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('generic_name')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('medicine_categories')->nullOnDelete();
            $table->foreignId('manufacturer_id')->nullable()->constrained('manufacturers')->nullOnDelete();
            $table->foreignId('default_supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('dosage_form')->nullable(); // tablet, syrup, injection, ointment
            $table->string('strength')->nullable();    // e.g. 500mg
            $table->string('strength_unit')->nullable(); // mg, ml, mcg
            $table->string('pack_size')->nullable();
            $table->string('barcode')->nullable()->unique();
            $table->string('rack_shelf')->nullable();
            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->decimal('sale_price', 12, 2)->default(0); // MRP
            $table->decimal('wholesale_price', 12, 2)->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('max_discount_percent', 5, 2)->default(0);
            $table->integer('min_stock_level')->default(0);
            $table->integer('reorder_level')->default(0);
            $table->integer('max_stock_level')->default(0);
            $table->boolean('prescription_required')->default(false);
            $table->boolean('controlled_medicine')->default(false);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
