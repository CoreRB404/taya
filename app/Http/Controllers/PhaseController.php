<?php

namespace App\Http\Controllers;

use App\Models\DetaineePhase;
use App\Services\AuditService;
use App\Services\PhaseComplianceService;
use Illuminate\Http\Request;

class PhaseController extends Controller
{
    public function __construct(
        protected PhaseComplianceService $phaseService
    ) {}

    public function complete(Request $request, DetaineePhase $phase)
    {
        $this->authorize('complete', $phase);

        $this->phaseService->completePhase($phase, $request->user());

        return redirect()->back()
            ->with('success', "Phase {$phase->phase_number} ({$phase->phase_name}) marked as complete.");
    }

    public function flag(Request $request, DetaineePhase $phase)
    {
        $this->authorize('flag', $phase);

        $request->validate([
            'flag_reason' => 'required|string|max:1000',
        ]);

        $phase->update([
            'flagged' => true,
            'flag_reason' => $request->input('flag_reason'),
        ]);

        AuditService::log(
            'phase_flagged',
            "Phase {$phase->phase_number} ({$phase->phase_name}) flagged: {$request->input('flag_reason')}",
            $phase->detainee_id
        );

        return redirect()->back()
            ->with('success', "Phase {$phase->phase_number} flagged successfully.");
    }
}
