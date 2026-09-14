<?php

namespace App\Modules\Leave\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Leave\Jobs\ProcessMonthlyAccrual;

class AccrueLeaveCommand extends Command
{
    protected $signature = 'leave:accrue {--month= : Specific month (1-12), defaults to current} {--year= : Specific year, defaults to current}';
    protected $description = 'Dispatch monthly leave balance accrual job for all employees';

    public function handle(): int
    {
        $month = $this->option('month') ? (int) $this->option('month') : now()->month;
        $year = $this->option('year') ? (int) $this->option('year') : now()->year;

        $this->info("Dispatching leave accrual job for {$year}-{$month}...");

        ProcessMonthlyAccrual::dispatch($month, $year);

        $this->info("Job dispatched. Check logs for results.");

        return Command::SUCCESS;
    }
}
