<?php

use App\Http\Controllers\MediaController;

Route::controller(MediaController::class)->group(function() {

	Route::view('/', 'media.list')->name('multimedia.index');

	Route::get('/search', 'search');
	Route::get('/get-list-selects', 'getListSelects');
	Route::get('/show/{multimedia}', 'show');
	Route::get('/eliminar/{id}', 'MediaController@eliminar')->name('media.eliminar');

	Route::post('/upload', 'upload');
	Route::get('/{media}/download', 'downloadExternalFile');
});
