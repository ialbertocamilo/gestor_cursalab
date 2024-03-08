<?php

use App\Http\Controllers\TemaController;

Route::controller(TemaController::class)->group(function() {

	Route::view('/', 'temas.list')->name('temas.list');
	// ->middleware('permission:abconfigs.list');
	Route::get('/search', 'search')->name('temas.search');

	Route::view('/create', 'temas.create_edit')->name('temas.create');
	// ->middleware('permission:temas.create');
	Route::view('/edit/{topic}', 'temas.create_edit')->name('temas.editTema');
	// ->middleware('permission:temas.editTema');

	Route::get('/form-selects', 'getFormSelects')->name('temas.form-selects');
	Route::get('/get-selects', 'getSelects');

	Route::post('/store', 'store')->name('temas.store');

	Route::put('/update/{topic}', 'update')->name('temas.update');

	Route::get('/search/{topic}', 'searchTema')->name('temas.search');

	Route::post('/{topic}', 'destroy')->name('temas.destroy');

	Route::put('/{topic}/status', 'updateStatus');

	Route::get('/{topic}/medias', 'listMedias');

	// Route::get('/{topic}/encuesta', 'getEncuesta');
	// Route::post('/{topic}/encuesta', 'storeUpdateEncuesta');
	Route::get('/{topic}/download-report-assistance', 'downloadReportAssistance');

	Route::get('/hosts', 'getHosts');
});
//encuestas
// PREGUNTAS
Route::prefix('{topic}/preguntas')->group(base_path('routes/cms/temas_preguntas.php'));
