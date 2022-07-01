<?php

use App\Http\Controllers\RoleController;

Route::controller(RoleController::class)->group(function() {

	Route::get('/search', 'search');
	// Route::get('/get-list-selects', 'getListSelects');
	// Route::get('/form-selects', 'getFormSelects');

	Route::get('/create', 'create');
	Route::post('/store', 'store');

	Route::get('/{role}/edit', 'edit');
	Route::put('/{role}/update', 'update');

	Route::put('/{role}/status', 'status');
	Route::get('/{role}/show', 'show');
	Route::delete('/{role}/destroy', 'destroy');
});