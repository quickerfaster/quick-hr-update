<?php

use Illuminate\Support\Facades\Route;



use App\Modules\Hr\Http\Controllers\PayrollRunController;
use App\Modules\Hr\Http\Controllers\PayrollReportController;
use App\Modules\Hr\Http\Controllers\PayslipController;


use App\Modules\Hr\Http\Livewire\AdjustAttendanceMvp;
use App\Modules\Hr\Http\Controllers\EmployeePrintController;
use App\Modules\Hr\Http\Controllers\Payrolls\BankFileController;
use App\Modules\Hr\Http\Controllers\Payrolls\PayrollRunPdfController;
use App\Modules\Hr\Models\PayrollRun;


Route::middleware([
    'web',
    // InitializeTenancyByDomain::class,
    // PreventAccessFromCentralDomains::class,

])->group(function () {





    // In your web.php or hr module routes

    /*Route::get('/hr/attendance/{attendanceId}/adjust', function ($attendanceId) {
        return view('hr::adjust-attendance', ['attendanceId' => $attendanceId]);
    } )->name('attendance.adjust');*/





    // Preview modal
    Route::get('/hr/payroll-runs/{payrollRun}/preview', [PayrollRunController::class, 'preview'])
        ->name('payroll.runs.preview');

    // Approve action
    Route::post('/hr/payroll-runs/{payrollRun}/approve', [PayrollRunController::class, 'approve'])
        ->name('payroll.runs.approve');


    // Preview modal
    Route::get('/hr/payroll-runs/{payrollRun}/edit', [PayrollRunController::class, 'edit'])
        ->name('payroll.payroll-employees.edit');


    // Payroll Reports
    Route::get('/hr/payroll-runs/{payrollRun}/report', [PayrollReportController::class, 'show'])
        ->name('payroll.reports.show');

    Route::get('/hr/payroll-runs/{payrollRun}/report/download/pdf', [PayrollReportController::class, 'downloadPdf'])
        ->name('payroll.reports.download.pdf');

    Route::get('/hr/payroll-runs/{payrollRun}/report/download/excel', [PayrollReportController::class, 'downloadExcel'])
        ->name('payroll.reports.download.excel');



    // Employee payslips
    Route::get('/hr/payslips/{payslip}', [PayslipController::class, 'download'])
        ->name('payslips.download');
    //->middleware('auth');

    // HR admin payslips
    Route::get('/hr/payslips/{payslip}/view', [PayslipController::class, 'view'])
        ->name('payslips.view');
    //->middleware('can:manage-payroll');



    // Route::post('/payroll-runs/{payrollRun}/generate-payslips', [PayrollRunController::class, 'generatePayslips']);
    // Route::post('/payroll-runs/{payrollRun}/mark-as-paid', [PayrollRunController::class, 'markAsPaid']);





    Route::get('/employees/{employee}/print', [EmployeePrintController::class, 'show'])
        ->name('hr.employees.print')
        //->middleware(['auth', 'can:view,employee']);
    ;



    Route::get('/payroll-run/{run}/bank-file', [BankFileController::class, 'download'])
        ->name('payroll.bank-file');



Route::get('/payroll-run/{run}/print-summary', function (PayrollRun $run) {
    $currencyCode = $run->paySchedule?->currency_code ?? $run->base_currency ?? 'USD';
    $companyName = $run->paySchedule?->company?->name ?? ($run->is_multi_company ? 'All Companies' : config('app.name', 'Quick HR'));
    $currencySymbol = "N";
    $run->load('payslips.employee');

    return view('hr::livewire.payroll.print.payroll-run-summary', [
        'run' => $run,
        'currencySymbol' => $currencySymbol,
        'companyName' => $companyName,
    ]);
})->name('payroll-run.print-summary');


    Route::get('/payroll-run/{run}/summary-grouped/{group_by}', function (PayrollRun $run, $group_by) {
        $validGroups = ['department', 'location', 'company'];
        if (!in_array($group_by, $validGroups)) {
            abort(404);
        }

        // Load payslips with all necessary relationships
        $run->load([
            'payslips' => function ($query) {
                $query->with([
                    'employee' => function ($q) {
                        $q->with('company');
                    },
                    'employee.employeePosition' // for department & location
                ]);
            }
        ]);

        // Group the payslips collection manually
        $groups = $run->payslips->groupBy(function ($payslip) use ($group_by) {
            switch ($group_by) {
                case 'department':
                    $dept = optional($payslip->employee->employeePosition?->department);
                    return $dept->name ?? 'No Department';
                case 'location':
                    $loc = optional($payslip->employee->employeePosition?->location);
                    return $loc->name ?? 'No Location';
                case 'company':
                    $company = optional($payslip->employee->company);
                    return $company->name ?? 'No Company';
                default:
                    return 'Unknown';
            }
        });

        return view('hr::livewire.payroll.print.payroll-run-summary-grouped', [
            'run' => $run,
            'groups' => $groups,
            'groupBy' => $group_by,
        ]);
    })->name('payroll-run.summary-grouped');



    Route::get('/payroll-run/{run}/executive-summary', function (PayrollRun $run) {
        return view('hr::livewire.payroll.payroll-executive-summary', ['run' => $run]);
    })->name('payroll-run.executive-summary');



})->middleware(['auth']);




// Routes for AttendancePolicy

// Create Route
Route::get('attendance-policies/create', function (\Illuminate\Http\Request $request) {
    return view('hr::attendance-policies.create', [
        'configKey' => 'hr.attendance_policy',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('attendance-policies.create');

// Show Route
Route::get('attendance-policies/{id}', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::attendance-policies.show', [
        'recordId' => (int) $id,
        'configKey' => 'hr.attendance_policy',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('attendance-policies.show')->where('id', '[0-9]+');

// Edit Route
Route::get('attendance-policies/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::attendance-policies.edit', [
        'recordId' => (int) $id,
        'configKey' => 'hr.attendance_policy',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('attendance-policies.edit')->where('id', '[0-9]+'); // And here;


// Routes for Employee

// Create Route
Route::get('employees/create', function (\Illuminate\Http\Request $request) {
    return view('hr::employees.create', [
        'configKey' => 'hr.employee',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('employees.create');

// Show Route
Route::get('employees/{id}', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::employees.show', [
        'recordId' => (int) $id,
        'configKey' => 'hr.employee',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('employees.show')->where('id', '[0-9]+');

// Edit Route
Route::get('employees/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::employees.edit', [
        'recordId' => (int) $id,
        'configKey' => 'hr.employee',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('employees.edit')->where('id', '[0-9]+'); // And here;


// Routes for EmployeeJobHistory

// Create Route
Route::get('employee-job-histories/create', function (\Illuminate\Http\Request $request) {
    return view('hr::employee-job-histories.create', [
        'configKey' => 'hr.employee_job_history',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('employee-job-histories.create');

// Show Route
Route::get('employee-job-histories/{id}', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::employee-job-histories.show', [
        'recordId' => (int) $id,
        'configKey' => 'hr.employee_job_history',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('employee-job-histories.show')->where('id', '[0-9]+');

// Edit Route
Route::get('employee-job-histories/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::employee-job-histories.edit', [
        'recordId' => (int) $id,
        'configKey' => 'hr.employee_job_history',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('employee-job-histories.edit')->where('id', '[0-9]+'); // And here;


// Routes for EmployeeProfile

// Create Route
Route::get('employee-profiles/create', function (\Illuminate\Http\Request $request) {
    return view('hr::employee-profiles.create', [
        'configKey' => 'hr.employee_profile',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('employee-profiles.create');

// Show Route
Route::get('employee-profiles/{id}', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::employee-profiles.show', [
        'recordId' => (int) $id,
        'configKey' => 'hr.employee_profile',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('employee-profiles.show')->where('id', '[0-9]+');

// Edit Route
Route::get('employee-profiles/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::employee-profiles.edit', [
        'recordId' => (int) $id,
        'configKey' => 'hr.employee_profile',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('employee-profiles.edit')->where('id', '[0-9]+'); // And here;


// Routes for WorkPattern

// Create Route
Route::get('work-patterns/create', function (\Illuminate\Http\Request $request) {
    return view('hr::work-patterns.create', [
        'configKey' => 'hr.work_pattern',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('work-patterns.create');

// Show Route
Route::get('work-patterns/{id}', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::work-patterns.show', [
        'recordId' => (int) $id,
        'configKey' => 'hr.work_pattern',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('work-patterns.show')->where('id', '[0-9]+');

// Edit Route
Route::get('work-patterns/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::work-patterns.edit', [
        'recordId' => (int) $id,
        'configKey' => 'hr.work_pattern',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('work-patterns.edit')->where('id', '[0-9]+'); // And here;


// Routes for PaySchedule

// Create Route
Route::get('pay-schedules/create', function (\Illuminate\Http\Request $request) {
    return view('hr::pay-schedules.create', [
        'configKey' => 'hr.pay_schedule',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('pay-schedules.create');

// Show Route
Route::get('pay-schedules/{id}', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::pay-schedules.show', [
        'recordId' => (int) $id,
        'configKey' => 'hr.pay_schedule',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('pay-schedules.show')->where('id', '[0-9]+');

// Edit Route
Route::get('pay-schedules/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::pay-schedules.edit', [
        'recordId' => (int) $id,
        'configKey' => 'hr.pay_schedule',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('pay-schedules.edit')->where('id', '[0-9]+'); // And here;


// Routes for EmployeePayrollProfile

// Create Route
Route::get('employee-payroll-profiles/create', function (\Illuminate\Http\Request $request) {
    return view('hr::employee-payroll-profiles.create', [
        'configKey' => 'hr.employee_payroll_profile',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('employee-payroll-profiles.create');

// Show Route
Route::get('employee-payroll-profiles/{id}', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::employee-payroll-profiles.show', [
        'recordId' => (int) $id,
        'configKey' => 'hr.employee_payroll_profile',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('employee-payroll-profiles.show')->where('id', '[0-9]+');

// Edit Route
Route::get('employee-payroll-profiles/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::employee-payroll-profiles.edit', [
        'recordId' => (int) $id,
        'configKey' => 'hr.employee_payroll_profile',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('employee-payroll-profiles.edit')->where('id', '[0-9]+'); // And here;


// Routes for PayrollRun

// Create Route
Route::get('payroll-runs/create', function (\Illuminate\Http\Request $request) {
    return view('hr::payroll-runs.create', [
        'configKey' => 'hr.payroll_run',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('payroll-runs.create');

// Show Route
Route::get('payroll-runs/{id}', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::payroll-runs.show', [
        'recordId' => (int) $id,
        'configKey' => 'hr.payroll_run',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('payroll-runs.show')->where('id', '[0-9]+');

// Edit Route
Route::get('payroll-runs/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::payroll-runs.edit', [
        'recordId' => (int) $id,
        'configKey' => 'hr.payroll_run',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('payroll-runs.edit')->where('id', '[0-9]+'); // And here;


// Routes for PayrollPayslip

// Create Route
Route::get('payroll-payslips/create', function (\Illuminate\Http\Request $request) {
    return view('hr::payroll-payslips.create', [
        'configKey' => 'hr.payroll_payslip',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('payroll-payslips.create');

// Show Route
Route::get('payroll-payslips/{id}', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::payroll-payslips.show', [
        'recordId' => (int) $id,
        'configKey' => 'hr.payroll_payslip',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('payroll-payslips.show')->where('id', '[0-9]+');

// Edit Route
Route::get('payroll-payslips/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::payroll-payslips.edit', [
        'recordId' => (int) $id,
        'configKey' => 'hr.payroll_payslip',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('payroll-payslips.edit')->where('id', '[0-9]+'); // And here;


// Routes for PayrollPolicy

// Create Route
Route::get('payroll-policies/create', function (\Illuminate\Http\Request $request) {
    return view('hr::payroll-policies.create', [
        'configKey' => 'hr.payroll_policy',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('payroll-policies.create');

// Show Route
Route::get('payroll-policies/{id}', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::payroll-policies.show', [
        'recordId' => (int) $id,
        'configKey' => 'hr.payroll_policy',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('payroll-policies.show')->where('id', '[0-9]+');

// Edit Route
Route::get('payroll-policies/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::payroll-policies.edit', [
        'recordId' => (int) $id,
        'configKey' => 'hr.payroll_policy',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('payroll-policies.edit')->where('id', '[0-9]+'); // And here;


// Routes for EmployeePosition

// Create Route
Route::get('employee-positions/create', function (\Illuminate\Http\Request $request) {
    return view('hr::employee-positions.create', [
        'configKey' => 'hr.employee_position',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('employee-positions.create');

// Show Route
Route::get('employee-positions/{id}', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::employee-positions.show', [
        'recordId' => (int) $id,
        'configKey' => 'hr.employee_position',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('employee-positions.show')->where('id', '[0-9]+');

// Edit Route
Route::get('employee-positions/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::employee-positions.edit', [
        'recordId' => (int) $id,
        'configKey' => 'hr.employee_position',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('employee-positions.edit')->where('id', '[0-9]+'); // And here;


// Routes for Attendance

// Create Route
Route::get('attendances/create', function (\Illuminate\Http\Request $request) {
    return view('hr::attendances.create', [
        'configKey' => 'hr.attendance',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('attendances.create');

// Show Route
Route::get('attendances/{id}', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::attendances.show', [
        'recordId' => (int) $id,
        'configKey' => 'hr.attendance',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('attendances.show')->where('id', '[0-9]+');

// Edit Route
Route::get('attendances/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::attendances.edit', [
        'recordId' => (int) $id,
        'configKey' => 'hr.attendance',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('attendances.edit')->where('id', '[0-9]+'); // And here;


// Routes for Holiday

// Create Route
Route::get('holidays/create', function (\Illuminate\Http\Request $request) {
    return view('hr::holidays.create', [
        'configKey' => 'hr.holiday',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('holidays.create');

// Show Route
Route::get('holidays/{id}', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::holidays.show', [
        'recordId' => (int) $id,
        'configKey' => 'hr.holiday',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('holidays.show')->where('id', '[0-9]+');

// Edit Route
Route::get('holidays/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::holidays.edit', [
        'recordId' => (int) $id,
        'configKey' => 'hr.holiday',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('holidays.edit')->where('id', '[0-9]+'); // And here;


// Routes for AttendancePolicy

// Create Route
Route::get('attendance-policies/create', function (\Illuminate\Http\Request $request) {
    return view('hr::attendance-policies.create', [
        'configKey' => 'hr.attendance_policy',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('attendance-policies.create');

// Show Route
Route::get('attendance-policies/{id}', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::attendance-policies.show', [
        'recordId' => (int) $id,
        'configKey' => 'hr.attendance_policy',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('attendance-policies.show')->where('id', '[0-9]+');

// Edit Route
Route::get('attendance-policies/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::attendance-policies.edit', [
        'recordId' => (int) $id,
        'configKey' => 'hr.attendance_policy',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('attendance-policies.edit')->where('id', '[0-9]+'); // And here;


// Routes for Employee

// Create Route
Route::get('employees/create', function (\Illuminate\Http\Request $request) {
    return view('hr::employees.create', [
        'configKey' => 'hr.employee',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('employees.create');

// Show Route
Route::get('employees/{id}', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::employees.show', [
        'recordId' => (int) $id,
        'configKey' => 'hr.employee',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('employees.show')->where('id', '[0-9]+');

// Edit Route
Route::get('employees/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::employees.edit', [
        'recordId' => (int) $id,
        'configKey' => 'hr.employee',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('employees.edit')->where('id', '[0-9]+'); // And here;


// Routes for EmployeeJobHistory

// Create Route
Route::get('employee-job-histories/create', function (\Illuminate\Http\Request $request) {
    return view('hr::employee-job-histories.create', [
        'configKey' => 'hr.employee_job_history',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('employee-job-histories.create');

// Show Route
Route::get('employee-job-histories/{id}', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::employee-job-histories.show', [
        'recordId' => (int) $id,
        'configKey' => 'hr.employee_job_history',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('employee-job-histories.show')->where('id', '[0-9]+');

// Edit Route
Route::get('employee-job-histories/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::employee-job-histories.edit', [
        'recordId' => (int) $id,
        'configKey' => 'hr.employee_job_history',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('employee-job-histories.edit')->where('id', '[0-9]+'); // And here;


// Routes for EmployeeProfile

// Create Route
Route::get('employee-profiles/create', function (\Illuminate\Http\Request $request) {
    return view('hr::employee-profiles.create', [
        'configKey' => 'hr.employee_profile',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('employee-profiles.create');

// Show Route
Route::get('employee-profiles/{id}', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::employee-profiles.show', [
        'recordId' => (int) $id,
        'configKey' => 'hr.employee_profile',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('employee-profiles.show')->where('id', '[0-9]+');

// Edit Route
Route::get('employee-profiles/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::employee-profiles.edit', [
        'recordId' => (int) $id,
        'configKey' => 'hr.employee_profile',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('employee-profiles.edit')->where('id', '[0-9]+'); // And here;


// Routes for WorkPattern

// Create Route
Route::get('work-patterns/create', function (\Illuminate\Http\Request $request) {
    return view('hr::work-patterns.create', [
        'configKey' => 'hr.work_pattern',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('work-patterns.create');

// Show Route
Route::get('work-patterns/{id}', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::work-patterns.show', [
        'recordId' => (int) $id,
        'configKey' => 'hr.work_pattern',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('work-patterns.show')->where('id', '[0-9]+');

// Edit Route
Route::get('work-patterns/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::work-patterns.edit', [
        'recordId' => (int) $id,
        'configKey' => 'hr.work_pattern',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('work-patterns.edit')->where('id', '[0-9]+'); // And here;


// Routes for PaySchedule

// Create Route
Route::get('pay-schedules/create', function (\Illuminate\Http\Request $request) {
    return view('hr::pay-schedules.create', [
        'configKey' => 'hr.pay_schedule',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('pay-schedules.create');

// Show Route
Route::get('pay-schedules/{id}', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::pay-schedules.show', [
        'recordId' => (int) $id,
        'configKey' => 'hr.pay_schedule',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('pay-schedules.show')->where('id', '[0-9]+');

// Edit Route
Route::get('pay-schedules/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::pay-schedules.edit', [
        'recordId' => (int) $id,
        'configKey' => 'hr.pay_schedule',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('pay-schedules.edit')->where('id', '[0-9]+'); // And here;


// Routes for EmployeePayrollProfile

// Create Route
Route::get('employee-payroll-profiles/create', function (\Illuminate\Http\Request $request) {
    return view('hr::employee-payroll-profiles.create', [
        'configKey' => 'hr.employee_payroll_profile',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('employee-payroll-profiles.create');

// Show Route
Route::get('employee-payroll-profiles/{id}', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::employee-payroll-profiles.show', [
        'recordId' => (int) $id,
        'configKey' => 'hr.employee_payroll_profile',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('employee-payroll-profiles.show')->where('id', '[0-9]+');

// Edit Route
Route::get('employee-payroll-profiles/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::employee-payroll-profiles.edit', [
        'recordId' => (int) $id,
        'configKey' => 'hr.employee_payroll_profile',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('employee-payroll-profiles.edit')->where('id', '[0-9]+'); // And here;


// Routes for PayrollRun

// Create Route
Route::get('payroll-runs/create', function (\Illuminate\Http\Request $request) {
    return view('hr::payroll-runs.create', [
        'configKey' => 'hr.payroll_run',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('payroll-runs.create');

// Show Route
Route::get('payroll-runs/{id}', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::payroll-runs.show', [
        'recordId' => (int) $id,
        'configKey' => 'hr.payroll_run',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('payroll-runs.show')->where('id', '[0-9]+');

// Edit Route
Route::get('payroll-runs/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::payroll-runs.edit', [
        'recordId' => (int) $id,
        'configKey' => 'hr.payroll_run',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('payroll-runs.edit')->where('id', '[0-9]+'); // And here;


// Routes for PayrollPayslip

// Create Route
Route::get('payroll-payslips/create', function (\Illuminate\Http\Request $request) {
    return view('hr::payroll-payslips.create', [
        'configKey' => 'hr.payroll_payslip',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('payroll-payslips.create');

// Show Route
Route::get('payroll-payslips/{id}', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::payroll-payslips.show', [
        'recordId' => (int) $id,
        'configKey' => 'hr.payroll_payslip',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('payroll-payslips.show')->where('id', '[0-9]+');

// Edit Route
Route::get('payroll-payslips/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::payroll-payslips.edit', [
        'recordId' => (int) $id,
        'configKey' => 'hr.payroll_payslip',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('payroll-payslips.edit')->where('id', '[0-9]+'); // And here;


// Routes for PayrollPolicy

// Create Route
Route::get('payroll-policies/create', function (\Illuminate\Http\Request $request) {
    return view('hr::payroll-policies.create', [
        'configKey' => 'hr.payroll_policy',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('payroll-policies.create');

// Show Route
Route::get('payroll-policies/{id}', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::payroll-policies.show', [
        'recordId' => (int) $id,
        'configKey' => 'hr.payroll_policy',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('payroll-policies.show')->where('id', '[0-9]+');

// Edit Route
Route::get('payroll-policies/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::payroll-policies.edit', [
        'recordId' => (int) $id,
        'configKey' => 'hr.payroll_policy',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('payroll-policies.edit')->where('id', '[0-9]+'); // And here;


// Routes for EmployeePosition

// Create Route
Route::get('employee-positions/create', function (\Illuminate\Http\Request $request) {
    return view('hr::employee-positions.create', [
        'configKey' => 'hr.employee_position',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('employee-positions.create');

// Show Route
Route::get('employee-positions/{id}', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::employee-positions.show', [
        'recordId' => (int) $id,
        'configKey' => 'hr.employee_position',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('employee-positions.show')->where('id', '[0-9]+');

// Edit Route
Route::get('employee-positions/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::employee-positions.edit', [
        'recordId' => (int) $id,
        'configKey' => 'hr.employee_position',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('employee-positions.edit')->where('id', '[0-9]+'); // And here;


// Routes for Attendance

// Create Route
Route::get('attendances/create', function (\Illuminate\Http\Request $request) {
    return view('hr::attendances.create', [
        'configKey' => 'hr.attendance',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('attendances.create');

// Show Route
Route::get('attendances/{id}', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::attendances.show', [
        'recordId' => (int) $id,
        'configKey' => 'hr.attendance',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('attendances.show')->where('id', '[0-9]+');

// Edit Route
Route::get('attendances/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::attendances.edit', [
        'recordId' => (int) $id,
        'configKey' => 'hr.attendance',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('attendances.edit')->where('id', '[0-9]+'); // And here;


// Routes for Holiday

// Create Route
Route::get('holidays/create', function (\Illuminate\Http\Request $request) {
    return view('hr::holidays.create', [
        'configKey' => 'hr.holiday',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('holidays.create');

// Show Route
Route::get('holidays/{id}', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::holidays.show', [
        'recordId' => (int) $id,
        'configKey' => 'hr.holiday',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('holidays.show')->where('id', '[0-9]+');

// Edit Route
Route::get('holidays/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::holidays.edit', [
        'recordId' => (int) $id,
        'configKey' => 'hr.holiday',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('holidays.edit')->where('id', '[0-9]+'); // And here;


// Routes for Location

// Create Route
Route::get('locations/create', function (\Illuminate\Http\Request $request) {
    return view('hr::locations.create', [
        'configKey' => 'hr.location',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('locations.create');

// Show Route
Route::get('locations/{id}', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::locations.show', [
        'recordId' => (int) $id,
        'configKey' => 'hr.location',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('locations.show')->where('id', '[0-9]+');

// Edit Route
Route::get('locations/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
    return view('hr::locations.edit', [
        'recordId' => (int) $id,
        'configKey' => 'hr.location',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('locations.edit')->where('id', '[0-9]+');
