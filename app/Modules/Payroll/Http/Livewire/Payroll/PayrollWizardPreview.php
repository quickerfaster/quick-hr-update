<?php

namespace App\Modules\Payroll\Http\Livewire\Payroll;

use Livewire\Component;
use Livewire\WithPagination;
use App\Modules\Payroll\Models\PayrollRun;
use App\Modules\Payroll\Models\PayrollPayslip;
use App\Modules\Hr\Models\EmployeePosition;
use App\Modules\Payroll\Models\PayrollRunProgress;
use App\Modules\Payroll\Services\Payroll\PayrollCalculator;
use QuickerFaster\UILibrary\Traits\HasCurrencySymbol;
use App\Modules\Payroll\Jobs\Payrolls\ProcessPayrollRun;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Modules\Hr\Models\Employee;
use Illuminate\Support\Facades\Cache;



use App\Modules\Hr\Models\Company;
use App\Modules\Hr\Models\Department;
use App\Modules\Hr\Models\Location;

class PayrollWizardPreview extends Component
{
    use WithPagination;
    use HasCurrencySymbol;

    protected $paginationTheme = 'bootstrap';

    public int $stepIndex;
    public int $payrollRunId;
    public array $previewData = [];
    public ?int $expandedPayslipId = null;
    public array $lazyItemsCache = [];

    // Filters & Search
    public ?int $filterCompany = null;
    public ?int $filterDepartment = null;
    public ?int $filterLocation = null;
    public ?string $filterEmploymentStatus = 'Active';
    public string $search = '';

    // Progress tracking
    public string $calculationStatus = 'pending';
    public int $progress = 0;
    public int $totalEmployees = 0;
    public int $processedEmployees = 0;
    public bool $isPolling = false;
    public bool $processingStartedEventSent = false;
    public bool $isFinalized = false;

    // Sorting properties
    public string $sortField = 'employee_name';
    public string $sortDirection = 'asc';


    protected $listeners = [
        'refreshPreview' => 'loadPreviewData',
        'savePreview' => 'save',
    ];

public function mount(int $stepIndex, int $payrollRunId): void
{
    $this->stepIndex = $stepIndex;
    $this->payrollRunId = $payrollRunId;

    $run = PayrollRun::withoutCompanyScope()->find($payrollRunId);
    if (!$run) {
        session()->forget('payroll-wizard-' . auth()->id());
        $this->redirectRoute('payroll-runs.create', ['error' => 'The payroll run was not found. Please start again.']);
        return;
    }

    if (!$run->is_multi_company) {
        $this->filterCompany = $run->company_id;
    }

    $this->loadPreviewData();
}

public function loadPreviewData(): void
{
    $run = PayrollRun::withoutCompanyScope()->find($this->payrollRunId);
    if (!$run) {
        $this->redirectRoute('payroll-runs.create', ['error' => 'Payroll run not found.']);
        return;
    }

    $this->calculationStatus = $run->calculation_status ?? 'pending';
    $this->isFinalized = $run->finalized_at !== null;

    // Try PayrollRunProgress first, fall back to payroll_runs
    $progress = \App\Modules\Payroll\Models\PayrollRunProgress::withoutCompanyScope()
        ->where('payroll_run_id', $this->payrollRunId)
        ->first();

    if ($progress) {
        $this->totalEmployees = $progress->total_employees;
        $this->processedEmployees = $progress->processed_employees;
    } else {
        $this->totalEmployees = $run->total_employees ?? 0;
        $this->processedEmployees = $run->processed_employees ?? 0;
    }

    if ($this->totalEmployees > 0) {
        $this->progress = round(($this->processedEmployees / $this->totalEmployees) * 100);
    }

    if ($this->calculationStatus === 'completed' || $this->calculationStatus === 'finalized') {
        $this->loadCalculatedData();
        $this->isPolling = false;
    } elseif ($this->calculationStatus === 'failed') {
        $this->isPolling = false;
        $this->dispatch('showAlert', ['type' => 'error', 'message' => 'Calculation failed.']);
    } elseif ($this->calculationStatus === 'pending' || $this->calculationStatus === 'processing') {
        // Dispatch the job if still pending
        if ($this->calculationStatus === 'pending') {
            // Use a cache lock to prevent duplicate dispatches across
            // page refreshes, browser tabs, and Livewire reconnects.
            // The lock auto-expires after 60s (long enough for the job
            // to be picked up by the cron worker).
            $lockKey = "payroll-dispatch-{$this->payrollRunId}";
            $lock = Cache::lock($lockKey, 60);

            if ($lock->get()) {
                // We hold the lock — safe to dispatch
                $this->dispatchJob();
                $this->calculationStatus = 'processing';
                $this->isPolling = true;
                Log::info("Payroll run #{$this->payrollRunId}: Dispatched (lock acquired).");
            } else {
                // Another request already dispatched this run
                Log::info("Payroll run #{$this->payrollRunId}: Job already dispatched (lock held by another request).");
                $this->calculationStatus = 'processing';
                $this->isPolling = true;
            }
        } else {
            // Already processing
            $this->calculationStatus = 'processing';
            $this->isPolling = true;
        }

        // After dispatchJob() call, ensure we have sane defaults
        if ($this->totalEmployees === 0 && $this->calculationStatus === 'processing') {
            // Job hasn't run yet, set a placeholder
            $this->totalEmployees = 0;
            $this->processedEmployees = 0;
        }
    }
}

/**
 * Dispatch the payroll job only if the run is still pending.
 */
protected function dispatchJob(): void
{
    if ($this->isPolling || $this->calculationStatus === 'processing') {
        return; // Already dispatched, ignore duplicate calls
    }

    DB::transaction(function () {
        $run = PayrollRun::withoutCompanyScope()
            ->where('id', $this->payrollRunId)
            ->lockForUpdate()
            ->first();

        if (!$run) {
            return;
        }

        // Only dispatch if pending – avoids double-dispatch
        if ($run->calculation_status === 'pending') {
            \App\Modules\Payroll\Jobs\Payrolls\ProcessPayrollRun::dispatch($run);
            Log::info("Dispatched ProcessPayrollRun for run #{$run->id}");
        } else {
            Log::info("Run #{$run->id} already in status: {$run->calculation_status}. Not dispatching.");
        }
    });
}



    public function sortBy($field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }




protected function startCalculation(): void
{
    // Use DB transaction with row lock to prevent race conditions
    DB::transaction(function () {
        $run = PayrollRun::withoutCompanyScope()
            ->where('id', $this->payrollRunId)
            ->lockForUpdate()
            ->first();

        if (!$run) {
            return;
        }

        // Only dispatch if the run is still pending.
        if ($run->calculation_status === 'pending') {
            // Do NOT change status here – let the job handle it.
            ProcessPayrollRun::dispatch($run);
            Log::info("Dispatched ProcessPayrollRun for run #{$run->id}");
        } else {
            Log::info("Run #{$run->id} already in status: {$run->calculation_status}. Not dispatching.");
        }
    });

    // Immediately set UI state to processing so the progress card appears.
    $this->calculationStatus = 'processing';
    $this->isPolling = true;

    // Optionally load current progress (will be 0 initially, but that's fine).
    $this->loadProgressData();
}

/**
 * Helper to load progress data from the database (already used in checkCalculationStatus).
 */
protected function loadProgressData(): void
{
    $progress = \App\Modules\Payroll\Models\PayrollRunProgress::withoutCompanyScope()
        ->where('payroll_run_id', $this->payrollRunId)
        ->first();

    if ($progress) {
        $this->totalEmployees = $progress->total_employees ?? 0;
        $this->processedEmployees = $progress->processed_employees ?? 0;
        // Normalize 'finalized' to 'completed' for UI consistency
        $status = $progress->status ?? $this->calculationStatus;
        $this->calculationStatus = $status === 'finalized' ? 'completed' : $status;
        if ($this->totalEmployees > 0) {
            $this->progress = round(($this->processedEmployees / $this->totalEmployees) * 100);
        }
    }
}

    protected function loadCalculatedData(): void
    {
        $run = PayrollRun::withoutCompanyScope()->find($this->payrollRunId);
        if (!$run) {
            $this->redirectRoute('payroll-runs.create', ['error' => 'Payroll run not found.']);
            return;
        }

        $this->previewData = [
            'period_start' => $run->period_start->format('Y-m-d'),
            'period_end' => $run->period_end->format('Y-m-d'),
            'total_cash_required' => $run->total_cash_required,
        ];

        $this->isPolling = false;
        $this->dispatch('refreshPayslips');
    }

    public function checkCalculationStatus(): void
    {
        // 1. Try reading from PayrollRunProgress
        $progress = PayrollRunProgress::withoutCompanyScope()
            ->where('payroll_run_id', $this->payrollRunId)
            ->first();

        if ($progress) {
            $this->totalEmployees = (int) $progress->total_employees;
            $this->processedEmployees = (int) $progress->processed_employees;

            if (in_array($progress->status, ['completed', 'finalized', 'failed'], true)) {
                $this->isPolling = false;
                $this->calculationStatus = $progress->status;

                if ($progress->status === 'completed' || $progress->status === 'finalized') {
                    $this->loadCalculatedData();
                    $this->dispatch('processingFinished');
                } else {
                    session()->flash('error', 'Payroll calculation failed. Please try again.');
                    $this->dispatch('processingFinished');
                }
                return;
            }
        }

        // 2. Fallback to payroll_runs table
        $run = PayrollRun::withoutCompanyScope()->find($this->payrollRunId);

        if (!$run) {
            $this->isPolling = false;
            return;
        }

        $this->calculationStatus = $run->calculation_status;

        if ($run->calculation_status === 'completed') {
            $this->isPolling = false;
            $this->processedEmployees = $this->totalEmployees;
            $this->loadCalculatedData();
            $this->dispatch('processingFinished');
            return;
        }

        if ($run->calculation_status === 'failed') {
            $this->isPolling = false;
            session()->flash('error', 'Payroll calculation failed.');
            $this->dispatch('processingFinished');
            return;
        }

        // 3. If still processing but no progress record yet, show "waiting" state
        if (!$progress && $run->calculation_status === 'processing') {
            $this->totalEmployees = max($this->totalEmployees, 0);
            $this->processedEmployees = max($this->processedEmployees, 0);
            // The progress bar will show 0% but we display a friendly message in the view
        }

        // 4. If stuck at 'pending' for too long, the job might not have been picked up
        if ($run->calculation_status === 'pending') {
            $secondsSinceUpdate = $run->updated_at->diffInSeconds(now());
            if ($secondsSinceUpdate > 120) {
                Log::warning("Payroll run #{$this->payrollRunId}: Stuck in 'pending' state for {$secondsSinceUpdate}s");
                // Don't dispatch again - just show a message
            }
        }

        // Update progress percentage
        if ($this->totalEmployees > 0) {
            $this->progress = round(($this->processedEmployees / $this->totalEmployees) * 100);
        }

        // Check if finalized
        $this->isFinalized = $run->finalized_at !== null;

        if ($this->progress && $this->progress > 0 && $this->calculationStatus !== 'completed') {
            $this->dispatch('processingStarted');
        }
    }

public function getPayslipsProperty()
{
    // Show payslips when calculation is completed OR when all employees have been
    // processed (belt-and-suspenders: payslips appear immediately even if the
    // status hasn't flipped yet due to FinalizePayrollRun's delay).
    $allProcessed = $this->totalEmployees > 0 && $this->processedEmployees >= $this->totalEmployees;
    if (!in_array($this->calculationStatus, ['completed', 'finalized'], true) && !$allProcessed) {
        return collect();
    }

    $run = PayrollRun::withoutCompanyScope()->find($this->payrollRunId);
    if (!$run) {
        return collect();
    }

    $query = PayrollPayslip::withoutCompanyScope()
        ->join('employees', 'payroll_payslips.employee_id', '=', 'employees.id')
        ->leftJoin('employee_positions', 'employees.id', '=', 'employee_positions.employee_id')
        ->where('payroll_payslips.payroll_run_id', $this->payrollRunId)
        ->select('payroll_payslips.*');

    // Company filter – use run's is_multi_company flag
    if ($run->is_multi_company) {
        if ($this->filterCompany) {
            $query->where('employees.company_id', $this->filterCompany);
        }
        // else show all companies
    } else {
        $query->where('employees.company_id', $run->company_id);
    }

    // Search filter
    if (!empty($this->search)) {
        $searchTerm = '%' . $this->search . '%';
        $query->where(function ($q) use ($searchTerm) {
            $q->where('employees.first_name', 'like', $searchTerm)
              ->orWhere('employees.last_name', 'like', $searchTerm)
              ->orWhere('employees.employee_number', 'like', $searchTerm);
        });
    }

    // Department filter
    if ($this->filterDepartment) {
        $query->where('employee_positions.department_id', $this->filterDepartment);
    }

    // Location filter
    if ($this->filterLocation) {
        $query->where('employee_positions.location_id', $this->filterLocation);
    }

    // Employment status filter
    if ($this->filterEmploymentStatus === 'On Leave') {
        $query->where('employee_positions.employment_status', 'On Leave');
    } elseif ($this->filterEmploymentStatus === 'Terminated') {
        $query->where('employee_positions.employment_status', 'Terminated');
    } elseif ($this->filterEmploymentStatus === 'All') {
        $query->whereIn('employee_positions.employment_status', ['Active', 'On Leave', 'Terminated']);
    } else {
        // Default: 'Active'
        $query->where('employee_positions.employment_status', 'Active');
    }

    // Sorting
    if ($this->sortField === 'employee_name') {
        $query->orderBy('employees.first_name', $this->sortDirection)
              ->orderBy('employees.last_name', $this->sortDirection);
    } elseif (in_array($this->sortField, ['gross_pay', 'total_deductions', 'net_pay'])) {
        $query->orderBy('payroll_payslips.' . $this->sortField, $this->sortDirection);
    }

    return $query->paginate(50);
}

    public function toggleDetails($payslipId): void
    {
        $allProcessed = $this->totalEmployees > 0 && $this->processedEmployees >= $this->totalEmployees;
        if ($this->calculationStatus !== 'completed' && !$allProcessed)
            return;

        if ($this->expandedPayslipId === $payslipId) {
            $this->expandedPayslipId = null;
        } else {
            $this->expandedPayslipId = $payslipId;
            if (!isset($this->lazyItemsCache[$payslipId])) {
                $payslip = PayrollPayslip::withoutCompanyScope()->with('items')->find($payslipId);
                $this->lazyItemsCache[$payslipId] = $payslip ? $payslip->items : collect();
            }
        }
    }

    // Filter update methods (reset pagination and expanded state)
    public function updatedFilterCompany(): void
    {
        $this->resetPage();
        $this->expandedPayslipId = null;
    }
    public function updatedFilterDepartment(): void
    {
        $this->resetPage();
        $this->expandedPayslipId = null;
    }
    public function updatedFilterLocation(): void
    {
        $this->resetPage();
        $this->expandedPayslipId = null;
    }
    public function updatedFilterEmploymentStatus(): void
    {
        $this->resetPage();
        $this->expandedPayslipId = null;
    }
    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->expandedPayslipId = null;
    }

    public function resetFilters(): void
    {
        $this->filterCompany = null;
        $this->filterDepartment = null;
        $this->filterLocation = null;
        $this->filterEmploymentStatus = 'Active';
        $this->search = '';
        $this->resetPage();
        $this->expandedPayslipId = null;

    }

    public function save(): void
    {
        $allProcessed = $this->totalEmployees > 0 && $this->processedEmployees >= $this->totalEmployees;
        if ($this->calculationStatus !== 'completed' && !$allProcessed) {
            $this->dispatch('showAlert', ['type' => 'warning', 'message' => 'Please wait for calculation to complete.']);
            return;
        }
        $this->dispatch('previewComplete');
    }



    public function retryCalculation(): void
    {
        if ($this->isPolling || $this->calculationStatus === 'processing') {
            return; // Already processing, ignore duplicate clicks
        }

        $run = PayrollRun::withoutCompanyScope()->find($this->payrollRunId);
        if (!$run) {
            $this->redirectRoute('payroll-runs.create', ['error' => 'Payroll run not found.']);
            return;
        }

        // Reset status to pending
        $run->update([
            'calculation_status' => 'pending',
            'failed_at' => null,
            'failure_reason' => null,
        ]);

        // Reload the preview (which will start a new job)
        $this->loadPreviewData();
    }



    /**
     * Check if the user is in "All Companies" mode (session company_id = 0).
     */
    public function isAllCompaniesMode(): bool
    {
        return session('current_company_id') === 0;
    }

    /**
     * Computed property: current company name for the static label.
     */
    public function getCurrentCompanyNameProperty(): string
    {
        $companyId = session('current_company_id');
        if (!$companyId || $companyId === 0) {
            return 'All Companies';
        }
        $company = \App\Modules\Hr\Models\Company::find($companyId);
        return $company?->name ?? 'Unknown Company';
    }



public function render()
{
    $run = PayrollRun::withoutCompanyScope()->find($this->payrollRunId);
    if (!$run) {
        $this->redirectRoute('payroll-runs.create', ['error' => 'Payroll run not found.']);
        return view('hr::livewire.payroll.wizard-preview', [
            'payslips'           => collect(),
            'companies'          => collect(),
            'departments'        => collect(),
            'locations'          => collect(),
            'search'             => $this->search,
            'calculationStatus'  => $this->calculationStatus,
            'progress'           => $this->progress,
            'totalEmployees'     => $this->totalEmployees,
            'processedEmployees' => $this->processedEmployees,
            'isPolling'          => $this->isPolling,
            'previewData'        => $this->previewData,
            'isMultiCompany'     => false,
            'companyName'        => null,
        ]);
    }

    // Get employee IDs from payslips (they already belong to the run)
    $employeeIds = PayrollPayslip::withoutCompanyScope()
        ->where('payroll_run_id', $this->payrollRunId)
        ->pluck('employee_id')
        ->unique();

    // Companies with payslips in this run
    $companies = collect();
    if ($employeeIds->isNotEmpty()) {
        $companyIds = Employee::withoutCompanyScope()
            ->whereIn('id', $employeeIds)
            ->whereNotNull('company_id')
            ->pluck('company_id')
            ->unique();
        if ($companyIds->isNotEmpty()) {
            $companies = Company::whereIn('id', $companyIds)->get();
        }
    }

    // Departments and locations (from employee positions of those employees)
    $departments = collect();
    $locations = collect();
    if ($employeeIds->isNotEmpty()) {
        $positions = Employee::withoutCompanyScope()
            ->whereIn('id', $employeeIds)
            ->with('employeePosition')
            ->get()
            ->pluck('employeePosition')
            ->filter();

        $departmentIds = $positions->pluck('department_id')->unique()->filter();
        $locationIds   = $positions->pluck('location_id')->unique()->filter();

        if ($departmentIds->isNotEmpty()) {
            $departments = Department::whereIn('id', $departmentIds)->get();
        }
        if ($locationIds->isNotEmpty()) {
            $locations = Location::whereIn('id', $locationIds)->get();
        }
    }

    // Single‑company label
    $companyName = null;
    if (!$run->is_multi_company && $run->company_id) {
        $company = Company::find($run->company_id);
        $companyName = $company ? $company->name : 'Unknown Company';
    }

    return view('hr::livewire.payroll.wizard-preview', [
        'payslips'           => $this->payslips,
        'companies'          => $companies,
        'departments'        => $departments,
        'locations'          => $locations,
        'search'             => $this->search,
        'calculationStatus'  => $this->calculationStatus,
        'progress'           => $this->progress,
        'totalEmployees'     => $this->totalEmployees,
        'processedEmployees' => $this->processedEmployees,
        'isPolling'          => $this->isPolling,
        'previewData'        => $this->previewData,
        'isMultiCompany'     => $run->is_multi_company,
        'companyName'        => $companyName,
        'isFinalized'        => $this->isFinalized,
    ]);
}

}
