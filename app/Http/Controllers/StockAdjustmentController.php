<?php

namespace App\Http\Controllers;

use App\Models\StockAdjustment;
use App\Services\StockAdjustmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class StockAdjustmentController extends Controller
{
    public function index(Request $request)
    {
        $query = StockAdjustment::query()
            ->with('branch', 'creator', 'approver')
            ->withCount('items')
            ->latest();

        if ($status = $request->string('status')->value()) {
            $query->where('status', $status);
        }
        if ($search = $request->string('search')->trim()->value()) {
            $query->where('adjustment_no', 'like', "%{$search}%");
        }

        return view('stock-adjustments.index', [
            'adjustments' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only('status', 'search'),
            'stats' => [
                'pending' => StockAdjustment::where('status', 'pending')->count(),
                'completed' => StockAdjustment::where('status', 'completed')->count(),
                'rejected' => StockAdjustment::where('status', 'rejected')->count(),
            ],
        ]);
    }

    public function approve(StockAdjustment $stockAdjustment, StockAdjustmentService $service): RedirectResponse
    {
        try {
            $service->approve($stockAdjustment->load('items'), Auth::user());
        } catch (ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first());
        }

        return back()->with('status', "Adjustment {$stockAdjustment->adjustment_no} approved and stock updated.");
    }

    public function reject(StockAdjustment $stockAdjustment, StockAdjustmentService $service): RedirectResponse
    {
        $service->reject($stockAdjustment, Auth::user());

        return back()->with('status', "Adjustment {$stockAdjustment->adjustment_no} rejected.");
    }
}
