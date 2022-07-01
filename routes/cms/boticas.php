<?php

use App\Http\Controllers\BoticaController;

Route::controller(BoticaController::class)->group(function() {

	Route::view('/', 'boticas.list')->name('boticas.list');
	// ->middleware('permission:boticas.list');

	Route::get('/search', 'search');
	Route::get('/search-no-paginate', 'searchNoPaginate');
	Route::get('/get-groups', 'getGroupsByModule');
	Route::get('/get-list-selects', 'getListSelects');

	Route::get('/create', 'create');
	Route::post('/store', 'store');
	Route::get('/{botica}/edit', 'edit');
	Route::put('/{botica}/update', 'update');

	Route::delete('/{botica}/destroy', 'destroy');

// Route::get('{botica}/usuarios', 'usuarios')->name('modulos.usuarios');

});
