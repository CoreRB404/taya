<?php

namespace Tests\Feature;

use App\Models\Detainee;
use App\Models\Facility;
use App\Models\PenaltyReference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_auditor_is_read_only(): void
    {
        $auditor = User::factory()->create(['role' => 'auditor']);

        $this->actingAs($auditor)->get(route('detainees.create'))->assertForbidden();
        $this->actingAs($auditor)->get(route('admin.audit-logs.index'))->assertOk();
        $this->actingAs($auditor)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_staff_cannot_view_another_facility_record(): void
    {
        $ownFacility = Facility::create(['name' => 'Own', 'region' => 'R1', 'address' => 'A', 'capacity' => 10]);
        $otherFacility = Facility::create(['name' => 'Other', 'region' => 'R2', 'address' => 'B', 'capacity' => 10]);
        $staff = User::factory()->create(['role' => 'staff', 'facility_id' => $ownFacility->id]);
        $admin = User::factory()->create(['role' => 'admin']);
        $penalty = PenaltyReference::create([
            'rpc_code' => 'TEST-1',
            'charge_name' => 'Test charge',
            'max_penalty_years' => 1,
            'law_source' => 'OTHER',
        ]);
        $detainee = Detainee::create([
            'full_name' => 'Restricted Record',
            'charge_description' => 'Test',
            'charge_rpc_code' => $penalty->id,
            'commitment_date' => now()->subDay(),
            'facility_id' => $otherFacility->id,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($staff)->get(route('detainees.show', $detainee))->assertForbidden();
    }
}
