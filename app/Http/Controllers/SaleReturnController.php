<?php

namespace App\Http\Controllers;

use App\Models\SaleReturn;
use Illuminate\Http\Request;

class SaleReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = SaleReturn::query()->with('sale', 'customer', 'branch')->withCount('items')->latest();

        if ($search = $request->string('search')->trim()->value()) {
            $query->where('return_no', 'like', "%{$search}%")
                ->orWhereHas('sale', fn ($s) => $s->where('sale_no', 'like', "%{$search}%"));
        }

        return view('sale-returns.index', [
            'returns' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only('search'),
            'stats' => [
                'count' => SaleReturn::count(),
                'amount' => (float) SaleReturn::sum('refund_amount'),
                'today' => (float) SaleReturn::whereDate('return_date', today())->sum('refund_amount'),
            ],
        ]);
    }
}
