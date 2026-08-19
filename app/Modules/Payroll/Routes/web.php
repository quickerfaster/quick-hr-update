<?php

use Illuminate\Support\Facades\Route;

use App\Modules\Payroll\Http\Controllers\PayrollRunController;
use App\Modules\Payroll\Http\Controllers\PayrollReportController;
use App\Modules\Payroll\Http\Controllers\PayslipController;
use App\Modules\Payroll\Http\Controllers\BankFileController;
use App\Modules\Payroll\Models\PayrollRun;

Route::middleware([
    'web',
])->group(function () {

    Route::get('/payroll/dashboard', function () {
        return view('payroll::dashboard');
    })->name('payroll.dashboard');

    // Preview modal
    Route::get('/payroll/payroll-runs/{payrollRun}/preview', [PayrollRunController::class, 'preview'])
        ->name('payroll.runs.preview');

    // Approve action
    Route::post('/payroll/payroll-runs/{payrollRun}/approve', [PayrollRunController::class, 'approve'])
        ->name('payroll.runs.approve');

    // Preview modal
    Route::get('/payroll/payroll-runs/{payrollRun}/edit', [PayrollRunController::class, 'edit'])
        ->name('payroll.payroll-employees.edit');

    // Payroll Reports
    Route::get('/payroll/payroll-runs/{payrollRun}/report', [PayrollReportController::class, 'show'])
        ->name('payroll.reports.show');

    Route::get('/payroll/payroll-runs/{payrollRun}/report/download/pdf', [PayrollReportController::class, 'downloadPdf'])
        ->name('payroll.reports.download.pdf');

    Route::get('/payroll/payroll-runs/{payrollRun}/report/download/excel', [PayrollReportController::class, 'downloadExcel'])
        ->name('payroll.reports.download.excel');

    // Employee payslips
    Route::get('/payroll/payslips/{payslip}', [PayslipController::class, 'download'])
        ->name('payslips.download');

    // HR admin payslips
    Route::get('/payroll/payslips/{payslip}/view', [PayslipController::class, 'view'])
        ->name('payslips.view');

    Route::get('/payroll/payroll-run/{run}/bank-file', [BankFileController::class, 'download'])
        ->name('payroll.bank-file');

    Route::get('/payroll/payroll-run/{run}/print-summary', function (PayrollRun $run) {
        $currencyCode = $run->paySchedule?->currency_code ?? $run->base_currency ?? 'USD';
        $companyName = $run->paySchedule?->company?->name ?? ($run->is_multi_company ? 'All Companies' : config('app.name', 'Quick HR'));
        $currencySymbol = "N";
        $run->load('payslips.employee');

        return view('payroll::livewire.payroll.print.payroll-run-summary', [
            'run' => $run,
            'currencySymbol' => $currencySymbol,
            'companyName' => $companyName,
        ]);
    })->name('payroll-run.print-summary');

    Route::get('/payroll/payroll-run/{run}/summary-grouped/{group_by}', function (PayrollRun $run, $group_by) {
        $validGroups = ['department', 'location', 'company'];
        if (!in_array($group_by, $validGroups)) {
            abort(404);
        }

        $run->load([
            'payslips' => function ($query) {
                $query->with([
                    'employee' => function ($q) {
                        $q->with('company');
                    },
                    'employee.employeePosition'
                ]);
            }
        ]);

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

        return view('payroll::livewire.payroll.print.payroll-run-summary-grouped', [
            'run' => $run,
            'groups' => $groups,
            'groupBy' => $group_by,
        ]);
    })->name('payroll-run.summary-grouped');

    Route::get('/payroll/payroll-run/{run}/executive-summary', function (PayrollRun $run) {
        return view('payroll::livewire.payroll.payroll-executive-summary', ['run' => $run]);
    })->name('payroll-run.executive-summary');

})->middleware(['auth']);

// Routes for PaySchedule

// Create Route
Route::get('pay-schedules/create', function (\Illuminate\Http\Request $request) {
    return view('payroll::pay-schedules.create', [
        'configKey' => 'payroll.pay_schedule',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('pay-schedules.create');

// Show Route
Route::get('pay-schedules/{id}', function (\Illuminate\Http\Request $request, $id) {
    return view('payroll::pay-schedules.show', [
        'recordId' => (int) $id,
        'configKey' => 'payroll.pay_schedule',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('pay-schedules.show')->where('id', '[0-9]+');

// Edit Route
Route::get('pay-schedules/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
    return view('payroll::pay-schedules.edit', [
        'recordId' => (int) $id,
        'configKey' => 'payroll.pay_schedule',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('pay-schedules.edit')->where('id', '[0-9]+');

// Routes for EmployeePayrollProfile

// Create Route
Route::get('employee-payroll-profiles/create', function (\Illuminate\Http\Request $request) {
    return view('payroll::employee-payroll-profiles.create', [
        'configKey' => 'payroll.employee_payroll_profile',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('employee-payroll-profiles.create');

// Show Route
Route::get('employee-payroll-profiles/{id}', function (\Illuminate\Http\Request $request, $id) {
    return view('payroll::employee-payroll-profiles.show', [
        'recordId' => (int) $id,
        'configKey' => 'payroll.employee_payroll_profile',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('employee-payroll-profiles.show')->where('id', '[0-9]+');

// Edit Route
Route::get('employee-payroll-profiles/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
    return view('payroll::employee-payroll-profiles.edit', [
        'recordId' => (int) $id,
        'configKey' => 'payroll.employee_payroll_profile',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('employee-payroll-profiles.edit')->where('id', '[0-9]+');

// Routes for PayrollRun

// Create Route
Route::get('payroll-runs/create', function (\Illuminate\Http\Request $request) {
    return view('payroll::payroll-runs.create', [
        'configKey' => 'payroll.payroll_run',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('payroll-runs.create');

// Show Route
Route::get('payroll-runs/{id}', function (\Illuminate\Http\Request $request, $id) {
    return view('payroll::payroll-runs.show', [
        'recordId' => (int) $id,
        'configKey' => 'payroll.payroll_run',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('payroll-runs.show')->where('id', '[0-9]+');

// Edit Route
Route::get('payroll-runs/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
    return view('payroll::payroll-runs.edit', [
        'recordId' => (int) $id,
        'configKey' => 'payroll.payroll_run',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('payroll-runs.edit')->where('id', '[0-9]+');

// Routes for PayrollPayslip

// Create Route
Route::get('payroll-payslips/create', function (\Illuminate\Http\Request $request) {
    return view('payroll::payroll-payslips.create', [
        'configKey' => 'payroll.payroll_payslip',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('payroll-payslips.create');

// Show Route
Route::get('payroll-payslips/{id}', function (\Illuminate\Http\Request $request, $id) {
    return view('payroll::payroll-payslips.show', [
        'recordId' => (int) $id,
        'configKey' => 'payroll.payroll_payslip',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('payroll-payslips.show')->where('id', '[0-9]+');

// Edit Route
Route::get('payroll-payslips/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
    return view('payroll::payroll-payslips.edit', [
        'recordId' => (int) $id,
        'configKey' => 'payroll.payroll_payslip',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('payroll-payslips.edit')->where('id', '[0-9]+');

// Routes for PayrollPolicy

// Create Route
Route::get('payroll-policies/create', function (\Illuminate\Http\Request $request) {
    return view('payroll::payroll-policies.create', [
        'configKey' => 'payroll.payroll_policy',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('payroll-policies.create');

// Show Route
Route::get('payroll-policies/{id}', function (\Illuminate\Http\Request $request, $id) {
    return view('payroll::payroll-policies.show', [
        'recordId' => (int) $id,
        'configKey' => 'payroll.payroll_policy',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('payroll-policies.show')->where('id', '[0-9]+');

// Edit Route
Route::get('payroll-policies/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
    return view('payroll::payroll-policies.edit', [
        'recordId' => (int) $id,
        'configKey' => 'payroll.payroll_policy',
        'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
    ]);
})->name('payroll-policies.edit')->where('id', '[0-9]+');
