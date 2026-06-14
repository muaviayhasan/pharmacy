<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->string('alert_no')->unique();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('module'); // inventory, expiry, ledger, shift, security, prescription, approval
            $table->string('alert_type'); // low_stock, out_of_stock, near_expiry, expired, payment_due, cash_shortage, etc.
            $table->string('title');
            $table->text('message')->nullable();
            $table->string('priority')->default('medium'); // low, medium, high, critical
            $table->string('status')->default('unread'); // unread, read, pending, in_progress, resolved, dismissed, escalated
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->nullableMorphs('reference'); // reference_type + reference_id
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
