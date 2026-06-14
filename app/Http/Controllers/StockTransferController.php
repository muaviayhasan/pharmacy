<?php

namespace App\Http\Controllers;

use App\Models\StockTransfer;
use App\Services\StockTransferService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class StockTransferController extends Controller
{
    public function index(Request $request)
    {
        $query = StockTransfer::query()
            ->with('fromBranch', 'toBranch', 'requester')
            ->withCount('items')
            ->latest();

        if ($status = $request->string('status')->value()) {
            $query->where('status', $status);
        }
        if ($search = $request->string('search')->trim()->value()) {
            $query->where('transfer_no', 'like', "%{$search}%");
        }

        return view('stock-transfers.index', [
            'transfers' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only('status', 'search'),
            'stats' => [
                'pending' => StockTransfer::where('status', 'pending')->count(),
                'dispatched' => StockTransfer::where('status', 'dispatched')->count(),
                'received' => StockTransfer::where('status', 'received')->count(),
            ],
        ]);
    }

    public function dispatchTransfer(StockTransfer $stockTransfer, StockTransferService $service): RedirectResponse
    {
        try {
            $service->dispatch($stockTransfer->load('items'), Auth::user());
        } catch (ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first());
        }

        return back()->with('status', "Transfer {$stockTransfer->transfer_no} dispatched from source branch.");
    }

    public function receive(StockTransfer $stockTransfer, StockTransferService $service): RedirectResponse
    {
        try {
            $service->receive($stockTransfer->load('items'), Auth::user());
        } catch (ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first());
        }

        return back()->with('status', "Transfer {$stockTransfer->transfer_no} received into destination branch.");
    }
}
