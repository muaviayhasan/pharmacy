<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class Audit
{
    /**
     * Record a sensitive action in the audit trail.
     *
     * @param  array{reference?:object, old?:array, new?:array, risk?:string, status?:string, user_id?:int, role?:string}  $opts
     */
    public static function log(string $module, string $action, ?string $description = null, array $opts = []): void
    {
        $user = Auth::user();
        $request = request();
        $reference = $opts['reference'] ?? null;

        AuditLog::create([
            'user_id' => $opts['user_id'] ?? $user?->id,
            'role_name' => $opts['role'] ?? $user?->getRoleNames()->first(),
            'branch_id' => session('active_branch_id'),
            'module' => $module,
            'action' => $action,
            'reference_type' => $reference ? $reference::class : null,
            'reference_id' => $reference?->getKey(),
            'old_value' => $opts['old'] ?? null,
            'new_value' => array_merge($opts['new'] ?? [], $description ? ['note' => $description] : []),
            'ip_address' => $request?->ip(),
            'device' => substr((string) $request?->userAgent(), 0, 250),
            'risk_level' => $opts['risk'] ?? 'low',
            'status' => $opts['status'] ?? 'success',
            'created_at' => now(),
        ]);
    }
}
