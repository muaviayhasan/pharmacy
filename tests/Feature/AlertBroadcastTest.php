<?php

namespace Tests\Feature;

use App\Events\CriticalAlertRaised;
use App\Models\Branch;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Services\AlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AlertBroadcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_critical_alert_is_broadcast_when_expired_stock_exists(): void
    {
        Event::fake([CriticalAlertRaised::class]);

        $branch = Branch::create(['name' => 'Main', 'code' => 'M1', 'status' => 'active']);
        $medicine = Medicine::create(['name' => 'Med', 'sale_price' => 10, 'purchase_price' => 5, 'reorder_level' => 0, 'status' => 'active']);
        MedicineBatch::create([
            'medicine_id' => $medicine->id, 'branch_id' => $branch->id, 'batch_no' => 'OLD',
            'expiry_date' => now()->subDay(), 'purchase_price' => 5, 'sale_price' => 10,
            'quantity' => 20, 'available_quantity' => 20, 'status' => 'in_stock',
        ]);

        app(AlertService::class)->generate();

        Event::assertDispatched(CriticalAlertRaised::class);
    }
}
