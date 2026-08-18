<?php

namespace App\Modules\Hr\Jobs;

use App\Modules\Hr\Models\Employee;
use App\Modules\Hr\Services\AttendanceAggregator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAttendanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 600]; // 1 min, 5 min, 10 min

    public function __construct(
        protected int $employeeId,
        protected string $date
    ) {}

    public function handle(AttendanceAggregator $aggregator): void
    {
        $employee = Employee::find($this->employeeId);
        if (!$employee) {
            \Log::error("ProcessAttendanceJob: Employee {$this->employeeId} not found");
            return;
        }
        $aggregator->recalculateForDay($employee->employee_number, $this->date);
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('ProcessAttendanceJob failed', [
            'employee_id' => $this->employeeId,
            'date' => $this->date,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
