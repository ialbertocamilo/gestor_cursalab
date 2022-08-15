<?php

use App\Http\Controllers\WorkspaceController;

Route::controller(WorkspaceController::class)->group(function() {

    Route::get('/list', 'list');
    Route::get('/configuration', 'configuration');
    Route::get('/{workspace}/edit', 'edit');
});
