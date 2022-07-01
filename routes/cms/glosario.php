<?php

use App\Http\Controllers\GlosarioController;

Route::controller(GlosarioController::class)->group(function() {

	Route::view('/', 'glosarios.list')->name('glosario.list');
	// ->middleware('permission:glosario.list');

	Route::get('/search', 'search');
	Route::get('/get-list-selects', 'getListSelects');
	Route::get('/form-selects', 'getFormSelects');

	Route::get('/import', 'import');
	// ->middleware('permission:glosarios.create');
	Route::post('/import', 'importFile');
	// ->middleware('permission:glosarios.create');

	Route::get('/carreras-categorias', 'carreerCategories');
	// ->middleware('permission:glosarios.create');
	Route::put('/carreras-categorias', 'carreerCategoriesStore');
	// ->middleware('permission:glosarios.create');

	Route::get('/create', 'create');
	Route::post('/store', 'store');
	Route::get('/{glosario}/edit', 'edit');
	Route::put('/{glosario}/update', 'update');
	Route::put('/{glosario}/status', 'status');
	Route::delete('/{glosario}/destroy', 'destroy');
});
