<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBranchRequest;
use App\Http\Requests\UpdateBranchRequest;
use App\Models\Branch;
use App\Models\MedicineBatch;
use App\Models\PosCounter;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $query = Branch::query()->with('manager')->withCount('users', 'posCounters')->latest();

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
        }
        if ($status = $request->string('status')->value()) {
            $query->where('status', $status);
        }

        return view('branches.index', [
            'branches' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only('search', 'status'),
            'stats' => [
                'total' => Branch::count(),
                'active' => Branch::where('status', 'active')->count(),
                'counters' => PosCounter::count(),
                'stock_value' => (float) MedicineBatch::where('available_quantity', '>', 0)->selectRaw('COALESCE(SUM(available_quantity*purchase_price),0) v')->value('v'),
            ],
        ]);
    }

    public function create()
    {
        return view('branches.create', ['users' => User::orderBy('name')->get()]);
    }

    public function store(StoreBranchRequest $request): RedirectResponse
    {
        $branch = Branch::create($request->validated());

        return redirect()->route('branches.index')->with('status', "Branch \"{$branch->name}\" created.");
    }

    public function show(Branch $branch)
    {
        $branch->load('manager', 'users', 'posCounters', 'accounts');

        return view('branches.show', [
            'branch' => $branch,
            'salesTotal' => (float) Sale::where('branch_id', $branch->id)->sum('grand_total'),
            'purchaseTotal' => (float) Purchase::where('branch_id', $branch->id)->sum('grand_total'),
            'stockValue' => (float) MedicineBatch::where('branch_id', $branch->id)->where('available_quantity', '>', 0)->selectRaw('COALESCE(SUM(available_quantity*purchase_price),0) v')->value('v'),
        ]);
    }

    public function edit(Branch $branch)
    {
        return view('branches.edit', ['branch' => $branch, 'users' => User::orderBy('name')->get()]);
    }

    public function update(UpdateBranchRequest $request, Branch $branch): RedirectResponse
    {
        $branch->update($request->validated());

        return redirect()->route('branches.index')->with('status', "Branch \"{$branch->name}\" updated.");
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        if ($branch->sales()->exists() || $branch->purchases()->exists()) {
            return back()->with('error', 'Cannot delete a branch with sales or purchase history. Set it inactive instead.');
        }

        $branch->delete();

        return redirect()->route('branches.index')->with('status', 'Branch deleted.');
    }
}
