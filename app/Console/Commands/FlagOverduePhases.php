<?php

namespace App\Console\Commands;

use App\Services\AuditService;
use App\Services\PhaseComplianceService;
use Illuminate\Console\Command;

class FlagOverduePhases extends Command
{
    protected $signature = 'taya:flag-overdue-phases';
    protected $description = 'Flag all overdue compliance phases for active detainees';

    public function handle(PhaseComplianceService $service): int
    {
        $this->info('Flagging overdue phases...');

        $count = $service->flagOverduePhases();

        $this->info("Flagged {$count} overdue phases.");

        AuditService::log(
            'scheduled_flag_overdue',
            "Scheduled job flagged {$count} overdue phases",
            null,
            null
        );

        return Command::SUCCESS;
    }
}
