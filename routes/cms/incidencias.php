<?php

use App\Http\Controllers\IncidenciaController;

Route::controller(IncidenciaController::class)->group(function() {

	Route::view('/', 'incidencias.list')->name('incidencias.list');
	// ->middleware('permission:incidencias.list');

	Route::get('/search', 'search');
	Route::get('/get-list-selects', 'getListSelects');
	Route::get('/form-selects', 'getFormSelects');

	Route::get('/{incidencia}/edit', 'edit');

	Route::delete('/{incidencia}/destroy', 'destroy');

	// Route::get('{incidencia}/usuarios', 'usuarios')->name('modulos.usuarios');
});
