<?php

use App\Http\Controllers\Induction\ProcessController;

Route::controller(ProcessController::class)->group(function() {

    Route::view('/', 'processes.index')->name('process.index');

	Route::post('/store', 'store')->name('process.store');
	Route::post('/store_inline', 'storeInline')->name('process.store_inline');
	Route::put('/update/{process}', 'update')->name('process.update');

    Route::get('/search', 'search');
    Route::get('/repository_media', 'getRepositoryMediaProcess');
    Route::post('/supervisors_users', 'supervisorsUsers');
    Route::post('/segments/store', 'storeSegments');
    // Route::get('/search/{process}', 'getData')->name('process.getData');
    // Route::get('/segments/{process}', 'getSegments')->name('process.getSegments');
    // Route::post('/segments/save', 'saveSegment');
    // Route::post('/segments/users', 'usersSegmentedBenefit');
    // Route::post('/segments/enviar_correo', 'sendEmailSegments');
	// Route::get('/form-selects', 'getFormSelects');

    Route::put('/{process}/update_qualifications','updateQualificationStages');
    Route::put('/{process}/status', 'status');
    Route::delete('/{process}/destroy', 'destroy');

    // Assistants
    Route::view('{process}/asistentes', 'processes.assistants.index')->name('process.assistants.index');
    Route::get('{process}/assistants/search', 'searchAssistants');
    Route::get('{process}/assistants/info', 'loadInfoAssistants');

});

// Stages
Route::prefix('{process}/etapas')->group(base_path('routes/cms/stages.php'));
Route::prefix('{process}/diploma')->group(base_path('routes/cms/induccion/diploma.php'));
