<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {

    Route::get('/leave/dashboard', function () {
        return view('leave::dashboard');
    })->name('leave.dashboard');

    // My Leave — Employee Self-Service view
    Route::get('/leave/my-leave', function () {
        return view('leave::leave.my-leave');
    })->name('leave.my-leave');

    // Routes for LeaveType
    Route::get('/leave/leave-types', function () {
        return view('leave::leave-types');
    })->name('leave.leave-types');

    Route::get('leave-types/create', function (\Illuminate\Http\Request $request) {
        return view('leave::leave-types.create', [
            'configKey' => 'leave.leave_type',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
        ]);
    })->name('leave-types.create');

    Route::get('leave-types/{id}', function (\Illuminate\Http\Request $request, $id) {
        return view('leave::leave-types.show', [
            'recordId' => (int) $id,
            'configKey' => 'leave.leave_type',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
        ]);
    })->name('leave-types.show')->where('id', '[0-9]+');

    Route::get('leave-types/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
        return view('leave::leave-types.edit', [
            'recordId' => (int) $id,
            'configKey' => 'leave.leave_type',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
        ]);
    })->name('leave-types.edit')->where('id', '[0-9]+');

    // Routes for LeaveRequest
    Route::get('/leave/leave-requests', function () {
        return view('leave::leave-requests');
    })->name('leave.leave-requests');

    Route::get('leave-requests/create', function (\Illuminate\Http\Request $request) {
        return view('leave::leave-requests.create', [
            'configKey' => 'leave.leave_request',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
        ]);
    })->name('leave-requests.create');

    Route::get('leave-requests/{id}', function (\Illuminate\Http\Request $request, $id) {
        return view('leave::leave-requests.show', [
            'recordId' => (int) $id,
            'configKey' => 'leave.leave_request',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
        ]);
    })->name('leave-requests.show')->where('id', '[0-9]+');

    // Approvals
    Route::get('/leave/approvals', function () {
        return view('leave::approvals');
    })->name('leave.approvals');

    Route::get('leave-requests/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
        return view('leave::leave-requests.edit', [
            'recordId' => (int) $id,
            'configKey' => 'leave.leave_request',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
        ]);
    })->name('leave-requests.edit')->where('id', '[0-9]+');

    // Routes for LeaveBalance
    Route::get('/leave/leave-balances', function () {
        return view('leave::leave-balances');
    })->name('leave.leave-balances');

    Route::get('leave-balances/create', function (\Illuminate\Http\Request $request) {
        return view('leave::leave-balances.create', [
            'configKey' => 'leave.leave_balance',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
        ]);
    })->name('leave-balances.create');

    Route::get('leave-balances/{id}', function (\Illuminate\Http\Request $request, $id) {
        return view('leave::leave-balances.show', [
            'recordId' => (int) $id,
            'configKey' => 'leave.leave_balance',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
        ]);
    })->name('leave-balances.show')->where('id', '[0-9]+');

    Route::get('leave-balances/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
        return view('leave::leave-balances.edit', [
            'recordId' => (int) $id,
            'configKey' => 'leave.leave_balance',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
        ]);
    })->name('leave-balances.edit')->where('id', '[0-9]+');

    // Overview dashboards
    Route::get('/leave/dashboard-leave-overview', function () {
        return view('leave::dashboard-leave-overview');
    })->name('leave.dashboard-leave-overview');

    Route::get('/leave/dashboard-requests-overview', function () {
        return view('leave::dashboard-requests-overview');
    })->name('leave.dashboard-requests-overview');

    Route::get('/leave/dashboard-configuration-overview', function () {
        return view('leave::dashboard-configuration-overview');
    })->name('leave.dashboard-configuration-overview');

});
