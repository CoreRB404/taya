<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleUserSeeder extends Seeder
{
    public function run(): void
    {
        $manilaCityJailId = Facility::where('name', 'Manila City Jail')->value('id');

        $users = [
            ['name' => 'BJMP Staff Officer', 'email' => 'bjmp@taya.gov.ph', 'role' => 'staff', 'facility_id' => $manilaCityJailId],
            ['name' => 'PAO Atty. Maria Santos', 'email' => 'pao@taya.gov.ph', 'role' => 'lawyer', 'facility_id' => null],
            ['name' => 'NGO Atty. Juan Cruz', 'email' => 'ngo@taya.gov.ph', 'role' => 'lawyer', 'facility_id' => null],
            ['name' => 'Court Administrator', 'email' => 'court@taya.gov.ph', 'role' => 'auditor', 'facility_id' => null],
            ['name' => 'Policy Advocate', 'email' => 'policy@taya.gov.ph', 'role' => 'auditor', 'facility_id' => null],
        ];

        foreach ($users as $data) {
            User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make(bin2hex(random_bytes(32))),
                    'role' => $data['role'],
                    'facility_id' => $data['facility_id'],
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
