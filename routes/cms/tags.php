<?php

use App\Http\Controllers\TagsController;

Route::controller(TagsController::class)->group(function() {

	Route::view('/', 'tags.list')->name('tags.list');
	// ->middleware('permission:tags.list');

	Route::get('/search', 'search');
	// Route::get('/form-selects', 'getFormSelects');
	// Route::get('/get-list-selects', 'getListSelects');

	Route::get('/create', 'create');
	Route::post('/store', 'store');

	Route::get('/{tag}/edit', 'edit');
	Route::put('/{tag}/update', 'update');

	Route::put('/{tag}/status', 'status');
	Route::delete('/{tag}/destroy', 'destroy');

});

// Route::post('tags', 'TagsController@get');
// Route::post('tags/store', 'TagsController@store')->name('tags.store')->middleware('permission:ayudas.create');
// Route::get('tags/index', 'TagsController@index')->name('tags.index')->middleware('permission:ayudas.index');
// Route::get('tags/create', 'TagsController@create')->name('tags.create')->middleware('permission:ayudas.create');
// Route::put('tags/{tag}', 'TagsController@update')->name('tags.update')->middleware('permission:ayudas.edit');
// Route::delete('tags/{tag}', 'TagsController@destroy')->name('tags.destroy')->middleware('permission:ayudas.destroy');
// Route::get('tags/{tag}/edit', 'TagsController@edit')->name('tags.edit')->middleware('permission:ayudas.edit');