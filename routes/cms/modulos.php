<?php

use App\Http\Controllers\WorkspaceController;

Route::controller(WorkspaceController::class)->group(function() {

    Route::view('/', 'abconfigs.list')->name('abconfigs.list');
    Route::post('store', 'storeSubWorkspace');
    Route::get('/search', 'searchSubWorkspace');
    Route::get('/copy/{subworkspace}', 'copy');
    Route::get('/form-selects', 'getFormSelects');
    Route::get('/get-list-selects', 'getListSubworkspaceSelects');
    Route::get('{subworkspace}/edit', 'editSubWorkspace');
    Route::put('{subworkspace}/update', 'updateSubWorkspace');
    Route::get('{subworkspace}/usuarios', 'usuarios');
    Route::post('{subworkspace}/copy-content', 'copyContent');
});

// ESCUELAS
// Route::prefix('{workspace}/escuelas')->group(base_path('routes/cms/escuelas.php'));
