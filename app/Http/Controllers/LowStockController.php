<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LowStockController extends Controller
{
    public function index(Request $request)
    {
        $branch = $request->integer('branch') ?: null;
        $tab = $request->string('tab')->value() ?: 'all';

        $stockSub = DB::table('medicine_batches')
            ->selectRaw('medicine_id, SUM(available_quantity) q')
            ->when($branch, fn ($q) => $q->where('branch_id', $branch))
            ->groupBy('medicine_id');

        $rows = DB::table('medicines as m')
            ->leftJoinSub($stockSub, 's', 's.medicine_id', '=', 'm.id')
            ->whereNull('m.deleted_at')
            ->where('m.status', 'active')
            ->selectRaw('m.id, m.name, m.generic_name, m.reorder_level, m.min_stock_level, m.max_stock_level, m.purchase_price, COALESCE(s.q,0) as available')
            ->orderBy('m.name')
            ->get()
            ->filter(fn ($r) => $r->available <= max($r->reorder_level, 0))
            ->map(function ($r) {
                $target = $r->max_stock_level > 0 ? $r->max_stock_level : max($r->reorder_level * 3, 10);
                $r->suggested = max($target - $r->available, 0);
                $r->priority = $r->available <= 0 ? 'out' : ($r->available <= max(1, (int) floor($r->reorder_level / 2)) ? 'critical' : 'low');
                $r->reorder_value = $r->suggested * (float) $r->purchase_price;

                return $r;
            })->values();

        $stats = [
            'all' => $rows->count(),
            'out' => $rows->where('priority', 'out')->count(),
            'critical' => $rows->where('priority', 'critical')->count(),
            'low' => $rows->where('priority', 'low')->count(),
            'value' => $rows->sum('reorder_value'),
        ];

        if (in_array($tab, ['out', 'critical', 'low'])) {
            $rows = $rows->where('priority', $tab)->values();
        }

        return view('inventory.low-stock', [
            'rows' => $rows,
            'stats' => $stats,
            'branches' => Branch::orderBy('name')->get(),
            'branch' => $branch,
            'tab' => $tab,
        ]);
    }
}
