<?php

namespace App\Http\Controllers;

use App\Models\PosCounter;
use App\Models\PosShift;
use App\Services\ShiftService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        $query = PosShift::query()->with('branch', 'posCounter', 'cashier', 'approver')->latest('opened_at');

        if ($status = $request->string('status')->value()) {
            $query->where('status', $status);
        }
        if ($search = $request->string('search')->trim()->value()) {
            $query->where('shift_no', 'like', "%{$search}%");
        }

        return view('shifts.index', [
            'shifts' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only('status', 'search'),
            'stats' => [
                'open' => PosShift::where('status', 'open')->count(),
                'pending' => PosShift::where('status', 'pending_approval')->count(),
                'expected_today' => (float) PosShift::whereDate('opened_at', today())->sum('expected_cash'),
            ],
        ]);
    }

    public function create()
    {
        $branchId = (int) (session('active_branch_id') ?: Auth::user()->branches()->value('branches.id') ?: 1);

        return view('shifts.open', [
            'branches' => Auth::user()->branches,
            'counters' => PosCounter::where('branch_id', $branchId)->where('status', 'active')->get(),
            'activeBranchId' => $branchId,
            'openShift' => PosShift::where('cashier_id', Auth::id())->where('status', 'open')->first(),
        ]);
    }

    public function store(Request $request, ShiftService $service): RedirectResponse
    {
        $data = $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'pos_counter_id' => ['nullable', 'integer', 'exists:pos_counters,id'],
            'opening_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $shift = $service->open(Auth::user(), $data['branch_id'], $data['pos_counter_id'] ?? null, (float) $data['opening_cash'], $data['notes'] ?? null);
        } catch (ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first())->withInput();
        }

        return redirect()->route('shifts.index')->with('status', "Shift {$shift->shift_no} opened with Rs. ".number_format($shift->opening_cash, 2).' opening cash.');
    }

    public function close(PosShift $shift)
    {
        $expected = round($shift->opening_cash + $shift->cash_sales - $shift->refunds - $shift->expenses, 2);

        return view('shifts.close', ['shift' => $shift, 'expected' => $expected]);
    }

    public function storeClose(Request $request, PosShift $shift, ShiftService $service): RedirectResponse
    {
        $data = $request->validate([
            'counted_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $service->close($shift, (float) $data['counted_cash'], $data['notes'] ?? null);
        } catch (ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first());
        }

        return redirect()->route('shifts.index')->with('status', "Shift {$shift->shift_no} closed and sent for approval.");
    }

    public function approve(PosShift $shift, ShiftService $service): RedirectResponse
    {
        try {
            $service->approve($shift, Auth::user());
        } catch (ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first());
        }

        return back()->with('status', "Shift {$shift->shift_no} approved.");
    }

    public function reject(PosShift $shift, ShiftService $service): RedirectResponse
    {
        $service->reject($shift, Auth::user());

        return back()->with('status', "Shift {$shift->shift_no} rejected.");
    }
}
