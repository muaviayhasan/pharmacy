<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use App\Services\ShiftService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ShiftServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_open_close_and_approve_reconciles_cash(): void
    {
        $user = User::create(['name' => 'Cashier', 'email' => 'c@test.com', 'password' => 'x', 'status' => 'active']);
        $manager = User::create(['name' => 'Mgr', 'email' => 'm@test.com', 'password' => 'x', 'status' => 'active']);
        $branch = Branch::create(['name' => 'Main', 'code' => 'M1', 'status' => 'active']);
        $svc = app(ShiftService::class);

        $shift = $svc->open($user, $branch->id, null, 5000);
        $this->assertEquals('open', $shift->status);

        $shift->update(['cash_sales' => 3000, 'refunds' => 200]);

        // counted 7700 vs expected 7800 => -100 shortage.
        $svc->close($shift->fresh(), 7700);
        $shift->refresh();
        $this->assertEquals('pending_approval', $shift->status);
        $this->assertEquals(7800.0, (float) $shift->expected_cash);
        $this->assertEquals(-100.0, (float) $shift->cash_difference);

        $svc->approve($shift->fresh(), $manager);
        $this->assertEquals('approved', $shift->fresh()->status);
    }

    public function test_only_one_open_shift_per_cashier_branch(): void
    {
        $user = User::create(['name' => 'Cashier', 'email' => 'c@test.com', 'password' => 'x', 'status' => 'active']);
        $branch = Branch::create(['name' => 'Main', 'code' => 'M1', 'status' => 'active']);
        $svc = app(ShiftService::class);

        $svc->open($user, $branch->id, null, 1000);

        $this->expectException(ValidationException::class);
        $svc->open($user, $branch->id, null, 2000);
    }
}
