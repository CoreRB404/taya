<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLegalActionRequest;
use App\Models\Detainee;
use App\Models\LegalAction;
use App\Services\AuditService;
use Illuminate\Http\Request;

class LegalActionController extends Controller
{
    public function index(Detainee $detainee)
    {
        $this->authorize('view', $detainee);
        $legalActions = $detainee->legalActions()
            ->with(['filedByUser', 'alert'])
            ->latest('filed_at')
            ->get();

        return response()->json($legalActions)->header('Cache-Control', 'private, no-store');
    }

    public function store(StoreLegalActionRequest $request, Detainee $detainee)
    {
        $legalAction = LegalAction::create([
            'alert_id' => $request->input('alert_id'),
            'detainee_id' => $detainee->id,
            'action_type' => $request->input('action_type'),
            'filed_by' => $request->user()->id,
            'notes' => $request->input('notes'),
            'filed_at' => now(),
        ]);

        AuditService::log(
            'legal_action_filed',
            "Legal action ({$request->input('action_type')}) filed for detainee {$detainee->full_name}",
            $detainee->id
        );

        return redirect()->back()->with('success', 'Legal action logged successfully.');
    }
}
