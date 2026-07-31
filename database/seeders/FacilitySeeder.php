<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $facilities = [
            [
                'name' => 'Manila City Jail',
                'region' => 'NCR',
                'address' => 'Bilibid Viejo, Manila',
                'capacity' => 800,
            ],
            [
                'name' => 'Quezon City Jail',
                'region' => 'NCR',
                'address' => 'Camp Karingal, Quezon City',
                'capacity' => 1200,
            ],
            [
                'name' => 'Cebu City Jail',
                'region' => 'Region VII',
                'address' => 'Cebu City, Cebu',
                'capacity' => 600,
            ],
            [
                'name' => 'Davao City Jail',
                'region' => 'Region XI',
                'address' => 'Ma-a, Davao City',
                'capacity' => 500,
            ],
            [
                'name' => 'Taguig City Jail',
                'region' => 'NCR',
                'address' => 'Bicutan, Taguig City',
                'capacity' => 700,
            ],
        ];

        foreach ($facilities as $facility) {
            Facility::create($facility);
        }
    }
}
