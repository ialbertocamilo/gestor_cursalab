<?php

use App\Http\Controllers\AbconfigController;
// use App\Http\Controllers\PosteoController;

Route::controller(AbconfigController::class)->group(function() {

	Route::view('/', 'abconfigs.list')->name('abconfigs.list');
	// ->middleware('permission:abconfigs.list');

	Route::get('/search', 'search');
	Route::get('/get-selects', 'getSelects');
	Route::get('/form-selects', 'getFormSelects');
	Route::get('/{abconfig}/search', 'searchModulo');

	Route::get('{abconfig}/usuarios', 'usuarios')->name('modulos.usuarios');
	Route::post('store', 'storeModulo')->name('modulos.storeModulo');
	Route::put('{modulo}/update', 'updateModulo')->name('modulos.updateModulo');
});

// ESCUELAS
Route::prefix('{abconfig}/escuelas')->group(base_path('routes/cms/escuelas.php'));
