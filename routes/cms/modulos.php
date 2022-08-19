<?php

use App\Http\Controllers\WorkspaceController;

Route::controller(WorkspaceController::class)->group(function() {

	Route::view('/', 'abconfigs.list')->name('abconfigs.list');
	Route::post('store', 'storeSubWorkspace');
	Route::get('/search', 'searchSubWorkspace');
	Route::get('/form-selects', 'getFormSelects');
	Route::get('{subworkspace}/edit', 'editSubWorkspace');
	Route::put('{subworkspace}/update', 'updateSubWorkspace');
	Route::get('{subworkspace}/usuarios', 'usuarios');
});

// ESCUELAS
// Route::prefix('{workspace}/escuelas')->group(base_path('routes/cms/escuelas.php'));
