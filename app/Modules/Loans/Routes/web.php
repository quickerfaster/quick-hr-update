<?php

/**
 * Loans Module — Web Routes
 */

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {

    // Dashboard overview
    Route::get('/hr/dashboard-loans-overview', function () {
        return view('hr::dashboard-loans-overview');
    })->name('hr.dashboard-loans-overview');

    // Module dashboard route (required by library's ModuleServiceProvider)
    Route::redirect('/loans/dashboard', '/hr/dashboard-loans-overview')
        ->name('loans.dashboard');

    // List view (explicit route for named route support — back links, active state)
    Route::get('/hr/loans', function () {
        return view('hr::loans');
    })->name('hr.loans');

    // Redirect from module-based URL to HR context URL
    Route::redirect('/loans/loans', '/hr/loans');

    // Loan Types list view
    Route::get('/hr/loan-types', function () {
        return view('hr::loan-types');
    })->name('hr.loan-types');

    // Redirect from module-based URL to HR context URL
    Route::redirect('/loans/loan-types', '/hr/loan-types');

    // Loan Repayments list view
    Route::get('/hr/loan-repayments', function () {
        return view('hr::loan-repayments');
    })->name('hr.loan-repayments');

    // Redirect from module-based URL to HR context URL
    Route::redirect('/loans/loan-repayments', '/hr/loan-repayments');

    // Create Route — uses /hr/loans/ prefix so sidebar active state works
    Route::get('/hr/loans/create', function (\Illuminate\Http\Request $request) {
        return view('hr::loans.create', [
            'configKey' => 'loans.loan',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters']),
        ]);
    })->name('loans.create');

    // Show Route — uses /hr/loans/ prefix so sidebar "All Loans" highlights
    Route::get('/hr/loans/{id}', function (\Illuminate\Http\Request $request, $id) {
        return view('hr::loans.show', [
            'recordId' => (int) $id,
            'configKey' => 'loans.loan',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters']),
        ]);
    })->name('loans.show')->where('id', '[0-9]+');

    // Edit Route — uses /hr/loans/ prefix
    Route::get('/hr/loans/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
        return view('hr::loans.edit', [
            'recordId' => (int) $id,
            'configKey' => 'loans.loan',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters']),
        ]);
    })->name('loans.edit')->where('id', '[0-9]+');

    // Redirect old detail URLs to new /hr/loans/ prefix
    Route::redirect('/loans/{id}', '/hr/loans/{id}')->where('id', '[0-9]+');
    Route::redirect('/loans/{id}/edit', '/hr/loans/{id}/edit')->where('id', '[0-9]+');

    // Loan Types Create Route
    Route::get('/hr/loan-types/create', function (\Illuminate\Http\Request $request) {
        return view('hr::loan-types.create', [
            'configKey' => 'loans.loan_type',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters']),
        ]);
    })->name('loan-types.create');

    // Loan Types Show Route
    Route::get('/hr/loan-types/{id}', function (\Illuminate\Http\Request $request, $id) {
        return view('hr::loan-types.show', [
            'recordId' => (int) $id,
            'configKey' => 'loans.loan_type',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters']),
        ]);
    })->name('loan-types.show')->where('id', '[0-9]+');

    // Loan Types Edit Route
    Route::get('/hr/loan-types/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
        return view('hr::loan-types.edit', [
            'recordId' => (int) $id,
            'configKey' => 'loans.loan_type',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters']),
        ]);
    })->name('loan-types.edit')->where('id', '[0-9]+');

    // Redirect old loan-type detail URLs
    Route::redirect('/loan-types/{id}', '/hr/loan-types/{id}')->where('id', '[0-9]+');
    Route::redirect('/loan-types/{id}/edit', '/hr/loan-types/{id}/edit')->where('id', '[0-9]+');

    // Loan Repayments Create Route
    Route::get('/hr/loan-repayments/create', function (\Illuminate\Http\Request $request) {
        return view('hr::loan-repayments.create', [
            'configKey' => 'loans.loan_repayment',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters']),
        ]);
    })->name('loan-repayments.create');

    // Loan Repayments Show Route
    Route::get('/hr/loan-repayments/{id}', function (\Illuminate\Http\Request $request, $id) {
        return view('hr::loan-repayments.show', [
            'recordId' => (int) $id,
            'configKey' => 'loans.loan_repayment',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters']),
        ]);
    })->name('loan-repayments.show')->where('id', '[0-9]+');

    // Loan Repayments Edit Route
    Route::get('/hr/loan-repayments/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
        return view('hr::loan-repayments.edit', [
            'recordId' => (int) $id,
            'configKey' => 'loans.loan_repayment',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters']),
        ]);
    })->name('loan-repayments.edit')->where('id', '[0-9]+');

    // Redirect old loan-repayment URLs
    Route::redirect('/loan-repayments/{id}', '/hr/loan-repayments/{id}')->where('id', '[0-9]+');
    Route::redirect('/loan-repayments/{id}/edit', '/hr/loan-repayments/{id}/edit')->where('id', '[0-9]+');

});
