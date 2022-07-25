<?php

use App\Http\Controllers\LearningBlockController;

Route::controller(LearningBlockController::class)->group(function() {

	Route::view('/', 'meetings.blocks.list')->name('blocks.list');
	// ->middleware('permission:blocks.list');

	Route::get('/search', 'search');
	Route::get('/get-list-selects', 'getListSelects');
	// Route::get('/form-selects', 'getFormSelects');

	Route::get('/create', 'create');
	Route::post('/store', 'store');

	Route::get('/{block}/edit', 'edit');
	Route::put('/{block}/update', 'update');

	Route::put('/{block}/status', 'status');
	Route::put('/{block}/token', 'generarToken');
	Route::delete('/{block}/destroy', 'destroy');
});

// Route::get('{block}/usuarios', 'usuarios')->name('modulos.usuarios');

