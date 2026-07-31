<?php

namespace Tests\Unit;

use App\Models\Detainee;
use App\Models\PenaltyReference;
use App\Models\Facility;
use App\Models\User;
use App\Services\PhaseComplianceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseComplianceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_compute_overstay_creates_critical_alert_when_detained_beyond_max_penalty()
    {
        $facility = Facility::create([
            'name' => 'Test Facility',
            'region' => 'Region A',
            'address' => '123 Test Rd',
            'capacity' => 100,
        ]);

        $penalty = PenaltyReference::create(['rpc_code' => '000', 'charge_name' => 'Test Charge', 'max_penalty_years' => 1, 'max_penalty_months' => 0, 'law_source' => 'RPC']);
        $user = User::factory()->create();

        $detainee = Detainee::create([
            'full_name' => 'Test Detainee',
            'charge_description' => 'Test charge',
            'charge_rpc_code' => $penalty->id,
            'commitment_date' => now()->subDays(400),
            'facility_id' => $facility->id,
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        $service = $this->app->make(PhaseComplianceService::class);
        $computation = $service->computeOverstay($detainee);

        $this->assertSame('critical', $computation->alert_level);
        $this->assertGreaterThan(0, $computation->overstay_days);
        $this->assertDatabaseHas('alerts', [
            'detainee_id' => $detainee->id,
            'alert_level' => 'critical',
        ]);
    }

    public function test_compute_overstay_marks_flagged_when_detained_until_half_penalty()
    {
        $facility = Facility::create([
            'name' => 'Test Facility B',
            'region' => 'Region B',
            'address' => '456 Example Ave',
            'capacity' => 100,
        ]);

        $penalty = PenaltyReference::create(['rpc_code' => '001', 'charge_name' => 'Test Charge', 'max_penalty_years' => 2, 'max_penalty_months' => 0, 'law_source' => 'RPC']);
        $user = User::factory()->create();

        $detainee = Detainee::create([
            'full_name' => 'Halfway Detainee',
            'charge_description' => 'Test charge',
            'charge_rpc_code' => $penalty->id,
            'commitment_date' => now()->subDays(400),
            'facility_id' => $facility->id,
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        $service = $this->app->make(PhaseComplianceService::class);
        $computation = $service->computeOverstay($detainee);

        $this->assertSame('flagged', $computation->alert_level);
        $this->assertDatabaseHas('alerts', [
            'detainee_id' => $detainee->id,
            'alert_level' => 'flagged',
        ]);
    }
}
