<?php

use App\Http\Controllers\CriteriosController;

Route::controller(CriteriosController::class)->group(function() {

	Route::view('/', 'criterios.list')->name('criterio.list');
	// ->middleware('permission:criterio.list');

	Route::get('/search', 'search');
	Route::get('/get-list-selects', 'getListSelects');
	Route::get('/form-selects', 'getFormSelects');

	Route::get('/create', 'create');
	Route::post('/store', 'store');
	Route::get('/{criterio}/edit', 'edit');
	Route::put('/{criterio}/update', 'update');
});
