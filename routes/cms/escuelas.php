<?php

use App\Http\Controllers\EscuelaController;

Route::controller(EscuelaController::class)->group(function() {

	Route::view('/', 'escuelas.list')->name('escuelas.list');
	// ->middleware('permission:abconfigs.list');
	Route::get('/search', 'search')->name('modulos.escuelas.search');


	Route::view('/create', 'escuelas.create_edit')->name('escuelas.create');
	// ->middleware('permission:escuelas.create');
	Route::view('/edit/{categoria}', 'escuelas.create_edit')->name('escuelas.edit');
	// ->middleware('permission:escuelas.edit');

	Route::post('/store', 'store')->name('escuelas.store');
	Route::put('/update/{categoria}', 'update')->name('escuelas.update');


	Route::get('/search/{categoria}', 'searchCategoria')->name('escuelas.searchEscuela');
	Route::get('/form-selects', 'getFormSelects')->name('escuelas.formSelects');

	Route::delete('/{categoria}', 'destroyEscuela')->name('escuelas.destroyEscuela');

	Route::put('/{categoria}/status', 'updateStatus');
});

// CURSOS
Route::prefix('{categoria}/cursos')->group(base_path('routes/cms/cursos.php'));
