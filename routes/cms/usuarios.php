<?php

use App\Http\Controllers\UsuarioController;


Route::controller(UsuarioController::class)->group(function () {

	Route::view('/', 'usuarios.list')->name('usuarios.list');
	// ->middleware('permission:usuarios.index');

	Route::get('/search', 'search');
	Route::get('/get-list-selects', 'getListSelects');
	Route::get('/{usuario}/search', 'searchUser');
	Route::get('/form-selects', 'getFormSelects');
	Route::put('/{usuario}/status', 'updateStatus');

	Route::get('{usuario}/reset', 'reset')->name('usuarios.reset');
	Route::post('{usuario}/reset_x_tema', 'reset_x_tema')->name('usuarios.reset_x_tema');
	Route::post('{usuario}/reset_x_curso', 'reset_x_curso')->name('usuarios.reset_x_curso');
	Route::post('{usuario}/reset_total', 'reset_total')->name('usuarios.reset_total');

	Route::get('/{usuario}/cursos-x-usuario', 'getCursosxUsuario');

	Route::get('/{modulo}/{botica}/carreras-x-grupo', 'getCarrerasxModuloxBotica');
	Route::get('/{carrera}/{botica}/ciclos-x-carrera', 'getCiclosxCarrera');

});
