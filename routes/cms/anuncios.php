<?php

use App\Http\Controllers\AnuncioController;

Route::controller(AnuncioController::class)->group(function() {

	Route::view('/', 'anuncios.list')->name('anuncios.list');
	// ->middleware('permission:anuncios.list');

	Route::get('/set-dates', 'setPublicationDates');
	Route::get('/search', 'search');
	Route::get('/get-list-selects', 'getListSelects');
	Route::get('/form-selects', 'getFormSelects');

	Route::get('/create', 'create');
	Route::post('/store', 'store');

	Route::get('/{anuncio}/edit', 'edit');
	Route::put('/{anuncio}/update', 'update');

	Route::put('/{anuncio}/status', 'status');
	Route::delete('/{anuncio}/destroy', 'destroy');

	// Route::get('{anuncio}/usuarios', 'usuarios')->name('modulos.usuarios');
});
