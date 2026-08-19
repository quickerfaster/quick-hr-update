<?php

use Illuminate\Support\Facades\Route;

use App\Modules\Hr\Http\Controllers\EmployeePrintController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/hr/dashboard', function () {
        return view('hr::dashboard');
    })->name('hr.dashboard');
});

Route::middleware([
    'web',
    // InitializeTenancyByDomain::class,
    // PreventAccessFromCentralDomains::class,

])->group(function () {

    // In your web.php or hr module routes

    /*Route::get('/hr/attendance/{attendanceId}/adjust', function ($attendanceId) {
        return view('hr::adjust-attendance', ['attendanceId' => $attendanceId]);
    } )->name('attendance.adjust');*/

    Route::get('/employees/{employee}/print', [EmployeePrintController::class, 'show'])
        ->name('hr.employees.print')
        //->middleware(['auth', 'can:view,employee']);
    ;

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
