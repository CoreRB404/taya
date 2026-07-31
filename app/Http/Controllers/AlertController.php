<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAlertRequest;
use App\Models\Alert;
use App\Models\Facility;
use App\Models\User;
use App\Notifications\AlertNotification;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Alert::class);
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'record_filter' => ['nullable', Rule::in([
                'all_alerts', 'unresolved', 'status:active', 'status:released', 'status:archived',
                'alert:critical', 'alert:at_risk', 'alert:flagged', 'alert:monitored', 'alert:resolved',
            ])],
            'facility_id' => ['nullable', 'integer', 'exists:facilities,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);
        $query = Alert::visibleTo($request->user())
            ->with(['detainee.facility', 'detainee.penaltyReference', 'detainee.phases', 'assignedUser', 'computation']);

        // Global search across detainee name, charge, and alert id
        if ($search = ($validated['search'] ?? null)) {
            $query->where(function ($q) use ($search) {
                if (ctype_digit($search)) {
                    $q->where('id', (int) $search);
                }
                $q->orWhereHas('detainee', function ($dq) use ($search) {
                      $term = '%'.mb_strtolower($search).'%';
                      $dq->whereRaw('LOWER(full_name) LIKE ?', [$term])
                         ->orWhereRaw('LOWER(charge_description) LIKE ?', [$term]);
                  });
            });

            // Prefer detainee names that start with the search term, then contains,
            // then charge description matches. We implement a lightweight relevance
            // ordering using a CASE expression on the related detainee fields.
            $prefix = mb_strtolower($search) . '%';
            $contains = '%' . mb_strtolower($search) . '%';

            $query->orderByRaw("CASE
                WHEN LOWER((SELECT full_name FROM detainees WHERE detainees.id = alerts.detainee_id)) LIKE ? THEN 1
                WHEN LOWER((SELECT full_name FROM detainees WHERE detainees.id = alerts.detainee_id)) LIKE ? THEN 2
                WHEN LOWER((SELECT charge_description FROM detainees WHERE detainees.id = alerts.detainee_id)) LIKE ? THEN 3
                ELSE 4 END",
                [$prefix, $contains, $contains]
            );
        }

        $filter = $validated['record_filter'] ?? null;

        if ($filter === null || $filter === '') {
            $filter = 'all_alerts';
        }

        if ($filter === 'unresolved') {
            $query->whereNull('resolved_at');
        } elseif ($filter === 'all_alerts') {
            // no extra condition
        } elseif ($filter === 'status:active') {
            $query->whereHas('detainee', fn($q) => $q->where('status', 'active'));
        } elseif ($filter === 'status:released') {
            $query->whereHas('detainee', fn($q) => $q->where('status', 'released'));
        } elseif ($filter === 'status:archived') {
            $query->whereHas('detainee', fn($q) => $q->where('status', 'archived'));
        } elseif ($filter === 'alert:resolved') {
            $query->where('alert_level', 'resolved')->whereNotNull('resolved_at');
        } elseif (str_starts_with($filter, 'alert:')) {
            $query->where('alert_level', substr($filter, 6));
        }

        if ($facility = ($validated['facility_id'] ?? null)) {
            $query->whereHas('detainee', fn($q) => $q->where('facility_id', $facility));
        }

        if ($from = ($validated['date_from'] ?? null)) {
            $query->where('created_at', '>=', $from);
        }

        if ($to = ($validated['date_to'] ?? null)) {
            $query->where('created_at', '<=', $to . ' 23:59:59');
        }

        $alerts = $query->orderByRaw("CASE alert_level
            WHEN 'critical' THEN 1
            WHEN 'at_risk' THEN 2
            WHEN 'flagged' THEN 3
            WHEN 'monitored' THEN 4
            ELSE 5 END")
            ->paginate(20)
            ->withQueryString();

        $facilities = Facility::all();

        return view('alerts.index', compact('alerts', 'facilities'));
    }

    public function show(Alert $alert)
    {
        $this->authorize('view', $alert);
        $alert->load([
            'detainee.facility',
            'detainee.penaltyReference',
            'detainee.phases',
            'computation',
            'assignedUser',
            'legalActions.filedByUser',
        ]);

        $lawyers = User::where('is_active', true)->whereIn('role', ['admin', 'lawyer', 'authorized_user'])->get();

        return view('alerts.show', compact('alert', 'lawyers'));
    }

    public function assign(Request $request, Alert $alert)
    {
        $this->authorize('assign', $alert);
        $request->validate([
            'assigned_to' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('is_active', true)
                    ->whereIn('role', ['admin', 'lawyer', 'authorized_user'])),
            ],
        ]);

        $alert->update(['assigned_to' => $request->input('assigned_to')]);

        $assignedUser = User::find($request->input('assigned_to'));

        // Notify the assigned lawyer
        if (in_array($alert->alert_level, ['critical', 'at_risk'])) {
            $assignedUser->notify(new AlertNotification($alert));
        }

        AuditService::log(
            'alert_assigned',
            "Alert #{$alert->id} assigned to {$assignedUser->name}",
            $alert->detainee_id
        );

        return redirect()->back()->with('success', "Alert assigned to {$assignedUser->name}.");
    }

    public function resolve(Request $request, Alert $alert)
    {
        $this->authorize('resolve', $alert);
        $detainee = $alert->detainee;
        $totalPhases = $detainee->phases()->count();
        $completedPhases = $detainee->phases()->where('completed', true)->count();
        $allPhasesComplete = $totalPhases > 0 && $completedPhases === $totalPhases;

        if (!$allPhasesComplete) {
            $request->validate([
                'current_password' => ['required', 'current_password'],
            ]);
        }

        $alert->update([
            'resolved_at' => now(),
            'alert_level' => 'resolved',
            'admin_override' => !$allPhasesComplete,
            'override_note' => !$allPhasesComplete ? 'Resolved via detainee profile override with password confirmation.' : null,
        ]);

        AuditService::log(
            'alert_resolved',
            "Alert #{$alert->id} resolved by {$request->user()->name}" . (!$allPhasesComplete ? ' (override)' : ''),
            $alert->detainee_id
        );

        return redirect()->back()->with('success', $allPhasesComplete
            ? 'Alert resolved successfully.'
            : 'Alert resolved successfully via override after password confirmation.');
    }

    public function adminOverride(UpdateAlertRequest $request, Alert $alert)
    {
        $this->authorize('override', $alert);
        $alert->update([
            'alert_level' => $request->input('alert_level'),
            'admin_override' => true,
            'override_note' => $request->input('override_note'),
        ]);

        AuditService::log(
            'alert_override',
            "Alert #{$alert->id} overridden to {$request->input('alert_level')} by admin: {$request->input('override_note')}",
            $alert->detainee_id
        );

        // If the override raises the alert to a high level and an assignee exists,
        // notify the assigned user immediately. This ensures admins' overrides
        // result in an email log entry when using the `log` mailer.
        if (in_array($request->input('alert_level'), ['critical', 'at_risk']) && $alert->assigned_to) {
            $alert->assignedUser->notify(new AlertNotification($alert));
        }

        return redirect()->back()->with('success', 'Alert level overridden successfully.');
    }
}
