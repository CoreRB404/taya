<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditService
{
    /**
     * Log an action to the audit trail.
     */
    public static function log(string $action, string $description, ?int $detaineeId = null, ?int $userId = null): ?AuditLog
    {
        try {
            $audit = AuditLog::create([
                'user_id' => $userId ?? Auth::id(),
                'detainee_id' => $detaineeId,
                'action' => $action,
                'description' => $description,
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            Log::error('audit_database_write_failed', [
                'action' => $action,
                'user_id' => $userId ?? Auth::id(),
                'detainee_id' => $detaineeId,
                'exception' => $exception::class,
            ]);

            return null;
        }

        // Also write a structured entry to the application log so system operators
        // can see audit events in `storage/logs/laravel.log` alongside the DB record.
        try {
            Log::info('audit_event', [
                'audit_id' => $audit->id,
                'action' => $action,
                'description' => $description,
                'user_id' => $audit->user_id,
                'detainee_id' => $audit->detainee_id,
                'ip' => $audit->ip_address,
                'created_at' => $audit->created_at->toDateTimeString(),
            ]);
        } catch (\Throwable $e) {
            // Never let logging failures break the application flow; continue silently.
        }

        return $audit;
    }
}
