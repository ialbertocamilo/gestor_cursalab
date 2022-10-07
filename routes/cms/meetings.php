<?php

use App\Http\Controllers\MeetingController;
use \App\Http\Controllers\ApiRest\RestMeetingController;

Route::prefix('cuentas')->group(base_path('routes/cms/accounts.php'));

Route::get('/list', [RestMeetingController::class, 'listUserMeetings']);
Route::get('/get-data', [RestMeetingController::class, 'getData']);

Route::controller(MeetingController::class)->group(function() {

    Route::view('/', 'meetings.list')->name('meetings.list');
    // ->middleware('permission:meetings.list');

    Route::get('/search', 'search');
    Route::get('/get-list-selects', 'getListSelects');

    Route::get('/form-selects', 'getFormSelects');
    Route::get('/get-selects-search-filters', 'getSelectSearchFilters');
    Route::post('/store', 'store');
    Route::get('/{meeting}/edit', 'edit');
    Route::get('/{meeting}/duplicate-data', 'getDuplicatedData');
    Route::put('/{meeting}/update', 'update');
    Route::get('/{meeting}/show', 'show');

    Route::get('/{meeting}/update-attendance-data', 'updateAttendanceData');

    Route::put('/{meeting}/start', 'start');
    Route::put('/{meeting}/update-url-start', 'updateUrlStart');
    Route::put('/{meeting}/finish', 'finish');

    Route::put('/{meeting}/cancel', 'cancel');
    Route::delete('/{meeting}/delete', 'destroy');

    Route::get('/{meeting}/stats', 'getMeetingStats');

    Route::get('/search-attendants', 'searchAttendants');
    Route::post('/upload-attendants', 'uploadAttendants');

    Route::get('/{meeting}/export-report', 'exportMeetingReport');
    Route::get('/export-general-report', 'exportGeneralMeetingsReport');

    # initial test function
    Route::get('/test', 'testFunction');
});
