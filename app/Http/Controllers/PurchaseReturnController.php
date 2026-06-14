<?php

namespace App\Http\Controllers;

use App\Models\PurchaseReturn;
use Illuminate\Http\Request;

class PurchaseReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseReturn::query()->with('purchase', 'supplier', 'branch')->withCount('items')->latest();

        if ($search = $request->string('search')->trim()->value()) {
            $query->where('return_no', 'like', "%{$search}%")
                ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$search}%"));
        }

        return view('purchase-returns.index', [
            'returns' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only('search'),
            'stats' => [
                'count' => PurchaseReturn::count(),
                'amount' => (float) PurchaseReturn::sum('return_amount'),
                'today' => (float) PurchaseReturn::whereDate('return_date', today())->sum('return_amount'),
            ],
        ]);
    }
}
