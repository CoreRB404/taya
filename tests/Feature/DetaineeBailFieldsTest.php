<?php

namespace Tests\Feature;

use App\Models\Detainee;
use App\Models\Facility;
use App\Models\PenaltyReference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DetaineeBailFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_detainee_with_bail_fields()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $facility = Facility::create([
            'name' => 'Investment Facility',
            'region' => 'Region 1',
            'address' => '1 Main St',
            'capacity' => 100,
        ]);
        $penalty = PenaltyReference::create([
            'rpc_code' => '003',
            'charge_name' => 'Test Charge',
            'max_penalty_years' => 1,
            'max_penalty_months' => 0,
            'law_source' => 'RPC',
        ]);

        $response = $this->actingAs($admin)->post(route('detainees.store'), [
            'full_name' => 'Bail Feature Test',
            'charge_description' => 'Test description',
            'charge_rpc_code' => $penalty->id,
            'commitment_date' => now()->subDay()->format('Y-m-d'),
            'facility_id' => $facility->id,
            'bail_amount' => 45000,
            'bail_status' => 'pending_review',
            'bail_posted_at' => now()->subDay()->format('Y-m-d'),
            'bail_notes' => 'Requires special bail assessment.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('detainees', [
            'full_name' => 'Bail Feature Test',
            'bail_amount' => 45000,
            'bail_status' => 'pending_review',
        ]);
    }

    public function test_updating_detainee_recomputes_overstay_details()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $facility = Facility::create([
            'name' => 'Update Facility',
            'region' => 'Region 1',
            'address' => '2 Main St',
            'capacity' => 50,
        ]);
        $penalty = PenaltyReference::create([
            'rpc_code' => '004',
            'charge_name' => 'Updated Charge',
            'max_penalty_years' => 1,
            'max_penalty_months' => 0,
            'law_source' => 'RPC',
        ]);
        $detainee = Detainee::create([
            'full_name' => 'Update Test',
            'charge_description' => 'Old description',
            'charge_rpc_code' => $penalty->id,
            'commitment_date' => now()->subDays(100)->format('Y-m-d'),
            'facility_id' => $facility->id,
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->put(route('detainees.update', $detainee), [
            'full_name' => 'Update Test',
            'charge_description' => 'New description',
            'charge_rpc_code' => $penalty->id,
            'commitment_date' => now()->subDays(100)->format('Y-m-d'),
            'facility_id' => $facility->id,
            'bail_amount' => 10000,
            'bail_status' => 'posted',
            'bail_posted_at' => now()->subDay()->format('Y-m-d'),
            'bail_notes' => 'Updated notes',
        ]);

        $response->assertRedirect(route('detainees.show', $detainee));
        $detainee->refresh();

        $this->assertSame('New description', $detainee->charge_description);
        $this->assertDatabaseHas('overstay_computations', ['detainee_id' => $detainee->id]);
        $this->assertDatabaseHas('alerts', ['detainee_id' => $detainee->id]);
    }

    public function test_updating_commitment_date_reschedules_phase_due_dates()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $facility = Facility::create([
            'name' => 'Schedule Facility',
            'region' => 'Region 1',
            'address' => '3 Main St',
            'capacity' => 60,
        ]);
        $penalty = PenaltyReference::create([
            'rpc_code' => '005',
            'charge_name' => 'Schedule Charge',
            'max_penalty_years' => 1,
            'max_penalty_months' => 0,
            'law_source' => 'RPC',
        ]);
        $originalCommitment = now()->subYear()->startOfDay();
        $updatedCommitment = now()->startOfDay();

        $detainee = Detainee::create([
            'full_name' => 'Schedule Test',
            'charge_description' => 'Schedule description',
            'charge_rpc_code' => $penalty->id,
            'commitment_date' => $originalCommitment->format('Y-m-d'),
            'facility_id' => $facility->id,
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        app(\App\Services\PhaseComplianceService::class)->initializePhases($detainee);

        $firstPhase = $detainee->phases()->where('phase_number', 1)->first();
        $this->assertSame($originalCommitment->copy()->addDays(15)->format('Y-m-d'), $firstPhase->due_date->format('Y-m-d'));

        $response = $this->actingAs($admin)->put(route('detainees.update', $detainee), [
            'full_name' => 'Schedule Test',
            'charge_description' => 'Schedule description',
            'charge_rpc_code' => $penalty->id,
            'commitment_date' => $updatedCommitment->format('Y-m-d'),
            'facility_id' => $facility->id,
            'bail_amount' => null,
            'bail_status' => 'not_posted',
            'bail_posted_at' => null,
            'bail_notes' => null,
        ]);

        $detainee->refresh();
        $rescheduledFirstPhase = $detainee->phases()->where('phase_number', 1)->first();

        $this->assertSame($updatedCommitment->copy()->addDays(15)->format('Y-m-d'), $rescheduledFirstPhase->due_date->format('Y-m-d'));
    }
}
