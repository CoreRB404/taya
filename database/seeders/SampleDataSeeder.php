<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SampleDataSeeder extends Seeder
{
    private const SEED_KEY = 'taya-sample-data-v1';

    public function run(): void
    {
        DB::transaction(function (): void {
            $claimed = DB::table('data_seed_runs')->insertOrIgnore([
                'key' => self::SEED_KEY,
                'completed_at' => null,
            ]);

            if ($claimed === 0) {
                $this->command?->info('TAYA sample data is already loaded; skipping.');

                return;
            }

            if (! User::where('role', 'admin')->exists()) {
                throw new \RuntimeException('Create the admin user before loading TAYA sample data.');
            }

            $this->call([
                FacilitySeeder::class,
                PenaltyReferenceSeeder::class,
                SampleUserSeeder::class,
                DetaineeSeeder::class,
            ]);

            DB::table('data_seed_runs')
                ->where('key', self::SEED_KEY)
                ->update(['completed_at' => now()]);
        });
    }
}
