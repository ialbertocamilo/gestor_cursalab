<?php

use App\Http\Controllers\Pregunta_frecuenteController;

Route::controller(Pregunta_frecuenteController::class)->group(function() {

	Route::view('/', 'pregunta_frecuentes.list')->name('pregunta_frecuentes.list');
	// ->middleware('permission:pregunta_frecuentes.list');

	Route::get('/search', 'search');
	// Route::get('/get-list-selects', 'getListSelects');
	// Route::get('/form-selects', 'getFormSelects');

	Route::get('/create', 'create');
	Route::post('/store', 'store');
	Route::get('/{pregunta_frecuente}/edit', 'edit');
	Route::put('/{pregunta_frecuente}/update', 'update');

	Route::put('/{pregunta_frecuente}/status', 'status');
	Route::delete('/{pregunta_frecuente}/destroy', 'destroy');

});

// Route::get('{pregunta_frecuente}/usuarios', 'usuarios')->name('modulos.usuarios');

