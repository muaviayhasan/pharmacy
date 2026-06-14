<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\MedicineBatch;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->build($request);

        return view('reports.index', $data + [
            'branches' => Branch::orderBy('name')->get(),
            'filters' => [
                'from' => $data['from'],
                'to' => $data['to'],
                'branch' => $request->integer('branch') ?: '',
            ],
        ]);
    }

    public function export(Request $request)
    {
        $data = $this->build($request);

        $pdf = Pdf::loadView('reports.pdf', $data + ['generatedAt' => now()]);

        return $pdf->download('pharmacore-report-'.$data['from'].'-to-'.$data['to'].'.pdf');
    }

    /**
     * @return array<string, mixed>
     */
    private function build(Request $request): array
    {
        $from = $request->string('from')->value() ?: now()->startOfMonth()->toDateString();
        $to = $request->string('to')->value() ?: now()->toDateString();
        $branch = $request->integer('branch') ?: null;

        $saleScope = fn ($q) => $q->whereDate('sale_date', '>=', $from)->whereDate('sale_date', '<=', $to)
            ->when($branch, fn ($x) => $x->where('branch_id', $branch));
        $purchaseScope = fn ($q) => $q->whereDate('invoice_date', '>=', $from)->whereDate('invoice_date', '<=', $to)
            ->when($branch, fn ($x) => $x->where('branch_id', $branch));

        $salesTotal = (float) $saleScope(Sale::query())->sum('grand_total');
        $purchaseTotal = (float) $purchaseScope(Purchase::query())->sum('grand_total');

        // Revenue & COGS from sale items for gross profit.
        $cogsRow = DB::table('sale_items as si')
            ->join('sales as s', 's.id', '=', 'si.sale_id')
            ->leftJoin('medicine_batches as b', 'b.id', '=', 'si.batch_id')
            ->whereDate('s.sale_date', '>=', $from)->whereDate('s.sale_date', '<=', $to)
            ->when($branch, fn ($q) => $q->where('s.branch_id', $branch))
            ->selectRaw('COALESCE(SUM(si.line_total),0) revenue, COALESCE(SUM(si.quantity * COALESCE(b.purchase_price,0)),0) cogs')
            ->first();

        $grossProfit = round(($cogsRow->revenue ?? 0) - ($cogsRow->cogs ?? 0), 2);
        $expenseTotal = (float) Expense::where('approval_status', 'approved')
            ->whereDate('expense_date', '>=', $from)->whereDate('expense_date', '<=', $to)
            ->when($branch, fn ($q) => $q->where('branch_id', $branch))->sum('total_amount');

        $byMethod = $saleScope(Sale::query())
            ->selectRaw('payment_method, COUNT(*) c, SUM(grand_total) total')
            ->groupBy('payment_method')->get();

        $byBranch = Sale::query()->whereDate('sale_date', '>=', $from)->whereDate('sale_date', '<=', $to)
            ->selectRaw('branch_id, COUNT(*) c, SUM(grand_total) total')
            ->with('branch')->groupBy('branch_id')->get();

        $topMedicines = DB::table('sale_items as si')
            ->join('sales as s', 's.id', '=', 'si.sale_id')
            ->join('medicines as m', 'm.id', '=', 'si.medicine_id')
            ->whereDate('s.sale_date', '>=', $from)->whereDate('s.sale_date', '<=', $to)
            ->when($branch, fn ($q) => $q->where('s.branch_id', $branch))
            ->selectRaw('m.name, SUM(si.quantity) qty, SUM(si.line_total) revenue')
            ->groupBy('m.id', 'm.name')->orderByDesc('revenue')->limit(8)->get();

        $byCategory = Expense::where('approval_status', 'approved')
            ->whereDate('expense_date', '>=', $from)->whereDate('expense_date', '<=', $to)
            ->when($branch, fn ($q) => $q->where('branch_id', $branch))
            ->selectRaw('category_id, SUM(total_amount) total')->with('category')->groupBy('category_id')->get();

        $stockValue = (float) MedicineBatch::where('available_quantity', '>', 0)
            ->when($branch, fn ($q) => $q->where('branch_id', $branch))
            ->selectRaw('COALESCE(SUM(available_quantity * purchase_price),0) v')->value('v');

        return [
            'from' => $from,
            'to' => $to,
            'kpis' => [
                'sales' => $salesTotal,
                'purchases' => $purchaseTotal,
                'gross_profit' => $grossProfit,
                'expenses' => $expenseTotal,
                'net_profit' => round($grossProfit - $expenseTotal, 2),
                'stock_value' => $stockValue,
                'receivable' => (float) Customer::sum('current_balance'),
                'payable' => (float) Supplier::sum('current_balance'),
            ],
            'byMethod' => $byMethod,
            'byBranch' => $byBranch,
            'topMedicines' => $topMedicines,
            'byCategory' => $byCategory,
        ];
    }
}
