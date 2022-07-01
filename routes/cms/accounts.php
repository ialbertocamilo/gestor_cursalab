<?php

use App\Http\Controllers\AccountController;

// Route::get('accounts/index', 'AccountController@index')->name('accounts.index');
// Route::get('accounts/getInitialData', 'AccountController@getInitialData');
// Route::post('accounts/create', 'AccountController@create');
// Route::post('accounts/edit', 'AccountController@edit');
// Route::post('accounts/generarToken', 'AccountController@generarToken');

Route::controller(AccountController::class)->group(function() {

	Route::view('/', 'meetings.accounts.list')->name('accounts.list');
	// ->middleware('permission:accounts.list');

	Route::get('/search', 'search');
	Route::get('/get-list-selects', 'getListSelects');
	// Route::get('/form-selects', 'getFormSelects');

	Route::get('/create', 'create');
	Route::post('/store', 'store');

	Route::get('/{account}/edit', 'edit');
	Route::put('/{account}/update', 'update');

	Route::put('/{account}/status', 'status');
	Route::put('/{account}/token', 'generarToken');
	Route::delete('/{account}/destroy', 'destroy');
});

// Route::get('{account}/usuarios', 'usuarios')->name('modulos.usuarios');

