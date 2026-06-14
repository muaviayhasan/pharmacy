<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Services\AlertService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $query = Alert::query()->with('branch', 'assignee')->latest();

        if ($priority = $request->string('priority')->value()) {
            $query->where('priority', $priority);
        }
        if ($module = $request->string('module')->value()) {
            $query->where('module', $module);
        }
        if ($status = $request->string('status')->value()) {
            $query->where('status', $status);
        }

        return view('alerts.index', [
            'alerts' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only('priority', 'module', 'status'),
            'stats' => [
                'open' => Alert::whereIn('status', ['unread', 'read', 'pending', 'in_progress'])->count(),
                'critical' => Alert::where('priority', 'critical')->whereNotIn('status', ['resolved', 'dismissed'])->count(),
                'expiry' => Alert::where('module', 'expiry')->whereNotIn('status', ['resolved', 'dismissed'])->count(),
                'payments' => Alert::where('module', 'ledger')->whereNotIn('status', ['resolved', 'dismissed'])->count(),
            ],
        ]);
    }

    public function generate(AlertService $service): RedirectResponse
    {
        $count = $service->generate();

        return back()->with('status', "Alert scan complete — {$count} active alerts.");
    }

    public function resolve(Alert $alert): RedirectResponse
    {
        $alert->update(['status' => 'resolved', 'resolved_at' => now()]);

        return back()->with('status', "Alert {$alert->alert_no} resolved.");
    }

    public function dismiss(Alert $alert): RedirectResponse
    {
        $alert->update(['status' => 'dismissed']);

        return back()->with('status', "Alert {$alert->alert_no} dismissed.");
    }
}
