<?php

namespace App\Services;

use App\Events\CriticalAlertRaised;
use App\Models\Alert;
use App\Models\Customer;
use App\Models\MedicineBatch;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class AlertService
{
    /**
     * Scan the system and (re)generate open alerts. Existing unresolved alerts
     * are cleared first so the scan is idempotent; resolved/dismissed alerts
     * are kept as history.
     */
    public function generate(): int
    {
        $count = DB::transaction(function () {
            Alert::whereIn('status', ['unread', 'read', 'pending', 'in_progress'])->delete();

            $count = 0;
            $seq = 0;

            $make = function (array $attr) use (&$count, &$seq) {
                $seq++;
                Alert::create(array_merge([
                    'alert_no' => 'ALRT-'.now()->format('ymd').'-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT),
                    'status' => 'unread',
                ], $attr));
                $count++;
            };

            // Low / out of stock per medicine + branch.
            $stock = DB::table('medicine_batches as b')
                ->join('medicines as m', 'm.id', '=', 'b.medicine_id')
                ->leftJoin('branches as br', 'br.id', '=', 'b.branch_id')
                ->whereNull('m.deleted_at')
                ->groupBy('b.medicine_id', 'b.branch_id', 'm.name', 'm.reorder_level', 'br.name')
                ->selectRaw('m.name, m.reorder_level, br.name as branch, b.branch_id, SUM(b.available_quantity) qty')
                ->havingRaw('SUM(b.available_quantity) <= GREATEST(m.reorder_level, 0)')
                ->limit(60)->get();

            foreach ($stock as $row) {
                $out = $row->qty <= 0;
                $make([
                    'branch_id' => $row->branch_id,
                    'module' => 'inventory',
                    'alert_type' => $out ? 'out_of_stock' : 'low_stock',
                    'title' => ($out ? 'Out of stock: ' : 'Low stock: ').$row->name,
                    'message' => "{$row->name} at {$row->branch} has {$row->qty} units (reorder level {$row->reorder_level}).",
                    'priority' => $out ? 'critical' : 'high',
                ]);
            }

            // Expiry alerts.
            $batches = MedicineBatch::with('medicine', 'branch')
                ->where('available_quantity', '>', 0)
                ->whereIn('status', ['in_stock', 'near_expiry'])
                ->whereDate('expiry_date', '<=', now()->addDays(90))
                ->orderBy('expiry_date')->limit(60)->get();

            foreach ($batches as $batch) {
                $expired = $batch->expiry_date->lte(now());
                $make([
                    'branch_id' => $batch->branch_id,
                    'module' => 'expiry',
                    'alert_type' => $expired ? 'expired' : 'near_expiry',
                    'title' => ($expired ? 'Expired: ' : 'Near expiry: ').$batch->medicine?->name,
                    'message' => "Batch {$batch->batch_no} ({$batch->available_quantity} units) expires {$batch->expiry_date->format('d M Y')} at {$batch->branch?->name}.",
                    'priority' => $expired ? 'critical' : 'medium',
                    'reference_type' => MedicineBatch::class,
                    'reference_id' => $batch->id,
                ]);
            }

            // Supplier payment due.
            foreach (Supplier::where('current_balance', '>', 0)->limit(40)->get() as $supplier) {
                $make([
                    'module' => 'ledger',
                    'alert_type' => 'supplier_payment_due',
                    'title' => 'Supplier payable: '.$supplier->name,
                    'message' => 'Rs. '.number_format($supplier->current_balance, 2)." payable to {$supplier->name}.",
                    'priority' => $supplier->current_balance > 100000 ? 'high' : 'medium',
                    'reference_type' => Supplier::class,
                    'reference_id' => $supplier->id,
                ]);
            }

            // Customer receivable due.
            foreach (Customer::where('current_balance', '>', 0)->limit(40)->get() as $customer) {
                $make([
                    'module' => 'ledger',
                    'alert_type' => 'customer_payment_due',
                    'title' => 'Customer receivable: '.$customer->name,
                    'message' => 'Rs. '.number_format($customer->current_balance, 2)." receivable from {$customer->name}.",
                    'priority' => 'medium',
                    'reference_type' => Customer::class,
                    'reference_id' => $customer->id,
                ]);
            }

            return $count;
        });

        // Notify connected clients in real time when critical alerts exist.
        $criticals = Alert::where('priority', 'critical')->where('status', 'unread')->get();
        if ($criticals->isNotEmpty()) {
            CriticalAlertRaised::dispatch($criticals->count(), $criticals->first()->title);
        }

        return $count;
    }
}
