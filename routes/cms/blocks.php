<?php

use App\Http\Controllers\LearningBlockController;

Route::controller(LearningBlockController::class)->group(function() {

	Route::view('/', 'learning.blocks.list')->name('blocks.list');
	// ->middleware('permission:blocks.list');

	Route::get('/search', 'search');
	Route::get('/get-list-selects', 'getListSelects');
	// Route::get('/form-selects', 'getFormSelects');

	// Route::get('/create', 'create');
	Route::view('/create', 'learning.blocks.form-data')->name('blocks.form');
	Route::view('/create-rutas', 'learning.blocks.form-courses')->name('blocks.form');

	Route::post('/store', 'store');

	Route::get('/{block}/edit', 'edit');
	Route::put('/{block}/update', 'update');

	Route::delete('/{block}/destroy', 'destroy');
});

// Route::get('{block}/usuarios', 'usuarios')->name('modulos.usuarios');

