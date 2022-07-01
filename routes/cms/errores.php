<?php

use App\Http\Controllers\ErrorController;

Route::controller(ErrorController::class)->group(function() {

	Route::view('/', 'errors.list')->name('errors.list');
	// ->middleware('permission:incidencias.index');

	Route::get('/search', 'search');
	Route::get('/get-list-selects', 'getListSelects');
	Route::get('/form-selects', 'getFormSelects');

	Route::get('/create', 'create');
	Route::post('/store', 'store');

	Route::get('/{error}/edit', 'edit');
	Route::put('/{error}/update', 'update');

	// Route::put('/{error}/status', 'status');
	Route::delete('/{error}/destroy', 'destroy');
});

// Route::get('{error}/usuarios', 'usuarios')->name('modulos.usuarios');
