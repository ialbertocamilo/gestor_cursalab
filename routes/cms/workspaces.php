<?php

use App\Http\Controllers\WorkspaceController;

Route::controller(WorkspaceController::class)->group(function() {

    Route::get('/list', 'list');
    Route::get('/search', 'search');
    Route::get('/{workspace}/edit', 'edit');
    Route::put('/{workspace}/update', 'update');
});
