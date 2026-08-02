<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\SampleDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SampleDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_sample_data_is_loaded_only_once(): void
    {
        User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->seed(SampleDataSeeder::class);

        $tables = [
            'facilities',
            'penalty_references',
            'users',
            'detainees',
            'detainee_phases',
            'overstay_computations',
            'alerts',
            'legal_actions',
        ];

        $firstRunCounts = collect($tables)
            ->mapWithKeys(fn (string $table): array => [$table => DB::table($table)->count()])
            ->all();

        $this->assertSame(5, $firstRunCounts['facilities']);
        $this->assertSame(6, $firstRunCounts['users']);
        $this->assertSame(27, $firstRunCounts['detainees']);
        $this->assertSame(108, $firstRunCounts['detainee_phases']);
        $this->assertNotNull(
            DB::table('data_seed_runs')->where('key', 'taya-sample-data-v1')->value('completed_at')
        );

        $this->seed(SampleDataSeeder::class);

        foreach ($firstRunCounts as $table => $count) {
            $this->assertSame($count, DB::table($table)->count(), "{$table} was duplicated");
        }
    }
}
