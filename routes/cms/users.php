<?php

use App\Http\Controllers\UserController;

Route::controller(UserController::class)->group(function() {

	Route::get('/search', 'search');
	Route::get('/get-form-data', 'getFormData');

	Route::put('/update-profile', 'updateProfile');
	Route::put('/update-phones', 'updatePhones');
	Route::put('/update-password', 'updatePassword');
	
	Route::get('/create', 'create');
	Route::post('/store', 'store');

	Route::get('/{user}/edit', 'edit');
	Route::put('/{user}/update', 'update');
	Route::get('/{user}/show', 'show');

	Route::put('/{user}/status', 'status');
	Route::delete('/{user}/destroy', 'destroy');

});

