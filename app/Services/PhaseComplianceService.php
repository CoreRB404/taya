<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Detainee;
use App\Models\DetaineePhase;
use App\Models\OverstayComputation;
use App\Models\User;
use App\Notifications\AlertNotification;
use Carbon\Carbon;

class PhaseComplianceService
{
    /**
     * Initialize the 4 compliance phases for a new detainee.
     */
    public function initializePhases(Detainee $detainee): void
    {
        $commitmentDate = Carbon::parse($detainee->commitment_date);

        $phases = [
            [
                'phase_number' => 1,
                'phase_name' => 'Preliminary Investigation',
                'day_count' => 15,
                'due_date' => $commitmentDate->copy()->addDays(15),
            ],
            [
                'phase_number' => 2,
                'phase_name' => 'Filing of Information',
                'day_count' => 10,
                'due_date' => $commitmentDate->copy()->addDays(25), // 15 + 10
            ],
            [
                'phase_number' => 3,
                'phase_name' => 'Arraignment',
                'day_count' => 30,
                'due_date' => $commitmentDate->copy()->addDays(55), // 25 + 30
            ],
            [
                'phase_number' => 4,
                'phase_name' => 'Pre-Trial',
                'day_count' => 20,
                'due_date' => $commitmentDate->copy()->addDays(75), // 55 + 20
            ],
        ];

        foreach ($phases as $phase) {
            $detainee->phases()->create($phase);
        }

        AuditService::log(
            'phases_initialized',
            "Initialized 4 compliance phases for detainee: {$detainee->full_name}",
            $detainee->id
        );
    }

    /**
     * Mark a phase as completed.
     */
    public function completePhase(DetaineePhase $phase, User $user): void
    {
        $completedAt = now();

        $phase->update([
            'completed' => true,
            'completed_at' => $completedAt,
            'completed_by' => $user->id,
            'flagged' => false,
            // Notice: We intentionally do NOT clear 'flag_reason' here 
            // so it remains as a historical record if the phase was completed late.
        ]);

        // Cascading Timeline: Shift due dates of all subsequent uncompleted phases
        $subsequentPhases = $phase->detainee->phases()
            ->where('phase_number', '>', $phase->phase_number)
            ->where('completed', false)
            ->orderBy('phase_number')
            ->get();

        $baseDate = $completedAt->copy();
        foreach ($subsequentPhases as $nextPhase) {
            $newDueDate = $baseDate->copy()->addDays($nextPhase->day_count);
            $nextPhase->update(['due_date' => $newDueDate]);
            $baseDate = $newDueDate;
        }

        AuditService::log(
            'phase_completed',
            "Phase {$phase->phase_number} ({$phase->phase_name}) marked complete by {$user->name}. Subsequent due dates adjusted.",
            $phase->detainee_id
        );
    }

    /**
     * Flag overdue phases for all active detainees.
     * Called by the scheduled command daily.
     */
    public function flagOverduePhases(): int
    {
        $flaggedCount = 0;
        $today = Carbon::today();

        $overduePhases = DetaineePhase::whereHas('detainee', function ($query) {
            $query->where('status', 'active');
        })
            ->where('completed', false)
            ->where('due_date', '<', $today)
            ->where('flagged', false)
            ->get();

        foreach ($overduePhases as $phase) {
            $daysOverdue = $phase->due_date->diffInDays($today);
            $phase->update([
                'flagged' => true,
                'flag_reason' => "Phase overdue by {$daysOverdue} days as of {$today->toDateString()}",
            ]);

            AuditService::log(
                'phase_flagged_overdue',
                "Phase {$phase->phase_number} ({$phase->phase_name}) flagged as overdue by {$daysOverdue} days",
                $phase->detainee_id
            );

            $flaggedCount++;
        }

        return $flaggedCount;
    }

    /**
     * Compute overstay for a detainee and generate/update alerts.
     */
    public function reschedulePhases(Detainee $detainee): void
    {
        $commitmentDate = Carbon::parse($detainee->commitment_date);
        $phases = $detainee->phases()->orderBy('phase_number')->get();
        $currentDueDate = $commitmentDate->copy();

        foreach ($phases as $phase) {
            $currentDueDate = $currentDueDate->copy()->addDays($phase->day_count);
            $phase->update(['due_date' => $currentDueDate]);
        }

        AuditService::log(
            'phases_rescheduled',
            "Phases rescheduled for detainee {$detainee->full_name} based on updated commitment date {$detainee->commitment_date->toDateString()}",
            $detainee->id
        );
    }

    public function computeOverstay(Detainee $detainee, bool $sendNotifications = true): OverstayComputation
    {
        $penalty = $detainee->penaltyReference;

        // Convert max penalty to days (years * 365 + months * 30)
        $maxPenaltyDays = (int) ($penalty->max_penalty_years * 365);
        if ($penalty->max_penalty_months) {
            $maxPenaltyDays += $penalty->max_penalty_months * 30;
        }

        $daysDetained = $detainee->days_detained;
        $overstayDays = max(0, $daysDetained - $maxPenaltyDays);

        // Determine alert level based on percentage of max penalty served
        $percentage = $maxPenaltyDays > 0 ? ($daysDetained / $maxPenaltyDays) * 100 : 100;
        $alertLevel = $this->determineAlertLevel($percentage, $detainee);

        // Create computation record
        $computation = OverstayComputation::create([
            'detainee_id' => $detainee->id,
            'days_detained' => $daysDetained,
            'max_penalty_days' => $maxPenaltyDays,
            'overstay_days' => $overstayDays,
            'alert_level' => $alertLevel,
            'computed_at' => now(),
        ]);

        // Create or update alert
        $recommendedAction = $this->getRecommendedAction($alertLevel, $detainee);

        $alertData = [
            'computation_id' => $computation->id,
            'detainee_id' => $detainee->id,
            'alert_level' => $alertLevel,
            'recommended_action' => $recommendedAction,
        ];

        if ($alertLevel === 'resolved') {
            $alertData['resolved_at'] = now();
        }

        $existingAlert = $detainee->alerts()->whereNull('resolved_at')->latest()->first();

        if ($existingAlert) {
            $existingAlert->update($alertData);
            $alert = $existingAlert;
        } else {
            $alert = Alert::create($alertData);
        }

        // Send notification for critical/at-risk alerts.
        // If the alert is assigned, notify the assigned user. If unassigned,
        // notify the pool of responsible users (admins / lawyers) so the
        // alert still generates a mail log entry even when not explicitly assigned.
        if ($sendNotifications && in_array($alertLevel, ['critical', 'at_risk'])) {
            if ($alert->assigned_to) {
                $alert->assignedUser->notify(new AlertNotification($alert));
            } else {
                $recipients = User::where('is_active', true)->whereIn('role', ['admin', 'lawyer', 'authorized_user'])->get();
                if ($recipients->isNotEmpty()) {
                    \Illuminate\Support\Facades\Notification::send($recipients, new AlertNotification($alert));
                } else {
                    // As a fallback, write an audit/log entry so operators can spot the unassigned alert.
                    \Illuminate\Support\Facades\Log::info("Unassigned alert requires attention: {$alert->id}", ['alert_id' => $alert->id]);
                }
            }
        }

        AuditService::log(
            'overstay_computed',
            "Overstay computed: {$daysDetained} days detained, max {$maxPenaltyDays} days, overstay {$overstayDays} days, level: {$alertLevel}",
            $detainee->id
        );

        return $computation;
    }

    /**
     * Determine alert level based on detention percentage.
     */
    protected function determineAlertLevel(float $percentage, Detainee $detainee): string
    {
        if ($detainee->status === 'resolved' || $detainee->status === 'released') {
            return 'resolved';
        }

        return match (true) {
            $percentage >= 100 => 'critical',
            $percentage >= 75 => 'at_risk',
            $percentage >= 50 => 'flagged',
            default => 'monitored',
        };
    }

    /**
     * Get recommended legal action based on alert level.
     */
    public function getRecommendedAction(string $alertLevel, Detainee $detainee): string
    {
        return match ($alertLevel) {
            'critical' => 'File immediate Motion for Release or Habeas Corpus petition. Refer to PAO supervisor.',
            'at_risk' => 'Schedule urgent case review. Prepare Motion for Release if no hearing within 15 days.',
            'flagged' => 'Monitor closely. Verify RPC penalty mapping and confirm commitment date accuracy.',
            'monitored' => 'Case within legal range. Continue regular monitoring.',
            'resolved' => 'Case resolved. Archive record and update audit log.',
            default => 'Unknown alert level. Please review manually.',
        };
    }
}
