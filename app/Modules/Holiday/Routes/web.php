<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/holiday/dashboard', function () {
        return view('holiday::dashboard');
    })->name('holiday.dashboard');

    // Holiday Calendars
    Route::get('/holiday/holiday-calendars', function () {
        return view('holiday::holiday-calendars');
    })->name('holiday.holiday-calendars');

    // Holidays
    Route::get('/holiday/holidays', function () {
        return view('holiday::holidays');
    })->name('holiday.holidays');

    // Holiday Batch Creation
    Route::get('/holiday/holiday-batch-creation', function () {
        return view('holiday::holiday-batch-creation');
    })->name('holiday.holiday-batch-creation');

    // Holiday CRUD routes
    Route::get('/holiday/holidays/create', function (\Illuminate\Http\Request $request) {
        return view('holiday::holidays.create', [
            'configKey' => 'holiday.holiday',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
        ]);
    })->name('holiday.holidays.create');

    Route::get('/holiday/holidays/{id}', function (\Illuminate\Http\Request $request, $id) {
        return view('holiday::holidays.show', [
            'recordId' => (int) $id,
            'configKey' => 'holiday.holiday',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
        ]);
    })->name('holiday.holidays.show')->where('id', '[0-9]+');

    Route::get('/holiday/holidays/{id}/edit', function (\Illuminate\Http\Request $request, $id) {
        return view('holiday::holidays.edit', [
            'recordId' => (int) $id,
            'configKey' => 'holiday.holiday',
            'returnParams' => $request->only(['page', 'perPage', 'search', 'sort', 'activeFilters'])
        ]);
    })->name('holiday.holidays.edit')->where('id', '[0-9]+');
});