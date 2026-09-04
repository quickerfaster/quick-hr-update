<?php

namespace App\Modules\Payroll\Http\Livewire\Payroll;

use Livewire\Component;
use Livewire\WithPagination;
use App\Modules\Payroll\Models\PayrollRun;
use App\Modules\Hr\Models\EmployeePosition;
use App\Modules\Payroll\Models\PayrollRunAdjustment;
use App\Modules\Hr\Models\Department;
use App\Modules\Hr\Models\Location;
use App\Modules\Hr\Models\Company;
use App\Modules\Hr\Models\Employee;
use Illuminate\Support\Facades\DB;
use QuickerFaster\UILibrary\Traits\HasCurrencySymbol;
use Illuminate\Support\Facades\Log;



class PayrollWizardAdjustments extends Component
{
    use WithPagination;
    use HasCurrencySymbol;

    protected $paginationTheme = 'bootstrap';

    public int $stepIndex;
    public int $payrollRunId;
    public array $tempAdjustments = [];
    protected array $existingAdjustmentsCache = [];

    // Filter properties
    public ?int $filterCompany = null;
    public ?int $filterDepartment = null;
    public ?int $filterLocation = null;
    public ?string $filterEmploymentStatus = 'Active';

    // Sorting properties
    public string $sortField = 'employee_name';
    public string $sortDirection = 'asc';

    // Search property
    public string $search = '';

    protected $listeners = [
        'saveAdjustments' => 'save',
        'refreshAdjustments' => '$refresh',
    ];

public function mount(int $stepIndex, int $payrollRunId): void
{
    $this->stepIndex = $stepIndex;
    $this->payrollRunId = $payrollRunId;

    // Reset filters to show all employees
    $this->filterCompany = null;
    $this->filterDepartment = null;
    $this->filterLocation = null;
    $this->filterEmploymentStatus = 'Active';
    $this->search = '';
    $this->sortField = 'employee_name';
    $this->sortDirection = 'asc';

    $run = PayrollRun::withoutCompanyScope()->find($payrollRunId);
    if (!$run) {
        session()->forget('payroll-wizard-' . auth()->id());
        $this->redirectRoute('payroll-runs.create', ['error' => 'Payroll run not found. Please start again.']);
        return;
    }

    $this->loadAdjustmentsCache();
    $this->initializeAllTempAdjustments();
}


    protected function initializeAllTempAdjustments(): void
    {
        // Always use withoutCompanyScope() — the wizard's internal state
        // is the single source of truth, never the global session.
        $run = PayrollRun::withoutCompanyScope()->find($this->payrollRunId);

        if (!$run) {
            $this->redirectRoute('payroll-runs.create', ['error' => 'Payroll run not found.']);
            return;
        }

        $positionQuery = EmployeePosition::withoutCompanyScope();

        // Only filter by pay_schedule_id for single-company runs.
        // Multi-company runs have pay_schedule_id = null and must include
        // employees across all schedules.
        if (!$run->is_multi_company) {
            $positionQuery->where('pay_schedule_id', $run->pay_schedule_id);
        }

        $allEmployeeIds = $positionQuery->pluck('employee_id');

        foreach ($allEmployeeIds as $employeeId) {
            $this->tempAdjustments[$employeeId] = [
                'Bonus' => $this->existingAdjustmentsCache[$employeeId]['Bonus'] ?? 0,
                'Commission' => $this->existingAdjustmentsCache[$employeeId]['Commission'] ?? 0,
                'Correction' => $this->existingAdjustmentsCache[$employeeId]['Correction'] ?? 0,
                'Reimbursement' => $this->existingAdjustmentsCache[$employeeId]['Reimbursement'] ?? 0,
                'Deduction' => $this->existingAdjustmentsCache[$employeeId]['Deduction'] ?? 0,
            ];
        }
    }


    protected function loadAdjustmentsCache(): void
    {
        $adjustments = PayrollRunAdjustment::withoutCompanyScope()
            ->where('payroll_run_id', $this->payrollRunId)
            ->get(['employee_id', 'type', 'amount']);

        foreach ($adjustments as $adj) {
            $this->existingAdjustmentsCache[$adj->employee_id][$adj->type] = $adj->amount;
        }
    }

public function getEmployeesProperty()
{
    $run = PayrollRun::withoutCompanyScope()->find($this->payrollRunId);
    if (!$run) {
        return collect();
    }

    $query = EmployeePosition::withoutCompanyScope()
        ->join('employees', 'employee_positions.employee_id', '=', 'employees.id')
        ->select('employee_positions.*')
        ->where('employee_positions.employment_status', 'Active')
        ->whereNull('employee_positions.deleted_at');

    // Single‑company: filter by pay_schedule_id
    if (!$run->is_multi_company) {
        $query->where('employee_positions.pay_schedule_id', $run->pay_schedule_id);
    }

    // Search
    if (!empty($this->search)) {
        $searchTerm = '%' . $this->search . '%';
        $query->where(function ($q) use ($searchTerm) {
            $q->where('employees.first_name', 'like', $searchTerm)
              ->orWhere('employees.last_name', 'like', $searchTerm)
              ->orWhere('employees.employee_number', 'like', $searchTerm);
        });
    }

    // Employment status filter (if 'All' selected, override the 'Active' default)
    if ($this->filterEmploymentStatus === 'On Leave') {
        $query->where('employee_positions.employment_status', 'On Leave');
    } elseif ($this->filterEmploymentStatus === 'Terminated') {
        $query->where('employee_positions.employment_status', 'Terminated');
    } elseif ($this->filterEmploymentStatus === 'All') {
        $query->whereIn('employee_positions.employment_status', ['Active', 'On Leave', 'Terminated']);
    }

    // Department & Location
    if ($this->filterDepartment) {
        $query->where('employee_positions.department_id', $this->filterDepartment);
    }
    if ($this->filterLocation) {
        $query->where('employee_positions.location_id', $this->filterLocation);
    }

    // Company filter – only for multi‑company runs
    if ($run->is_multi_company && $this->filterCompany) {
        $query->where('employees.company_id', $this->filterCompany);
    }

    // Sorting
    if ($this->sortField === 'employee_name') {
        $query->orderBy('employees.first_name', $this->sortDirection)
              ->orderBy('employees.last_name', $this->sortDirection);
    } elseif ($this->sortField === 'base_salary') {
        $query->orderBy('employee_positions.base_salary', $this->sortDirection);
    }

    return $query->paginate(50);
}

    public function updatedSearch(): void
    {
        $this->resetPage();
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














    public function updatedFilterCompany(): void
    {
        $this->resetPage();
    }

    public function updatedFilterDepartment(): void
    {
        $this->resetPage();
    }

    public function updatedFilterLocation(): void
    {
        $this->resetPage();
    }

    public function updatedFilterEmploymentStatus(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->filterCompany = null;
        $this->filterDepartment = null;
        $this->filterLocation = null;
        $this->filterEmploymentStatus = 'Active';
        $this->resetPage();
    }

public function updatedTempAdjustments($value, $key): void
{
    Log::info('updatedTempAdjustments called', ['key' => $key, 'value' => $value]);

    // Normalize key: remove "tempAdjustments." prefix if present
    if (str_starts_with($key, 'tempAdjustments.')) {
        $key = substr($key, strlen('tempAdjustments.'));
    }

    $parts = explode('.', $key);
    if (count($parts) !== 2) {
        Log::warning('Invalid adjustment key format', ['key' => $key]);
        return;
    }

    $employeeId = (int) $parts[0];
    $type = $parts[1];

    // Validate type
    $allowed = ['Bonus', 'Commission', 'Correction', 'Reimbursement', 'Deduction'];
    if (!in_array($type, $allowed)) {
        Log::warning('Invalid adjustment type', ['type' => $type]);
        return;
    }

    $amount = (float) $value;
    $this->saveAdjustmentForEmployee($employeeId, $type, $amount);
}

protected function saveAdjustmentForEmployee($employeeId, $type, $amount): void
{
    Log::info('Saving adjustment', ['employeeId' => $employeeId, 'type' => $type, 'amount' => $amount]);


    try {
        DB::transaction(function () use ($employeeId, $type, $amount) {
            // Find existing record without company scope
            $adjustment = PayrollRunAdjustment::withoutCompanyScope()
                ->where('payroll_run_id', $this->payrollRunId)
                ->where('employee_id', $employeeId)
                ->where('type', $type)
                ->first();

            if ($amount == 0 && $adjustment) {
                $adjustment->delete();
                unset($this->existingAdjustmentsCache[$employeeId][$type]);
                Log::info('Deleted adjustment', ['id' => $adjustment->id]);
                return;
            }

            if (!$adjustment) {
                $adjustment = new PayrollRunAdjustment();
                $adjustment->payroll_run_id = $this->payrollRunId;
                $adjustment->employee_id = $employeeId;
                $adjustment->type = $type;
                // Set company_id from the employee
                $companyId = Employee::withoutCompanyScope()
                    ->where('id', $employeeId)
                    ->value('company_id');
                $adjustment->company_id = $companyId;
            }

            $adjustment->label = $type;
            $adjustment->amount = $amount;
            $adjustment->save();

            $this->existingAdjustmentsCache[$employeeId][$type] = $amount;
            Log::info('Saved adjustment', ['id' => $adjustment->id, 'company_id' => $adjustment->company_id, 'amount' => $amount]);
        });
    } catch (\Exception $e) {
        Log::error('Failed to save adjustment', [
            'employeeId' => $employeeId,
            'type' => $type,
            'amount' => $amount,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
    }
}




    public function save(): void
    {
        /*foreach ($this->tempAdjustments as $employeeId => $types) {
            foreach ($types as $type => $amount) {
                $this->saveAdjustmentForEmployee($employeeId, $type, (float) $amount);
            }
        }*/
        // All adjustments are already saved individually via wire:model

        // Force recalculation when moving to preview
        $run = PayrollRun::withoutCompanyScope()->find($this->payrollRunId);
        if ($run) {
            $run->update(['calculation_status' => 'pending']);
            // Delete old payslips to ensure clean slate (the job will recreate them)
            \App\Modules\Payroll\Models\PayrollPayslip::withoutCompanyScope()->where('payroll_run_id', $this->payrollRunId)->delete();
        }

        $this->dispatch('adjustmentsComplete');
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
        return view('payroll::livewire.payroll.wizard-adjustments', [
            'employees'        => collect(),
            'companies'        => collect(),
            'departments'      => collect(),
            'locations'        => collect(),
            'sortField'        => $this->sortField,
            'sortDirection'    => $this->sortDirection,
            'search'           => $this->search,
            'isMultiCompany'   => false,
            'companyName'      => null,
        ]);
    }

    // Base query for active employees (without date filters)
    $baseQuery = EmployeePosition::withoutCompanyScope()
        ->where('employment_status', 'Active')
        ->whereNull('deleted_at');

    // For single‑company, restrict to the run's pay schedule
    if (!$run->is_multi_company) {
        $baseQuery->where('pay_schedule_id', $run->pay_schedule_id);
    }

    $employeeIds = $baseQuery->pluck('employee_id')->unique();

    // Companies with active employees
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

    // Departments and locations (using the same base query)
    $departmentIds = $baseQuery->pluck('department_id')->unique()->filter();
    $locationIds   = $baseQuery->pluck('location_id')->unique()->filter();

    $departments = Department::whereIn('id', $departmentIds)->get();
    $locations   = Location::whereIn('id', $locationIds)->get();

    // Single‑company label
    $companyName = null;
    if (!$run->is_multi_company && $run->company_id) {
        $company = Company::find($run->company_id);
        $companyName = $company ? $company->name : 'Unknown Company';
    }

    return view('payroll::livewire.payroll.wizard-adjustments', [
        'employees'        => $this->employees,
        'companies'        => $companies,
        'departments'      => $departments,
        'locations'        => $locations,
        'sortField'        => $this->sortField,
        'sortDirection'    => $this->sortDirection,
        'search'           => $this->search,
        'isMultiCompany'   => $run->is_multi_company,
        'companyName'      => $companyName,
    ]);
}


    public function hydrate(): void
    {
        if ($this->payrollRunId && !PayrollRun::where('id', $this->payrollRunId)->exists()) {
            session()->forget('payroll-wizard-' . auth()->id());
            $this->redirectRoute('payroll-runs.create', ['error' => 'The payroll run has expired or was deleted.']);
        }
    }

}



