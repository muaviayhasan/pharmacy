<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Models\LedgerEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query()->withCount('sales')->latest();

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        }
        if ($type = $request->string('type')->value()) {
            $query->where('customer_type', $type);
        }
        if ($status = $request->string('status')->value()) {
            $query->where('status', $status);
        }

        return view('customers.index', [
            'customers' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only('search', 'type', 'status'),
            'stats' => [
                'total' => Customer::count(),
                'receivable' => (float) Customer::sum('current_balance'),
                'credit' => Customer::where('customer_type', 'credit')->count(),
                'active' => Customer::where('status', 'active')->count(),
            ],
        ]);
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['current_balance'] = $data['opening_balance'] ?? 0;

        $customer = Customer::create($data);

        return redirect()->route('customers.index')->with('status', "Customer \"{$customer->name}\" created.");
    }

    public function show(Customer $customer)
    {
        $entries = LedgerEntry::where('ledger_type', 'customer')
            ->where('customer_id', $customer->id)
            ->latest('transaction_date')->latest('id')
            ->paginate(15);

        $totals = LedgerEntry::where('ledger_type', 'customer')->where('customer_id', $customer->id)
            ->selectRaw('COALESCE(SUM(debit),0) d, COALESCE(SUM(credit),0) c')->first();

        return view('customers.show', [
            'customer' => $customer,
            'entries' => $entries,
            'totalDebit' => (float) $totals->d,
            'totalCredit' => (float) $totals->c,
        ]);
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', ['customer' => $customer]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());

        return redirect()->route('customers.index')->with('status', "Customer \"{$customer->name}\" updated.");
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return redirect()->route('customers.index')->with('status', 'Customer deleted.');
    }
}
