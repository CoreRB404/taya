<?php

namespace Database\Seeders;

use App\Models\Detainee;
use App\Models\Facility;
use App\Models\PenaltyReference;
use App\Models\User;
use App\Services\PhaseComplianceService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DetaineeSeeder extends Seeder
{
    public function run(PhaseComplianceService $phaseService): void
    {
        $facilities = Facility::all();
        $penalties = PenaltyReference::all();
        $penaltyByCode = $penalties->keyBy('rpc_code');
        $admin = User::where('role', 'admin')->first();

        // Realistic current-time test data:
        // Some committed recently (phases still upcoming), some weeks/months ago (overdue phases)
        $detainees = [
            // Recently committed — all phases upcoming
            ['name' => 'Juan Dela Cruz',       'days_ago' => 2],
            ['name' => 'Maria Santos',         'days_ago' => 5],
            ['name' => 'Pedro Penduko',        'days_ago' => 7],

            // 2-3 weeks ago — Phase 1 should be overdue (15-day deadline passed)
            ['name' => 'Jose Rizal',           'days_ago' => 18],
            ['name' => 'Andres Bonifacio',     'days_ago' => 20],
            ['name' => 'Emilio Aguinaldo',     'days_ago' => 22],

            // 1 month ago — Phase 1 & 2 overdue (25-day deadline passed)
            ['name' => 'Apolinario Mabini',    'days_ago' => 30],
            ['name' => 'Marcelo del Pilar',    'days_ago' => 35],

            // 2 months ago — Phase 1, 2, 3 overdue (55-day deadline passed)
            ['name' => 'Sultan Kudarat',       'days_ago' => 60],
            ['name' => 'Gabriela Silang',      'days_ago' => 65],
            ['name' => 'Melchora Aquino',      'days_ago' => 70],

            // 3+ months ago — All 4 phases overdue (75-day deadline passed)
            ['name' => 'Antonio Luna',         'days_ago' => 90],
            ['name' => 'Gregorio del Pilar',   'days_ago' => 100],
            ['name' => 'Francisco Dagohoy',    'days_ago' => 120],
            ['name' => 'Diego Silang',         'days_ago' => 150],

            // Status-specific records for queue filtering
            ['name' => 'Ramon Magsaysay', 'days_ago' => 300, 'status' => 'released', 'rpc_code' => 'Art. 264', 'target_alert' => 'resolved'],
            ['name' => 'Andrea dela Cruz', 'days_ago' => 420, 'status' => 'resolved', 'rpc_code' => 'Art. 264', 'target_alert' => 'resolved'],
            ['name' => 'Marina Reyes', 'days_ago' => 180, 'status' => 'archived', 'rpc_code' => 'Art. 264', 'target_alert' => 'resolved'],

            // Explicit alert level examples for the queue
            ['name' => 'Carlos Villanueva', 'days_ago' => 400, 'rpc_code' => 'Art. 264', 'target_alert' => 'critical'],
            ['name' => 'Rafael Tan', 'days_ago' => 310, 'rpc_code' => 'Art. 264', 'target_alert' => 'at_risk'],
            ['name' => 'Fiona Mercado', 'days_ago' => 220, 'rpc_code' => 'Art. 264', 'target_alert' => 'flagged'],
            ['name' => 'Miguel Santos', 'days_ago' => 90, 'rpc_code' => 'Art. 264', 'target_alert' => 'monitored'],

            // Long-term detainees — high overstay risk
            ['name' => 'Lapu-Lapu',            'days_ago' => 365],
            ['name' => 'Rajah Sulayman',       'days_ago' => 500],
            ['name' => 'Rajah Humabon',        'days_ago' => 730],
            ['name' => 'Datu Puti',            'days_ago' => 900],
            ['name' => 'Miguel Malvar',        'days_ago' => 1200],
        ];

        foreach ($detainees as $data) {
            $facility = $facilities->random();
            $penalty = isset($data['rpc_code']) && isset($penaltyByCode[$data['rpc_code']])
                ? $penaltyByCode[$data['rpc_code']]
                : $penalties->random();
            $commitmentDate = Carbon::now()->subDays($data['days_ago']);

            $detainee = Detainee::firstOrCreate(
                ['full_name' => $data['name']],
                [
                    'facility_id' => $facility->id,
                    'commitment_date' => $commitmentDate,
                    'charge_rpc_code' => $penalty->id,
                    'charge_description' => $penalty->charge_name,
                    'status' => $data['status'] ?? 'active',
                    'created_by' => $admin->id,
                ]
            );

            if (! $detainee->wasRecentlyCreated) {
                continue;
            }

            // Initialize the 4 compliance phases with real due dates
            $phaseService->initializePhases($detainee);

            // For detainees committed months ago, complete early phases realistically
            if ($data['days_ago'] >= 90) {
                // Complete phase 1 on time (within 15 days)
                $phase1 = $detainee->phases()->where('phase_number', 1)->first();
                $phaseService->completePhase($phase1, $admin);

                // Complete phase 2 on time (within 25 days)
                $phase2 = $detainee->phases()->where('phase_number', 2)->first();
                $phaseService->completePhase($phase2, $admin);
            }

            // Compute overstay and generate alerts
            $phaseService->computeOverstay($detainee, sendNotifications: false);

            if (!empty($data['target_alert'])) {
                $latestAlert = $detainee->alerts()->latest()->first();
                $latestAlert->update([
                    'alert_level' => $data['target_alert'],
                    'resolved_at' => $data['target_alert'] === 'resolved' ? now() : null,
                ]);
            }
        }

        // Flag all overdue phases
        $phaseService->flagOverduePhases();

        // Assign some lawyers to critical/at-risk alerts
        $lawyers = User::whereIn('role', ['lawyer', 'authorized_user'])->get();
        $criticalAlerts = \App\Models\Alert::whereIn('alert_level', ['critical', 'at_risk'])
            ->whereNull('resolved_at')
            ->get();

        foreach ($criticalAlerts as $alert) {
            if ($lawyers->isNotEmpty()) {
                $alert->update(['assigned_to' => $lawyers->random()->id]);
            }
        }

        // Add some legal actions
        foreach ($criticalAlerts->take(3) as $alert) {
            if ($alert->assigned_to) {
                \App\Models\LegalAction::create([
                    'detainee_id' => $alert->detainee_id,
                    'alert_id' => $alert->id,
                    'filed_by' => $alert->assigned_to,
                    'action_type' => collect(['motion_for_release', 'case_review', 'habeas_corpus'])->random(),
                    'notes' => 'Urgent case review initiated. Detainee has exceeded maximum imposable penalty.',
                    'filed_at' => Carbon::now()->subDays(rand(1, 5)),
                ]);
            }
        }
    }
}
