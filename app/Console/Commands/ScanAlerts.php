<?php

namespace App\Console\Commands;

use App\Services\AlertService;
use Illuminate\Console\Command;

class ScanAlerts extends Command
{
    protected $signature = 'alerts:scan';

    protected $description = 'Scan inventory and ledgers and (re)generate active alerts';

    public function handle(AlertService $alerts): int
    {
        $count = $alerts->generate();
        $this->info("Alert scan complete — {$count} active alerts.");

        return self::SUCCESS;
    }
}
