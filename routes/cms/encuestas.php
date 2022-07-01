<?php

use App\Http\Controllers\EncuestaController;

Route::controller(EncuestaController::class)->group(function() {

	Route::view('/', 'encuestas.list')->name('encuestas.list');
	// Route::view('/', 'encuestas.list')->name('encuestas.list')->middleware('permission:encuestas.list');

	Route::get('/search', 'search');
	// Route::get('/get-list-selects', 'getListSelects');
	Route::get('/form-selects', 'getFormSelects');
	Route::get('/{encuesta}/search', 'search');

	Route::get('/create', 'create');
	Route::post('/store', 'store');
	Route::get('/{encuesta}/edit', 'edit');
	Route::put('/{encuesta}/update', 'update');

	Route::put('/{encuesta}/status', 'status');
	Route::delete('/{encuesta}/destroy', 'destroy');
});

Route::prefix('{encuesta}/preguntas')->group(base_path('routes/cms/encuestas_preguntas.php'));
