<?php

use App\Http\Controllers\AuditController;

Route::controller(AuditController::class)->group(function() {

    Route::view('/', 'audits.list')->name('audits.list');
    Route::get('/search', 'search');

    // Route::get('/{user}/last-activity', 'lastActivity');

    Route::get('/last-activity', 'lastActivity');
    Route::get('/selects', 'loadDataForSelects');
    Route::get('/{audit}/show', 'show');
});
