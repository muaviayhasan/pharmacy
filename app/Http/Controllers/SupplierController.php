<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\LedgerEntry;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query()->withCount('purchases')->latest();

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('contact_person', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%"));
        }
        if ($status = $request->string('status')->value()) {
            $query->where('status', $status);
        }

        return view('suppliers.index', [
            'suppliers' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only('search', 'status'),
            'stats' => [
                'total' => Supplier::count(),
                'payable' => (float) Supplier::sum('current_balance'),
                'active' => Supplier::where('status', 'active')->count(),
            ],
        ]);
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['current_balance'] = $data['opening_balance'] ?? 0;

        $supplier = Supplier::create($data);

        return redirect()->route('suppliers.index')->with('status', "Supplier \"{$supplier->name}\" created.");
    }

    public function show(Supplier $supplier)
    {
        $entries = LedgerEntry::where('ledger_type', 'supplier')
            ->where('supplier_id', $supplier->id)
            ->latest('transaction_date')->latest('id')
            ->paginate(15);

        $totals = LedgerEntry::where('ledger_type', 'supplier')->where('supplier_id', $supplier->id)
            ->selectRaw('COALESCE(SUM(debit),0) d, COALESCE(SUM(credit),0) c')->first();

        return view('suppliers.show', [
            'supplier' => $supplier,
            'entries' => $entries,
            'totalDebit' => (float) $totals->d,
            'totalCredit' => (float) $totals->c,
        ]);
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', ['supplier' => $supplier]);
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($request->validated());

        return redirect()->route('suppliers.index')->with('status', "Supplier \"{$supplier->name}\" updated.");
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $supplier->delete();

        return redirect()->route('suppliers.index')->with('status', 'Supplier deleted.');
    }
}
