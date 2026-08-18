<?php


namespace App\Modules\Payroll\Http\Livewire\Payroll;

use Livewire\Component;
use App\Modules\Payroll\Models\PaySchedule;
use App\Modules\Payroll\Models\PayrollRun;
use App\Modules\Payroll\Models\PayrollRunAdjustment;
use App\Modules\Payroll\Models\PayrollPayslip;
use App\Modules\Hr\Models\Company;
use QuickerFaster\UILibrary\Concerns\ResolvesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayrollRunWizard extends Component
{
    use ResolvesModels;
    public int $currentStep = 1;
    public ?int $payrollRunId = null;
    public $pay_schedule_id = null;
    public $title = "";
    public $period_start = null;
    public $period_end = null;
    public array $stepData = [];
    public $companyId = null;

    public bool $isProcessing = false;

    // Multi-company (All Companies) properties
    public bool $isMultiCompany = false;
    public ?array $eligibleCompanies = null;
    public int $eligibleCompanyCount = 0;
    public int $totalEligibleEmployees = 0;


    protected $listeners = [
        'adjustmentsComplete' => 'goToStep3',
        'previewComplete' => 'finalize',
        'cancelKeep' => 'cancelKeep',
        'cancelDelete' => 'cancelDelete',

        'processingStarted' => 'setProcessing',
        'processingFinished' => 'clearProcessing',
    ];

    public function mount($payrollRunId = null)
    {
        $wizardId = $this->getWizardId();

        // Validate stored session data
        if (session()->has($wizardId)) {
            $data = session()->get($wizardId);
            if (isset($data['payrollRunId']) && $data['payrollRunId']) {
                $exists = PayrollRun::withoutCompanyScope()
                    ->where('id', $data['payrollRunId'])
                    ->exists();
                if (!$exists) {
                    session()->forget($wizardId);
                    $data = null;
                }
            }
        }

        if (session()->has($wizardId)) {
            $data = session()->get($wizardId);
            $this->currentStep = $data['currentStep'] ?? 1;
    $stepFromQuery = request()->query('step');
    if ($stepFromQuery) {
        $this->currentStep = (int) $stepFromQuery;
    }


            $this->payrollRunId = $data['payrollRunId'] ?? null;
            $this->pay_schedule_id = $data['pay_schedule_id'] ?? null;
            $this->title = $data['title'] ?? "";
            $this->period_start = $data['period_start'] ?? null;
            $this->period_end = $data['period_end'] ?? null;
            $this->stepData = $data['stepData'] ?? [];
            $this->isMultiCompany = $data['isMultiCompany'] ?? false;
            $this->companyId = $data['companyId'] ?? null;
        } elseif ($payrollRunId) {
            $run = $this->resolveModel(PayrollRun::class, $payrollRunId);

            if (!$run) {
                $this->cleanupWizardSession(
                    $this->getWizardId(),
                    'The payroll run you are trying to edit could not be found. It may have been deleted.',
                    'payroll-runs.index'
                );
                return;
            }

            $this->payrollRunId = $run->id;
            $this->pay_schedule_id = $run->pay_schedule_id;
            $this->title = $run->title;
            $this->period_start = $run->period_start->format('Y-m-d');
            $this->period_end = $run->period_end->format('Y-m-d');
            $this->currentStep = $run->current_step ?? 1;
            $this->isMultiCompany = $run->is_multi_company ?? false;
            $this->companyId = $run->company_id;
            $this->stepData = ['payroll_run_id' => $run->id];
            $this->saveToSession();
        } else {
            // NEW wizard
            $this->companyId = session('current_company_id') ?: null;
            $this->saveToSession();
        }

        // If a pay schedule is already selected, compute eligible companies immediately
if ($this->pay_schedule_id) {
    $this->eligibleCompanies = $this->computeEligibleCompanies();
} else {
    $this->eligibleCompanies = $this->computeAllEligibleCompanies();
}
    }

public function updatedIsMultiCompany($value)
{
    if ($value) {
        // multi-company: ignore pay schedule, compute all companies
        $this->eligibleCompanies = $this->computeAllEligibleCompanies();
    } else {
        // single-company: recompute using pay schedule if selected
        if ($this->pay_schedule_id) {
            $this->eligibleCompanies = $this->computeEligibleCompanies();
        } else {
            $this->eligibleCompanies = [];
        }
    }
}

    protected function getWizardId(): string
    {
        return 'payroll-wizard-' . auth()->id();
    }

    protected function saveToSession(): void
    {
        session()->put($this->getWizardId(), [
            'currentStep' => $this->currentStep,
            'payrollRunId' => $this->payrollRunId,
            'pay_schedule_id' => $this->pay_schedule_id,
            'title' => $this->title,
            'period_start' => $this->period_start,
            'period_end' => $this->period_end,
            'stepData' => $this->stepData,
            'isMultiCompany' => $this->isMultiCompany,
            'companyId' => $this->companyId,
        ]);
    }

    public function getCompaniesProperty()
    {
        return Company::all();
    }

    public function isAllCompaniesMode(): bool
    {
        return session('current_company_id') === 0;
    }

    public function goToStep($step)
    {
        $this->currentStep = $step;
        $this->saveToSession();
    }

    /**
     * Recompute eligible companies whenever the pay schedule changes.
     * Called automatically by Livewire's magic hook.
     */
public function updatedPayScheduleId($value)
{
    if (!$this->isMultiCompany) {
        $this->eligibleCompanies = $value ? $this->computeEligibleCompanies() : [];
    } else {
        // In multi-company mode, schedule change should not affect companies,
        // but we might still keep the current all-companies list.
        // Actually, we could recompute all companies (they remain the same).
        // We can leave as is or recompute.

        // multi-company: ignore pay schedule, compute all companies
        $this->eligibleCompanies = $this->computeAllEligibleCompanies();



    }
}



protected function computeEligibleCompanies(): array
{
    if (!$this->pay_schedule_id) {
        $this->eligibleCompanyCount = 0;
        $this->totalEligibleEmployees = 0;
        return [];
    }

    // Query active employee positions on this schedule, grouped by company
    $results = DB::table('employee_positions')
        ->join('employees', 'employee_positions.employee_id', '=', 'employees.id')
        ->where('employee_positions.pay_schedule_id', $this->pay_schedule_id)
        ->where('employee_positions.employment_status', 'Active')
        ->whereNull('employee_positions.deleted_at')
        ->select('employees.company_id', DB::raw('COUNT(*) as employee_count'))
        ->groupBy('employees.company_id')
        ->get();

    // Fetch company names
    $companyIds = $results->pluck('company_id')->filter()->unique();
    $companies = Company::whereIn('id', $companyIds)->get()->keyBy('id');

    $result = [];
    foreach ($results as $row) {
        $company = $companies->get($row->company_id);
        $result[] = [
            'company_id' => $row->company_id,
            'company_name' => $company ? $company->name : 'Unassigned',
            'employee_count' => (int) $row->employee_count,
        ];
    }

    $this->eligibleCompanyCount = count($result);
    $this->totalEligibleEmployees = $results->sum('employee_count');

    return $result;
}




protected function computeAllEligibleCompanies(): array
{
    // Query all active employee positions across all companies, grouped by company
    $results = DB::table('employee_positions')
        ->join('employees', 'employee_positions.employee_id', '=', 'employees.id')
        ->where('employee_positions.employment_status', 'Active')
        ->whereNull('employee_positions.deleted_at')
        ->select('employees.company_id', DB::raw('COUNT(*) as employee_count'))
        ->groupBy('employees.company_id')
        ->get();

    $companyIds = $results->pluck('company_id')->filter()->unique();
    $companies = Company::whereIn('id', $companyIds)->get()->keyBy('id');

    $result = [];
    foreach ($results as $row) {
        $company = $companies->get($row->company_id);
        $result[] = [
            'company_id' => $row->company_id,
            'company_name' => $company ? $company->name : 'Unassigned',
            'employee_count' => (int) $row->employee_count,
        ];
    }

    $this->eligibleCompanyCount = count($result);
    $this->totalEligibleEmployees = $results->sum('employee_count');

    return $result;
}





public function goToStep2()
{
    if ($this->isProcessing) {
        return; // Already processing, ignore duplicate clicks
    }

    $rules = [
        'period_start' => 'required|date',
        'period_end'   => 'required|date|after:period_start',
        'title'        => 'required|unique:payroll_runs,title,' . $this->payrollRunId,
    ];

    // Single‑company mode: require a pay schedule and (if in "All Companies" top‑nav) a companyId
    if (!$this->isMultiCompany) {
        $rules['pay_schedule_id'] = 'required|exists:pay_schedules,id';
        if ($this->isAllCompaniesMode()) {
            $rules['companyId'] = 'required|integer|exists:companies,id';
        }
    }

    $this->validate($rules);




    // --- ADD THIS VALIDATION ---
    if (!$this->isMultiCompany && $this->isAllCompaniesMode() && $this->companyId) {
        // Recompute eligible companies for the chosen pay schedule
        $eligible = $this->computeEligibleCompanies();
        $companyIds = array_column($eligible, 'company_id');
        if (!in_array($this->companyId, $companyIds)) {
            $this->addError('companyId', 'The selected company has no active employees on the chosen pay schedule. Please choose a different company or schedule.');
            return;
        }
    }



    DB::transaction(function () {
        if ($this->isMultiCompany) {
            $runCompanyId = null;
            $payScheduleId = null; // <-- now allowed by the migration
        } else {
            // Use wizard's internal $companyId (initialized once in mount),
            // never re-read session('current_company_id').
            $runCompanyId = $this->companyId;
            $payScheduleId = $this->pay_schedule_id;
        }

        if (!$this->payrollRunId) {
            $run = PayrollRun::create([
                'pay_schedule_id'   => $payScheduleId,
                'period_start'      => $this->period_start,
                'period_end'        => $this->period_end,
                'status'            => 'draft',
                'calculation_status'=> 'pending',
                'current_step'      => 2,
                'title'             => $this->title,
                'company_id'        => $runCompanyId,
                'is_multi_company'  => $this->isMultiCompany,
            ]);
            $this->payrollRunId = $run->id;
        } else {
            $run = PayrollRun::find($this->payrollRunId);
            if ($run) {
                $run->update([
                    'pay_schedule_id'   => $payScheduleId,
                    'period_start'      => $this->period_start,
                    'period_end'        => $this->period_end,
                    'title'             => $this->title,
                    'company_id'        => $runCompanyId,
                    'is_multi_company'  => $this->isMultiCompany,
                ]);
            } else {
                // If run missing, create a new one
                $run = PayrollRun::create([
                    'pay_schedule_id'   => $payScheduleId,
                    'period_start'      => $this->period_start,
                    'period_end'        => $this->period_end,
                    'status'            => 'draft',
                    'calculation_status'=> 'pending',
                    'current_step'      => 2,
                    'title'             => $this->title,
                    'company_id'        => $runCompanyId,
                    'is_multi_company'  => $this->isMultiCompany,
                ]);
                $this->payrollRunId = $run->id;
            }
        }
    });

    $this->currentStep = 2;
    $this->saveToSession();
}



    /*public function goToStep3()
    {
        $this->currentStep = 3;
        $this->saveToSession();
        $this->dispatch('refreshPreview');
    }*/


public function goToStep3()
{
    if ($this->isProcessing) {
        return; // Already processing, ignore duplicate clicks
    }

    // Save session data before redirect
    $this->saveToSession();

    // Build the URL with proper query parameters
    $url = url('/hr/payroll-wizard') . '?payrollRunId=' . $this->payrollRunId . '&step=3';

    return redirect()->to($url);
}




    /**
     * Finalize the wizard – does NOT run calculation synchronously.
     * The calculation is handled by the queue job (triggered by the preview component).
     */
public function finalize()
{
    if ($this->isProcessing) {
        return; // Already processing, ignore duplicate clicks
    }

    $run = PayrollRun::find($this->payrollRunId);
    if (!$run) {
        session()->forget($this->getWizardId());
        $this->redirectRoute('payroll-runs.create', ['error' => 'Payroll run not found.']);
        return;
    }

    // Update run status (optional, can stay 'ready_for_review')
    $run->update([
        'status' => 'ready_for_review',
        'current_step' => 4,
    ]);

    // Start approval process
    $engine = app(\QuickerFaster\UILibrary\Services\Workflow\WorkflowEngine::class);
    if (!$run->isUnderApproval()) {
        $engine->start($run);
    }

    // Clear wizard session
    session()->forget($this->getWizardId());

    session()->flash('message', 'Payroll run submitted for approval. You will be notified when reviewed.');
    return redirect()->route('payroll-runs.show', $this->payrollRunId);
}


    public function setProcessing(): void
    {
        $this->isProcessing = true;
    }

    public function clearProcessing(): void
    {
        $this->isProcessing = false;
    }



    public function confirmCancel(): void
    {
        $this->dispatch('showAlert', [
            'type' => 'confirm',
            'title' => 'Cancel Payroll Wizard?',
            'message' => 'Any data you have already saved will remain in the system. Do you want to keep it or delete all progress?',
            'icon' => 'fas fa-question-circle',
            'confirmText' => 'Keep Data',
            'cancelText' => 'Delete Progress',
            'confirmEvent' => 'cancelKeep',
            'cancelEvent' => 'cancelDelete',
        ]);
    }

    public function cancelKeep(): void
    {
        session()->forget($this->getWizardId());
        $this->redirect('/hr/payroll-runs');
    }

    public function cancelDelete(): void
    {
        DB::transaction(function () {
            if ($this->payrollRunId) {
                PayrollRunAdjustment::where('payroll_run_id', $this->payrollRunId)->delete();
                PayrollPayslip::where('payroll_run_id', $this->payrollRunId)->delete();
                PayrollRun::destroy($this->payrollRunId);
            }
        });
        session()->forget($this->getWizardId());
        $this->redirect('/hr/payroll-runs');
    }

    public function render()
    {
        return view('hr::livewire.payroll.payroll-run-wizard', [
            'currentStep' => $this->currentStep,
            'title' => $this->title,
            'payrollRunId' => $this->payrollRunId,
            'paySchedule' => $this->pay_schedule_id ? PaySchedule::find($this->pay_schedule_id) : null,
            'errorBag' => $this->getErrorBag(),
        ]);
    }
}
