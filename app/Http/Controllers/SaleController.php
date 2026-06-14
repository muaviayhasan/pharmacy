<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::query()->with('customer', 'branch', 'creator')->withCount('items')->latest('sale_date');

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(fn ($q) => $q->where('sale_no', 'like', "%{$search}%")
                ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%")));
        }
        if ($status = $request->string('payment_status')->value()) {
            $query->where('payment_status', $status);
        }
        if ($method = $request->string('payment_method')->value()) {
            $query->where('payment_method', $method);
        }
        if ($from = $request->string('from')->value()) {
            $query->whereDate('sale_date', '>=', $from);
        }
        if ($to = $request->string('to')->value()) {
            $query->whereDate('sale_date', '<=', $to);
        }

        return view('sales.index', [
            'sales' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only('search', 'payment_status', 'payment_method', 'from', 'to'),
            'stats' => [
                'total' => (float) Sale::sum('grand_total'),
                'count' => Sale::count(),
                'today' => (float) Sale::whereDate('sale_date', today())->sum('grand_total'),
                'due' => (float) Sale::sum('due_amount'),
            ],
        ]);
    }

    public function show(Sale $sale)
    {
        $sale->load('customer', 'branch', 'shift', 'creator', 'items.medicine', 'returns');

        return view('sales.show', ['sale' => $sale]);
    }
}
