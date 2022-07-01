<?php

use App\Http\Controllers\TemaController;

Route::controller(TemaController::class)->group(function() {

	Route::view('/', 'temas.list')->name('temas.list');
	// ->middleware('permission:abconfigs.list');
	Route::get('/search', 'search')->name('temas.search');

	Route::view('/create', 'temas.create_edit')->name('temas.create');
	// ->middleware('permission:temas.create');
	Route::view('/edit/{tema}', 'temas.create_edit')->name('temas.editTema');
	// ->middleware('permission:temas.editTema');

	Route::get('/form-selects', 'getFormSelects')->name('temas.form-selects');

	Route::post('/store', 'store')->name('temas.store');

	Route::put('/update/{tema}', 'update')->name('temas.update');

	Route::get('/search/{tema}', 'searchTema')->name('temas.search');

	Route::post('/{tema}', 'destroy')->name('temas.destroy');

	Route::put('/{tema}/status', 'updateStatus');

});

// PREGUNTAS
Route::prefix('{tema}/preguntas')->group(base_path('routes/cms/temas_preguntas.php'));
