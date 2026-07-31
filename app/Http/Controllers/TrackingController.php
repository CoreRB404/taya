<?php

namespace App\Http\Controllers;

use App\Models\Detainee;
use App\Models\PenaltyReference;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function landing()
    {
        $cases = PenaltyReference::query()
            ->select(['id', 'rpc_code', 'charge_name', 'law_source', 'max_penalty_years', 'max_penalty_months'])
            ->orderBy('charge_name')
            ->get()
            ->map(function (PenaltyReference $penalty) {
                return [
                    'id' => $penalty->id,
                    'label' => sprintf('[%s] %s', $penalty->rpc_code ?: $penalty->law_source, $penalty->charge_name),
                    'penalty_display' => $penalty->penalty_duration_display,
                    'years' => (int) $penalty->max_penalty_years,
                    'months' => (int) ($penalty->max_penalty_months ?? 0),
                ];
            });

        return view('tracking.landing', compact('cases'));
    }

    public function lookup(Request $request)
    {
        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:17', 'regex:/^TAYA-[A-Z0-9]{6,12}$/i'],
        ]);
        $code = $validated['code'] ?? null;
        $error = null;

        if ($code) {
            $detainee = Detainee::where('tracking_code', strtoupper($code))
                ->where('tracking_enabled', true)
                ->first();

            if (!$detainee) {
                $error = 'Tracking code not found or is not active. Please verify the code and try again.';
            } else {
                return redirect()->route('tracking.show', $detainee->tracking_code);
            }
        }

        return view('tracking.lookup', compact('error'));
    }

    public function show(Request $request, string $code)
    {
        abort_unless(preg_match('/^TAYA-[A-Z0-9]{6,12}$/i', $code) === 1, 404);
        $detainee = Detainee::where('tracking_code', strtoupper($code))
            ->where('tracking_enabled', true)
            ->firstOrFail();

        $detainee->load([
            'facility',
            'penaltyReference',
            'phases' => fn($q) => $q->orderBy('phase_number'),
            'alerts' => fn($q) => $q->latest()->limit(1),
        ]);

        $latestAlert = $detainee->alerts->first();

        return response()
            ->view('tracking.show', compact('detainee', 'latestAlert'))
            ->header('Cache-Control', 'private, no-store');
    }
}
