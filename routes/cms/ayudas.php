<?php

use App\Http\Controllers\AyudaController;

Route::controller(AyudaController::class)->group(function() {

	Route::view('/', 'ayudas.list')->name('ayudas.list');
	// ->middleware('permission:ayudas.list');

	Route::get('/search', 'search');
	// Route::get('/get-list-selects', 'getListSelects');
	// Route::get('/form-selects', 'getFormSelects');

	Route::get('/create', 'create');
	Route::post('/store', 'store');
	Route::get('/{ayuda}/edit', 'edit');
	Route::put('/{ayuda}/update', 'update');
	Route::put('/{ayuda}/status', 'status');
	Route::delete('/{ayuda}/destroy', 'destroy');

	// Route::get('{ayuda}/usuarios', 'usuarios')->name('modulos.usuarios');
});
