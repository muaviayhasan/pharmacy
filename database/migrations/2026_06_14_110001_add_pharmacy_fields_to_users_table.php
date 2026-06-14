<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('status')->default('active')->after('phone'); // active, inactive, blocked
            $table->boolean('two_factor_enabled')->default(false)->after('status');
            $table->foreignId('default_branch_id')->nullable()->after('two_factor_enabled');
            $table->string('avatar_path')->nullable()->after('default_branch_id');
            $table->timestamp('last_login_at')->nullable()->after('avatar_path');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'status', 'two_factor_enabled', 'default_branch_id',
                'avatar_path', 'last_login_at', 'last_login_ip', 'deleted_at',
            ]);
        });
    }
};
