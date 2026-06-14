<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\ExpenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::query()->with('branch', 'category', 'creator')->latest();

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(fn ($q) => $q->where('expense_no', 'like', "%{$search}%")->orWhere('title', 'like', "%{$search}%"));
        }
        if ($branch = $request->integer('branch')) {
            $query->where('branch_id', $branch);
        }
        if ($category = $request->integer('category')) {
            $query->where('category_id', $category);
        }
        if ($status = $request->string('status')->value()) {
            $query->where('approval_status', $status);
        }

        return view('expenses.index', [
            'expenses' => $query->paginate(15)->withQueryString(),
            'branches' => Branch::orderBy('name')->get(),
            'categories' => ExpenseCategory::orderBy('name')->get(),
            'filters' => $request->only('search', 'branch', 'category', 'status'),
            'stats' => [
                'total' => (float) Expense::where('approval_status', 'approved')->sum('total_amount'),
                'pending' => (float) Expense::where('approval_status', 'pending')->sum('total_amount'),
                'today' => (float) Expense::whereDate('expense_date', today())->where('approval_status', 'approved')->sum('total_amount'),
                'count' => Expense::count(),
            ],
        ]);
    }

    public function create()
    {
        $branchId = (int) (session('active_branch_id') ?: Auth::user()->branches()->value('branches.id') ?: 1);

        return view('expenses.create', [
            'branches' => Auth::user()->branches,
            'categories' => ExpenseCategory::where('status', 'active')->orderBy('name')->get(),
            'accounts' => Account::orderBy('name')->get(),
            'activeBranchId' => $branchId,
        ]);
    }

    public function store(StoreExpenseRequest $request, ExpenseService $service): RedirectResponse
    {
        $data = $request->validated();
        if ($request->hasFile('receipt')) {
            $data['attachment_path'] = $request->file('receipt')->store('expense-receipts', 'public');
        }

        $expense = $service->create(Auth::user(), $data);

        return redirect()->route('expenses.index')->with('status', "Expense {$expense->expense_no} submitted for approval.");
    }

    public function approve(Expense $expense, ExpenseService $service): RedirectResponse
    {
        try {
            $service->approve($expense, Auth::user());
        } catch (ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first());
        }

        return back()->with('status', "Expense {$expense->expense_no} approved and posted to the ledger.");
    }

    public function reject(Expense $expense, ExpenseService $service): RedirectResponse
    {
        $service->reject($expense, Auth::user());

        return back()->with('status', "Expense {$expense->expense_no} rejected.");
    }
}
