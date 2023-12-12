<?php

//use App\Http\Controllers\CriteriosController;
use App\Http\Controllers\CriterionValueController;

//Route::controller(CriteriosController::class)->group(function() {
Route::controller(CriterionValueController::class)->group(function() {

	Route::view('/', 'criterion_values.list')->name('criterion_values.list');
	// ->middleware('permission:criterio.list');

	Route::get('/search', 'search');
	Route::get('/search-wk', 'searchWk');
//	Route::get('/get-list-selects', 'getListSelects');
	Route::get('/form-selects', 'getFormSelects');

	Route::post('/store', 'store');
	Route::post('/upload', 'upload');
	Route::get('/{criterion_value}/edit', 'edit');
	Route::put('/{criterion_value}/update', 'update');
});
