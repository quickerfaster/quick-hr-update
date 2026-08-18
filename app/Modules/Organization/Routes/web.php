<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/organization/dashboard', function () {
        return view('organization::organization.dashboard');
    })->name('organization.dashboard');

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
});