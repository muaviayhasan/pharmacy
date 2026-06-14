<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicine_reorder_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained('medicines')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('preferred_supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->integer('min_stock')->default(0);
            $table->integer('reorder_level')->default(0);
            $table->integer('max_stock')->default(0);
            $table->integer('safety_stock')->default(0);
            $table->decimal('average_daily_sale', 12, 2)->default(0);
            $table->integer('suggested_reorder_qty')->default(0);
            $table->timestamps();
            $table->unique(['medicine_id', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicine_reorder_settings');
    }
};
