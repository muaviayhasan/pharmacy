<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::query()->with('user', 'branch')->latest();

        if ($module = $request->string('module')->value()) {
            $query->where('module', $module);
        }
        if ($action = $request->string('action')->value()) {
            $query->where('action', $action);
        }
        if ($risk = $request->string('risk')->value()) {
            $query->where('risk_level', $risk);
        }
        if ($user = $request->integer('user')) {
            $query->where('user_id', $user);
        }
        if ($from = $request->string('from')->value()) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->string('to')->value()) {
            $query->whereDate('created_at', '<=', $to);
        }

        return view('audit-logs.index', [
            'logs' => $query->paginate(25)->withQueryString(),
            'filters' => $request->only('module', 'action', 'risk', 'user', 'from', 'to'),
            'modules' => AuditLog::query()->select('module')->distinct()->orderBy('module')->pluck('module'),
            'users' => User::orderBy('name')->get(['id', 'name']),
            'stats' => [
                'total' => AuditLog::count(),
                'critical' => AuditLog::where('risk_level', 'high')->count(),
                'failed' => AuditLog::where('status', 'failed')->count(),
                'today' => AuditLog::whereDate('created_at', today())->count(),
            ],
        ]);
    }
}
