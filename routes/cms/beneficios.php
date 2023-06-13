<?php

use App\Http\Controllers\BenefitController;

Route::controller(BenefitController::class)->group(function() {

    Route::view('/', 'benefits.index')->name('benefit.index');
    Route::view('/create', 'benefits.create_edit')->name('benefit.createCurso');
    Route::view('/edit/{benefit}', 'benefits.create_edit')->name('benefit.editCurso');

	Route::post('/store', 'store')->name('benefit.store');
	Route::put('/update/{course}', 'update')->name('benefit.update');

    Route::get('/search', 'search');
	Route::get('/form-selects', 'getFormSelects');

});
