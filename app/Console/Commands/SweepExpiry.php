<?php

namespace App\Console\Commands;

use App\Models\MedicineBatch;
use Illuminate\Console\Command;

class SweepExpiry extends Command
{
    protected $signature = 'inventory:expiry-sweep';

    protected $description = 'Flag past-expiry batches as expired so they leave the sellable pool';

    public function handle(): int
    {
        $count = MedicineBatch::whereIn('status', ['in_stock', 'near_expiry'])
            ->where('available_quantity', '>', 0)
            ->whereDate('expiry_date', '<=', now())
            ->update(['status' => 'expired']);

        $this->info("Expiry sweep complete — {$count} batch(es) marked expired.");

        return self::SUCCESS;
    }
}
