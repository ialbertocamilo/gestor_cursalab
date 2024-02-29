<?php

use App\Http\Controllers\UsuarioController;


Route::controller(UsuarioController::class)->group(function () {

    // Route::get('/session', 'session');
    // Route::put('/session/workspace/{workspace}', 'updateWorkspaceInSession');

	Route::view('/', 'usuarios.list')->name('usuarios.list');
	// ->middleware('permission:usuarios.index');

	Route::get('/search', 'search');
	Route::get('/get-list-selects', 'getListSelects');
    Route::get('/users-empty-criteria', 'countUsersWithEmptyCriteria');

	Route::get('/form-selects', 'getFormSelectsV2');

	Route::post('/store', 'store');
    Route::get('/{user}/edit', 'edit');
    Route::put('/{user}/update', 'update');
    Route::get('/{user}/get-profile', 'getProfileData');
//    Route::get('/{user}/show', 'show');

    Route::put('/{user}/status', 'updateStatus');
    Route::post('/{user}/get-signature', 'getSignature');

	Route::get('{user}/reset', 'reset')->name('usuarios.reset');
	Route::post('{user}/reset_x_tema', 'reset_x_tema')->name('usuarios.reset_x_tema');
	Route::post('{user}/reset_x_curso', 'reset_x_curso')->name('usuarios.reset_x_curso');
	Route::post('{user}/reset_total', 'reset_total')->name('usuarios.reset_total');
	Route::post('{user}/reset-password', 'resetPassword')->name('usuarios.reset.password');


	Route::get('/{user}/courses-by-user', 'getCoursesByUser');

	Route::get('/{modulo}/{botica}/carreras-x-grupo', 'getCarrerasxModuloxBotica');
	Route::get('/{carrera}/{botica}/ciclos-x-carrera', 'getCiclosxCarrera');

});
