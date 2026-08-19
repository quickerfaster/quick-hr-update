<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Attendance\Http\Controllers\ClockEventController;

Route::middleware([
    'web',
])->group(function () {

    // Clock Event API routes (for Android sync)
    Route::post('/api/clock-events', [ClockEventController::class, 'store'])
        ->name('clock-events.store');

    Route::post('/api/clock-events/batch', [ClockEventController::class, 'storeBatch'])
        ->name('clock-events.store-batch');

});

Route::middleware(['web', 'auth'])->group(function () {

    Route::get('/attendance/dashboard', function () {
        return view('attendance::dashboard');
    })->name('attendance.dashboard');

    // Overview dashboards
    Route::get('/attendance/dashboard-time-overview', function () {
        return view('attendance::dashboard-time-overview');
    })->name('attendance.dashboard-time-overview');

    Route::get('/attendance/dashboard-policies-overview', function () {
        return view('attendance::dashboard-policies-overview');
    })->name('attendance.dashboard-policies-overview');

    Route::get('/attendance/dashboard-scheduling-overview', function () {
        return view('attendance::dashboard-scheduling-overview');
    })->name('attendance.dashboard-scheduling-overview');

    // Routes for AttendancePolicy
    Route::get('attendance-policies/create', function (\Illuminate\Http\Request $request) {
        return view('attendance::attendance-policies.create', [
            'configKey' => 'attendance.attendance_policy',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
        ]);
    })->name('attendance-policies.create');

    Route::get('attendance-policies/{id}', function (\Illuminate\Http\Request $request, $id) {
        return view('attendance::attendance-policies.show', [
            'recordId' => (int) $id,
            'configKey' => 'attendance.attendance_policy',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
        ]);
    })->name('attendance-policies.show')->where('id', '[0-9]+');

    Route::get('attendance-policies/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
        return view('attendance::attendance-policies.edit', [
            'recordId' => (int) $id,
            'configKey' => 'attendance.attendance_policy',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
        ]);
    })->name('attendance-policies.edit')->where('id', '[0-9]+');

    // Routes for Attendance
    Route::get('attendances/create', function (\Illuminate\Http\Request $request) {
        return view('attendance::attendances.create', [
            'configKey' => 'attendance.attendance',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
        ]);
    })->name('attendances.create');

    Route::get('attendances/{id}', function (\Illuminate\Http\Request $request, $id) {
        return view('attendance::attendances.show', [
            'recordId' => (int) $id,
            'configKey' => 'attendance.attendance',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
        ]);
    })->name('attendances.show')->where('id', '[0-9]+');

    Route::get('attendances/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
        return view('attendance::attendances.edit', [
            'recordId' => (int) $id,
            'configKey' => 'attendance.attendance',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
        ]);
    })->name('attendances.edit')->where('id', '[0-9]+');

    // Routes for WorkPattern
    Route::get('work-patterns/create', function (\Illuminate\Http\Request $request) {
        return view('attendance::work-patterns.create', [
            'configKey' => 'attendance.work_pattern',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
        ]);
    })->name('work-patterns.create');

    Route::get('work-patterns/{id}', function (\Illuminate\Http\Request $request, $id) {
        return view('attendance::work-patterns.show', [
            'recordId' => (int) $id,
            'configKey' => 'attendance.work_pattern',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
        ]);
    })->name('work-patterns.show')->where('id', '[0-9]+');

    Route::get('work-patterns/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
        return view('attendance::work-patterns.edit', [
            'recordId' => (int) $id,
            'configKey' => 'attendance.work_pattern',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
        ]);
    })->name('work-patterns.edit')->where('id', '[0-9]+');

});