<?php

namespace App\Console\Commands;

use App\Models\Detainee;
use App\Services\AuditService;
use App\Services\PhaseComplianceService;
use Illuminate\Console\Command;

class RecomputeOverstay extends Command
{
    protected $signature = 'taya:recompute-overstay';
    protected $description = 'Recompute overstay and refresh alert levels for all active detainees';

    public function handle(PhaseComplianceService $service): int
    {
        $detainees = Detainee::where('status', 'active')->get();

        $this->info("Recomputing overstay for {$detainees->count()} active detainees...");

        $bar = $this->output->createProgressBar($detainees->count());

        foreach ($detainees as $detainee) {
            $service->computeOverstay($detainee);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Overstay recomputation complete.');

        AuditService::log(
            'scheduled_recompute_overstay',
            "Scheduled job recomputed overstay for {$detainees->count()} detainees",
            null,
            null
        );

        return Command::SUCCESS;
    }
}
