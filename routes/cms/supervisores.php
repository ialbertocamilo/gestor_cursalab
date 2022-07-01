<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\SupervisoresController;

Route::controller(SupervisoresController::class)->group(function() {

	Route::view('/', 'reportes_supervisores.list')->name('supervisores');

	Route::get('/search', 'search');
	Route::get('/get-list-selects', 'getListSelects');
	Route::get('/form-selects', 'getListSelects');
	Route::post('/get-usuarios', 'getUsuarios');

	Route::post('/store-supervisor', 'storeSupervisor');
	Route::post('/delete-supervisor', 'deleteSupervisor');

	Route::get('/get-areas/{modulo}', 'getAreas');

});
