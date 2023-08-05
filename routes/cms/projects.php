<?php

use App\Http\Controllers\ProjectController;

Route::controller(ProjectController::class)->group(function() {

    Route::view('/', 'projects.list')->name('projects.list');
    // Route::post('store', 'storeSubWorkspace');
    Route::get('/search', 'searchProject');
    Route::get('/get-selects', 'getListSelects');
    // Route::get('/get-list-selects', 'getListSubworkspaceSelects');
    // Route::get('{subworkspace}/edit', 'editSubWorkspace');
    // Route::put('{subworkspace}/update', 'updateSubWorkspace');
    // Route::get('{subworkspace}/usuarios', 'usuarios');
});

?>
