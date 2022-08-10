<?php

use App\Http\Controllers\SegmentController;

Route::controller(SegmentController::class)->group(function() {

	Route::get('/search', 'search');
	Route::get('/get-list-selects', 'getListSelects');

	Route::get('/create', 'create');
	// Route::view('/crear', 'form-data')->name('blocks.form');

	Route::post('/store', 'store');

	Route::get('/{block}/edit', 'edit');
	Route::put('/{block}/update', 'update');

	Route::delete('/{block}/destroy', 'destroy');
});

// Route::get('{block}/usuarios', 'usuarios')->name('modulos.usuarios');

