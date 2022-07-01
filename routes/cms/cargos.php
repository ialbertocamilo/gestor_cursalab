<?php

use App\Http\Controllers\CargoController;

Route::controller(CargoController::class)->group(function() {

	Route::view('/', 'cargos.list')->name('cargos.list');
	// ->middleware('permission:cargos.list');

	Route::get('/search', 'search');
	// Route::get('/get-list-selects', 'getListSelects');
	// Route::get('/form-selects', 'getFormSelects');

	Route::get('/create', 'create');
	Route::post('/store', 'store');
	Route::get('/{cargo}/edit', 'edit');
	Route::put('/{cargo}/update', 'update');
	Route::delete('/{cargo}/destroy', 'destroy');
});
