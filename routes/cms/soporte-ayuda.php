<?php

use App\Http\Controllers\AyudaAppController;

Route::controller(AyudaAppController::class)->group(function() {

	Route::view('/', 'usuario_ayuda.app_list')->name('ayuda_app.list');
	// ->middleware('permission:usuarios_ayuda.list');

	Route::get('/search', 'search');
	// Route::get('/get-list-selects', 'getListSelects');
	// Route::get('/form-selects', 'getFormSelects');

	Route::get('/create', 'create');
	Route::post('/store', 'store');
	Route::get('/{ayuda_app}/edit', 'edit');
	Route::put('/{ayuda_app}/update', 'update');
	Route::put('/{ayuda_app}/status', 'status');
	Route::delete('/{ayuda_app}/destroy', 'destroy');

});

// Route::get('{ayuda}/usuarios', 'usuarios')->name('modulos.usuarios');

