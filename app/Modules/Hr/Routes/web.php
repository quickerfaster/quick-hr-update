<?php

use Illuminate\Support\Facades\Route;

use App\Modules\Hr\Http\Controllers\EmployeePrintController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/hr/dashboard', function () {
        return view('hr::dashboard');
    })->name('hr.dashboard');

    // My Portal — Employee Self-Service dashboard
    Route::get('/hr/my-portal', function () {
        return view('hr::hr.my-portal');
    })->name('hr.dashboard-my-portal-overview');

    // ESS-scoped resource views (prevents admin view leakage)
    Route::get('/hr/my-attendance', fn () => view('hr::ess.attendance'))->name('hr.my-attendance');
    Route::get('/hr/my-leave-requests', fn () => view('hr::ess.leave-requests'))->name('hr.my-leave-requests');
    Route::get('/hr/my-clock-events', fn () => view('hr::ess.clock-events'))->name('hr.my-clock-events');
    Route::get('/hr/my-payslips-view', fn () => view('hr::ess.payslips'))->name('hr.my-payslips-view');
    Route::get('/hr/my-documents-view', fn () => view('hr::ess.documents'))->name('hr.my-documents-view');

    // Overview dashboards
    Route::get('/hr/dashboard-organization-overview', function () {
        return view('hr::dashboard-organization-overview');
    })->name('hr.dashboard-organization-overview');

    Route::get('/hr/dashboard-people-overview', function () {
        return view('hr::dashboard-people-overview');
    })->name('hr.dashboard-people-overview');

    Route::get('/hr/dashboard-manage-overview', function () {
        return view('hr::dashboard-manage-overview');
    })->name('hr.dashboard-manage-overview');

    // Issue 4: Missing page routes
    Route::get('/hr/my-documents', function () {
        return view('hr::my-documents');
    })->name('hr.my-documents');

    Route::get('/hr/my-account', function () {
        return view('hr::my-account');
    })->name('hr.my-account');

    Route::redirect('/leave/my-leave', '/hr/leave-hub');
    Route::redirect('/hr/employee-self-service', '/hr/leave-hub');
    Route::redirect('/hr/my-account', '/hr/my-profile');
    Route::redirect('/payroll/my-payslips', '/hr/my-profile');
    Route::redirect('/hr/my-documents', '/hr/my-profile');
    Route::redirect('/attendance/my-attendance', '/hr/my-profile');

    Route::get('/hr/team-calendar', function () {
        return view('hr::team-calendar');
    })->name('hr.team-calendar');

    // Issue 5: Index routes for data table pages
    Route::get('/hr/employees', function () {
        return view('hr::employees');
    })->name('hr.employees');

    Route::get('/hr/employee-groups', function () {
        return view('hr::employee-groups');
    })->name('hr.employee-groups');

    Route::get('/hr/job-titles', function () {
        return view('hr::job-titles');
    })->name('hr.job-titles');

    Route::get('/hr/teams', function () {
        return view('hr::teams');
    })->name('hr.teams');

    Route::get('/hr/tags', function () {
        return view('hr::tags');
    })->name('hr.tags');

    Route::get('/hr/employee-job-histories', function () {
        return view('hr::employee-job-histories');
    })->name('hr.employee-job-histories');

    Route::get('/hr/employee-profiles', function () {
        return view('hr::employee-profiles');
    })->name('hr.employee-profiles');

    Route::get('/hr/employee-positions', function () {
        return view('hr::employee-positions');
    })->name('hr.employee-positions');

    Route::get('/hr/locations', function () {
        return view('hr::locations');
    })->name('hr.locations');

    Route::get('/hr/companies', function () {
        return view('hr::companies');
    })->name('hr.companies');

    Route::get('/hr/departments', function () {
        return view('hr::departments');
    })->name('hr.departments');

    Route::get('/hr/documents', function () {
        return view('hr::documents');
    })->name('hr.documents');

    // Onboarding routes
    Route::get('/hr/onboarding-overview', function () {
        return view('hr::hr.onboarding-overview');
    })->name('hr.onboarding-overview');

    Route::get('/hr/invitations', function () {
        return view('hr::hr.invitations');
    })->name('hr.invitations');

    Route::get('/hr/invitation-analytics', function () {
        return view('hr::hr.invitation-analytics');
    })->name('hr.invitation-analytics');
});

/*
|--------------------------------------------------------------------------
| Post-Acceptance Onboarding Route (Phase 7 — Consolidated Wizard)
|--------------------------------------------------------------------------
|
| Single-page wizard at /onboarding replaces the old 6 separate step pages.
| The EmployeeOnboardingWizard Livewire component manages all 5 steps
| internally with a sidebar step indicator and step-by-step content area.
| Each step's child Livewire component saves independently.
*/
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/onboarding', \App\Modules\Hr\Http\Livewire\Onboarding\EmployeeOnboardingWizard::class)
        ->name('hr.onboarding.wizard');
});

Route::middleware([
    'web',
    // InitializeTenancyByDomain::class,
    // PreventAccessFromCentralDomains::class,

])->group(function () {

    // In your web.php or hr module routes

    /*Route::get('/hr/attendance/{attendanceId}/adjust', function ($attendanceId) {
        return view('attendance::adjust-attendance', ['attendanceId' => $attendanceId]);
    } )->name('attendance.adjust');*/

    Route::get('/employees/{employee}/print', [EmployeePrintController::class, 'show'])
        ->name('hr.employees.print')
        //->middleware(['auth', 'can:view,employee']);
    ;

})->middleware(['auth']);

// Routes for AttendancePolicy, Employee, EmployeeJobHistory,
// EmployeeProfile, EmployeePosition, and Location — CRUD routes
// wrapped in web + auth middleware (security fix: were previously unprotected)

Route::middleware(['web', 'auth'])->group(function () {

    // Create Route
    Route::get('attendance-policies/create', function (\Illuminate\Http\Request $request) {
        return view('attendance::attendance-policies.create', [
            'configKey' => 'hr.attendance_policy',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
        ]);
    })->name('attendance-policies.create');

    // Show Route
    Route::get('attendance-policies/{id}', function (\Illuminate\Http\Request $request, $id) {
        return view('attendance::attendance-policies.show', [
            'recordId' => (int) $id,
            'configKey' => 'hr.attendance_policy',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
        ]);
    })->name('attendance-policies.show')->where('id', '[0-9]+');

    // Edit Route
    Route::get('attendance-policies/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
        return view('attendance::attendance-policies.edit', [
            'recordId' => (int) $id,
            'configKey' => 'hr.attendance_policy',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
        ]);
    })->name('attendance-policies.edit')->where('id', '[0-9]+');

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
    })->name('employees.edit')->where('id', '[0-9]+');

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
    })->name('employee-job-histories.edit')->where('id', '[0-9]+');

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
    })->name('employee-profiles.edit')->where('id', '[0-9]+');

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
    })->name('employee-positions.edit')->where('id', '[0-9]+');

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

});
