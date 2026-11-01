<?php

/**
 * Policies Module — Web Routes
 */

use Illuminate\Support\Facades\Route;
use App\Modules\Policies\Models\CompanyPolicy;

Route::middleware(['web', 'auth'])->group(function () {

    // List view (explicit route for named route support — back links, active state)
    Route::get('/hr/company-policies', function () {
        return view('hr::company-policies');
    })->name('hr.company-policies');

    // Redirect from module-based URL to HR context URL
    // The DataTable form generates back URLs using the config key's module prefix
    // (e.g., /policies/company-policies), but the list lives under /hr/company-policies
    Route::redirect('/policies/company-policies', '/hr/company-policies');

    // Policy print/download view
    Route::get('/policies/{id}/print', function ($id) {
        $policy = CompanyPolicy::published()->findOrFail($id);
        return view('policies::policy-print', ['policy' => $policy]);
    })->name('policies.print');

    // Create Route
    Route::get('company-policies/create', function (\Illuminate\Http\Request $request) {
        return view('hr::company-policies.create', [
            'configKey' => 'policies.company_policy',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters']),
        ]);
    })->name('company-policies.create');

    // Show Route
    Route::get('company-policies/{id}', function (\Illuminate\Http\Request $request, $id) {
        return view('hr::company-policies.show', [
            'recordId' => (int) $id,
            'configKey' => 'policies.company_policy',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters']),
        ]);
    })->name('company-policies.show')->where('id', '[0-9]+');

    // Edit Route
    Route::get('company-policies/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
        return view('hr::company-policies.edit', [
            'recordId' => (int) $id,
            'configKey' => 'policies.company_policy',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters']),
        ]);
    })->name('company-policies.edit')->where('id', '[0-9]+');

});
