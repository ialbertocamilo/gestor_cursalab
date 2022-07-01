<?php

use App\Http\Controllers\TipoCriterioController;

Route::controller(TipoCriterioController::class)->group(function() {

	Route::view('/', 'tipo_criterio.list')->name('tipo_criterio.list');
	// ->middleware('permission:tipo_criterio.list');

	Route::get('/search', 'search');
	// Route::get('/get-list-selects', 'getListSelects');
	// Route::get('/form-selects', 'getFormSelects');

	Route::get('/create', 'create');
	Route::post('/store', 'store');
	Route::get('/{tipo_criterio}/edit', 'edit');
	Route::put('/{tipo_criterio}/update', 'update');

});

// Route::get('{anuncio}/usuarios', 'usuarios')->name('modulos.usuarios');

// CRITERIOS
Route::prefix('{tipo_criterio}/criterios')->group(base_path('routes/cms/criterios.php'));