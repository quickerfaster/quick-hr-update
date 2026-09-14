<?php

namespace App\Modules\Leave\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Modules\Leave\Services\LeaveAccrualService;
use Illuminate\Support\Facades\Log;

class ProcessMonthlyAccrual implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maximum execution time. Monthly accrual may process all active
     * employees, so this matches the payroll dispatcher timeout.
     */
    public $timeout = 300;

    /**
     * Allow up to 3 attempts. Transient failures (DB timeout, brief
     * connection loss) are common on shared hosting.
     */
    public $tries = 3;

    public function __construct(
        public int $month,
        public int $year,
    ) {}

    public function handle(LeaveAccrualService $service): void
    {
        Log::info("Starting monthly leave accrual for {$this->year}-{$this->month}");

        $result = $service->runMonthlyAccrual($this->month, $this->year);

        Log::info("Monthly leave accrual complete", $result);
    }

    /**
     * Handle a job failure (called when all retries are exhausted).
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical("ProcessMonthlyAccrual permanently failed for {$this->year}-{$this->month}", [
            'error' => $exception->getMessage(),
            'error_class' => get_class($exception),
        ]);
    }
}
