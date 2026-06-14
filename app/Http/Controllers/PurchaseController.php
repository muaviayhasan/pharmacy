<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Purchase::query()->with('supplier', 'branch')->latest();

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('purchase_no', 'like', "%{$search}%")
                    ->orWhere('supplier_invoice_no', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$search}%"));
            });
        }

        if ($supplier = $request->integer('supplier')) {
            $query->where('supplier_id', $supplier);
        }

        if ($status = $request->string('status')->value()) {
            $query->where('payment_status', $status);
        }

        if ($from = $request->string('from')->value()) {
            $query->whereDate('invoice_date', '>=', $from);
        }
        if ($to = $request->string('to')->value()) {
            $query->whereDate('invoice_date', '<=', $to);
        }

        $purchases = $query->paginate(15)->withQueryString();

        $stats = [
            'count' => Purchase::count(),
            'total' => (float) Purchase::sum('grand_total'),
            'due' => (float) Purchase::sum('due_amount'),
            'today' => (float) Purchase::whereDate('invoice_date', today())->sum('grand_total'),
        ];

        return view('purchases.index', [
            'purchases' => $purchases,
            'suppliers' => Supplier::orderBy('name')->get(),
            'stats' => $stats,
            'filters' => $request->only('search', 'supplier', 'status', 'from', 'to'),
        ]);
    }

    public function show(Purchase $purchase)
    {
        $purchase->load('supplier', 'branch', 'creator', 'items.medicine');

        $payments = Payment::where('direction', 'out')
            ->where('supplier_id', $purchase->supplier_id)
            ->where('reference_no', $purchase->purchase_no)
            ->latest()
            ->get();

        return view('purchases.show', [
            'purchase' => $purchase,
            'payments' => $payments,
        ]);
    }
}
