<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Detainee;
use App\Models\Facility;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function facilityReport(Facility $facility)
    {
        abort_unless(request()->user()->canAccessFacility($facility->id), 403);
        $facility->load(['detainees' => fn($q) => $q->where('status', 'active')
            ->with(['penaltyReference', 'alerts' => fn($q2) => $q2->latest()->limit(1), 'phases'])]);

        $stats = [
            'total_active' => $facility->detainees->where('status', 'active')->count(),
            'critical' => $facility->detainees->flatMap->alerts->where('alert_level', 'critical')->count(),
            'at_risk' => $facility->detainees->flatMap->alerts->where('alert_level', 'at_risk')->count(),
            'capacity' => $facility->capacity,
            'occupancy_pct' => $facility->capacity > 0 ? round(($facility->detainees->where('status', 'active')->count() / $facility->capacity) * 100, 1) : 0,
            'over_capacity' => $facility->detainees->where('status', 'active')->count() > $facility->capacity,
        ];

        $pdf = Pdf::loadView('reports.facility', compact('facility', 'stats'));

        return $pdf->download("facility_report_{$facility->id}_" . now()->format('Y-m-d') . '.pdf');
    }

    public function caseAlert(Alert $alert)
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

        $pdf = Pdf::loadView('reports.case-alert', compact('alert'));

        return $pdf->download("case_alert_{$alert->id}_" . now()->format('Y-m-d') . '.pdf');
    }

    public function detaineeProfile(Detainee $detainee)
    {
        $this->authorize('view', $detainee);
        $detainee->load([
            'facility',
            'penaltyReference',
            'phases' => fn($q) => $q->orderBy('phase_number'),
            'overstayComputations' => fn($q) => $q->latest()->limit(1),
            'alerts' => fn($q) => $q->latest()->limit(1),
            'legalActions.filedByUser',
            'documents.uploadedByUser',
        ]);

        $latestComputation = $detainee->overstayComputations->first();
        $latestAlert = $detainee->alerts->first();

        $pdf = Pdf::loadView('reports.detainee-profile', compact('detainee', 'latestComputation', 'latestAlert'));

        return $pdf->download("detainee_profile_{$detainee->id}_" . now()->format('Y-m-d') . '.pdf');
    }

    public function policyAnalytics(Request $request)
    {
        $validated = $request->validate(['export' => ['nullable', 'in:json']]);
        $user = $request->user();
        $alertsByLevel = Alert::visibleTo($user)->whereNull('resolved_at')
            ->selectRaw('alert_level, COUNT(*) as count')
            ->groupBy('alert_level')
            ->pluck('count', 'alert_level')
            ->toArray();

        $detaineesByFacility = Detainee::visibleTo($user)->where('status', 'active')
            ->join('facilities', 'detainees.facility_id', '=', 'facilities.id')
            ->selectRaw('facilities.name, COUNT(*) as count')
            ->groupBy('facilities.name')
            ->pluck('count', 'name')
            ->toArray();

        $monthExpression = match (\Illuminate\Support\Facades\DB::getDriverName()) {
            'pgsql' => "TO_CHAR(resolved_at, 'YYYY-MM')",
            'mysql', 'mariadb' => "DATE_FORMAT(resolved_at, '%Y-%m')",
            default => "strftime('%Y-%m', resolved_at)",
        };

        $resolutionsOverTime = Alert::visibleTo($user)->whereNotNull('resolved_at')
            ->where('resolved_at', '>=', now()->subMonths(6))
            ->selectRaw("{$monthExpression} as month, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $emptyResolutionMonths = collect(range(5, 0))
            ->mapWithKeys(fn (int $monthsAgo) => [now()->subMonths($monthsAgo)->format('Y-m') => 0])
            ->all();
        $resolutionsOverTime = array_replace($emptyResolutionMonths, $resolutionsOverTime);

        $unableToPayBail = Detainee::visibleTo($user)->where('status', 'active')
            ->where('bail_status', 'unable_to_pay')
            ->count();

        $overcrowdedFacilities = Facility::when(
            $user->isFacilityStaff(),
            fn ($query) => $query->whereKey($user->facility_id ?? 0)
        )->whereExists(function ($query) {
            $query->selectRaw('1')
                ->from('detainees')
                ->whereColumn('detainees.facility_id', 'facilities.id')
                ->where('detainees.status', 'active')
                ->groupBy('facility_id')
                ->havingRaw('COUNT(*) > facilities.capacity');
        })->count();

        if (($validated['export'] ?? null) === 'json') {
            return response()->json([
                'alerts_by_level' => $alertsByLevel,
                'detainees_by_facility' => $detaineesByFacility,
                'resolutions_over_time' => $resolutionsOverTime,
                'total_active_detainees' => Detainee::visibleTo($user)->where('status', 'active')->count(),
                'total_critical' => Alert::visibleTo($user)->where('alert_level', 'critical')->whereNull('resolved_at')->count(),
                'total_resolved' => Alert::visibleTo($user)->whereNotNull('resolved_at')->count(),
                'unable_to_pay_bail' => $unableToPayBail,
                'overcrowded_facilities' => $overcrowdedFacilities,
            ]);
        }

        return view('dashboard.policy', compact(
            'alertsByLevel',
            'detaineesByFacility',
            'resolutionsOverTime',
            'unableToPayBail',
            'overcrowdedFacilities'
        ));
    }
}
