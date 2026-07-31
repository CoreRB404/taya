<?php

namespace Tests\Unit;

use App\Models\Facility;
use App\Models\Detainee;
use App\Models\PenaltyReference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacilityOccupancyTest extends TestCase
{
    use RefreshDatabase;

    public function test_occupancy_percentage_is_computed_correctly()
    {
        $facility = Facility::create([
            'name' => 'Test Facility',
            'region' => 'Test Region',
            'address' => '100 Test St',
            'capacity' => 100,
        ]);

        $penalty = PenaltyReference::create([
            'rpc_code' => '000',
            'charge_name' => 'Test Charge',
            'max_penalty_years' => 1,
            'max_penalty_months' => 0,
            'law_source' => 'RPC',
        ]);

        $user = User::factory()->create();

        for ($i = 0; $i < 85; $i++) {
            Detainee::create([
                'full_name' => "Test Detainee {$i}",
                'charge_description' => 'Test charge',
                'charge_rpc_code' => $penalty->id,
                'commitment_date' => now()->subDays(10),
                'facility_id' => $facility->id,
                'status' => 'active',
                'created_by' => $user->id,
            ]);
        }

        $facility->refresh();

        $this->assertSame(85.0, $facility->occupancy_percentage);
        $this->assertFalse($facility->is_over_capacity);
    }

    public function test_over_capacity_returns_true_when_active_detainees_exceed_capacity()
    {
        $facility = Facility::create([
            'name' => 'Crowded Facility',
            'region' => 'Test Region',
            'address' => '200 Test St',
            'capacity' => 10,
        ]);

        $penalty = PenaltyReference::create([
            'rpc_code' => '001',
            'charge_name' => 'Test Charge',
            'max_penalty_years' => 1,
            'max_penalty_months' => 0,
            'law_source' => 'RPC',
        ]);

        $user = User::factory()->create();

        for ($i = 0; $i < 12; $i++) {
            Detainee::create([
                'full_name' => "Overcrowded Detainee {$i}",
                'charge_description' => 'Test charge',
                'charge_rpc_code' => $penalty->id,
                'commitment_date' => now()->subDays(5),
                'facility_id' => $facility->id,
                'status' => 'active',
                'created_by' => $user->id,
            ]);
        }

        $facility->refresh();

        $this->assertTrue($facility->is_over_capacity);
        $this->assertSame(100.0, $facility->occupancy_percentage);
    }
}
