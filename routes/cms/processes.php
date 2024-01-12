<?php

use App\Http\Controllers\Induction\ProcessController;

Route::controller(ProcessController::class)->group(function() {

    Route::view('/', 'processes.index')->name('process.index');

	Route::post('/store', 'store')->name('process.store');
	Route::post('/store_inline', 'storeInline')->name('process.store_inline');
	Route::put('/update/{process}', 'update')->name('process.update');

    Route::get('/search', 'search');
    // Route::get('/search/{process}', 'getData')->name('process.getData');
    // Route::get('/segments/{process}', 'getSegments')->name('process.getSegments');
    // Route::post('/segments/save', 'saveSegment');
    // Route::post('/segments/users', 'usersSegmentedBenefit');
    // Route::post('/segments/enviar_correo', 'sendEmailSegments');
	// Route::get('/form-selects', 'getFormSelects');

    Route::put('/{process}/status', 'status');
    Route::delete('/{process}/destroy', 'destroy');

});

// Stages
Route::prefix('{process}/etapas')->group(base_path('routes/cms/stages.php'));
