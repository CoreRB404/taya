<?php

namespace Tests\Unit;

use App\Models\Detainee;
use App\Models\Facility;
use App\Models\PenaltyReference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DetaineeBailTest extends TestCase
{
    use RefreshDatabase;

    public function test_bail_fields_are_saved_and_displayed()
    {
        $facility = Facility::create([
            'name' => 'Bail Facility',
            'region' => 'Test Region',
            'address' => '300 Test St',
            'capacity' => 100,
        ]);

        $penalty = PenaltyReference::create([
            'rpc_code' => '002',
            'charge_name' => 'Test Charge',
            'max_penalty_years' => 1,
            'max_penalty_months' => 0,
            'law_source' => 'RPC',
        ]);

        $user = User::factory()->create();

        $detainee = Detainee::create([
            'full_name' => 'Bail Test',
            'charge_description' => 'Test offense',
            'charge_rpc_code' => $penalty->id,
            'commitment_date' => now()->subDays(1),
            'facility_id' => $facility->id,
            'status' => 'active',
            'created_by' => $user->id,
            'bail_amount' => 75000,
            'bail_status' => 'unable_to_pay',
            'bail_posted_at' => now()->subDay(),
            'bail_notes' => 'Cannot afford bail, needs indigent assistance.',
        ]);

        $this->assertSame('₱75,000', $detainee->bail_amount_display);
        $this->assertSame('Unable to pay', $detainee->bail_status_label);
        $this->assertSame('Cannot afford bail, needs indigent assistance.', $detainee->bail_notes);
        $this->assertDatabaseHas('detainees', ['id' => $detainee->id, 'bail_status' => 'unable_to_pay']);
    }
}
