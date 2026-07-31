<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view-audit-logs');
        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'detainee_id' => ['nullable', 'integer', 'exists:detainees,id'],
            'action' => ['nullable', 'string', 'max:100'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);
        $query = AuditLog::with(['user', 'detainee']);

        if ($userId = ($validated['user_id'] ?? null)) {
            $query->where('user_id', $userId);
        }

        if ($detaineeId = ($validated['detainee_id'] ?? null)) {
            $query->where('detainee_id', $detaineeId);
        }

        if ($action = ($validated['action'] ?? null)) {
            $query->whereRaw('LOWER(action) LIKE ?', ['%'.mb_strtolower($action).'%']);
        }

        if ($from = ($validated['date_from'] ?? null)) {
            $query->where('created_at', '>=', $from);
        }

        if ($to = ($validated['date_to'] ?? null)) {
            $query->where('created_at', '<=', $to . ' 23:59:59');
        }

        $logs = $query->latest('created_at')->paginate(30)->withQueryString();

        return view('admin.audit-logs', compact('logs'));
    }
}
