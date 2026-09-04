<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/organization/dashboard', function () {
        return view('organization::organization.dashboard');
    })->name('organization.dashboard');

    // Overview dashboards
    Route::get('/organization/dashboard-overview', function () {
        return view('organization::organization.dashboard');
    })->name('organization.dashboard-overview');

    Route::get('/organization/dashboard-companies-overview', function () {
        return view('organization::organization.dashboard-companies-overview');
    })->name('organization.dashboard-companies-overview');

    Route::get('/organization/dashboard-structure-overview', function () {
        return view('organization::organization.dashboard-structure-overview');
    })->name('organization.dashboard-structure-overview');

    Route::get('/organization/dashboard-locations-overview', function () {
        return view('organization::organization.dashboard-locations-overview');
    })->name('organization.dashboard-locations-overview');

    Route::get('/organization/dashboard-classification-overview', function () {
        return view('organization::organization.dashboard-classification-overview');
    })->name('organization.dashboard-classification-overview');

    Route::get('/organization/dashboard-reports-overview', function () {
        return view('organization::organization.dashboard-reports-overview');
    })->name('organization.dashboard-reports-overview');

    Route::get('/organization/reports/companies', function () {
        return view('organization::organization.reports-companies');
    })->name('organization.reports.companies');

    Route::get('/organization/reports/departments', function () {
        return view('organization::organization.reports-departments');
    })->name('organization.reports.departments');

    Route::get('/organization/reports/locations', function () {
        return view('organization::organization.reports-locations');
    })->name('organization.reports.locations');

    Route::get('/organization/reports/growth', function () {
        return view('organization::organization.reports-growth');
    })->name('organization.reports.growth');

    Route::get('/organization/dashboard-teams-overview', function () {
        return view('organization::organization.dashboard-teams-overview');
    })->name('organization.dashboard-teams-overview');

    Route::get('/organization/companies', function () {
        return view('organization::organization.companies');
    })->name('organization.companies');

    Route::get('/organization/branches', function () {
        return view('organization::organization.branches');
    })->name('organization.branches');

    Route::get('/organization/departments', function () {
        return view('organization::organization.departments');
    })->name('organization.departments');

    Route::get('/organization/divisions', function () {
        return view('organization::organization.divisions');
    })->name('organization.divisions');

    Route::get('/organization/business-units', function () {
        return view('organization::organization.business-units');
    })->name('organization.business-units');

    Route::get('/organization/locations', function () {
        return view('organization::organization.locations');
    })->name('organization.locations');

    Route::get('/organization/teams', function () {
        return view('organization::organization.teams');
    })->name('organization.teams');

    // Dashboard sub-pages (3-segment URLs not handled by catch-all)
    Route::get('/organization/dashboard/organization-summary', function () {
        return view('organization::organization.dashboard.organization-summary');
    })->name('organization.dashboard.organization-summary');

    Route::get('/organization/dashboard/growth', function () {
        return view('organization::organization.dashboard.growth');
    })->name('organization.dashboard.growth');

    Route::get('/organization/dashboard/recent-changes', function () {
        return view('organization::organization.dashboard.recent-changes');
    })->name('organization.dashboard.recent-changes');

    Route::get('/organization/organization-chart', function () {
        return view('organization::organization.organization-chart');
    })->name('organization.organization-chart');
});
