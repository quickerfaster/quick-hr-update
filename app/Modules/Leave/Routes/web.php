<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {

    Route::get('/leave/dashboard', function () {
        return view('leave::dashboard');
    })->name('leave.dashboard');

    // Routes for LeaveType
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

    Route::get('leave-requests/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
        return view('leave::leave-requests.edit', [
            'recordId' => (int) $id,
            'configKey' => 'leave.leave_request',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
        ]);
    })->name('leave-requests.edit')->where('id', '[0-9]+');

    // Routes for LeaveBalance
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

    // Routes for LeaveApprover
    Route::get('leave-approvers/create', function (\Illuminate\Http\Request $request) {
        return view('leave::leave-approvers.create', [
            'configKey' => 'leave.leave_approver',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
        ]);
    })->name('leave-approvers.create');

    Route::get('leave-approvers/{id}', function (\Illuminate\Http\Request $request, $id) {
        return view('leave::leave-approvers.show', [
            'recordId' => (int) $id,
            'configKey' => 'leave.leave_approver',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
        ]);
    })->name('leave-approvers.show')->where('id', '[0-9]+');

    Route::get('leave-approvers/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
        return view('leave::leave-approvers.edit', [
            'recordId' => (int) $id,
            'configKey' => 'leave.leave_approver',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
        ]);
    })->name('leave-approvers.edit')->where('id', '[0-9]+');

    // Dashboard
    Route::get('dashboard-leave-overview', function () {
        return view('leave::dashboard-leave-overview');
    })->name('dashboard-leave-overview');

});