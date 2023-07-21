<?php

use App\Http\Controllers\BenefitController;

Route::controller(BenefitController::class)->group(function() {

    Route::view('/', 'benefits.index')->name('benefit.index');
    Route::view('/create', 'benefits.create_edit')->name('benefit.createBenefit');
    Route::view('/edit/{benefit}', 'benefits.create_edit')->name('benefit.editBenefit');

	Route::post('/store', 'store')->name('benefit.store');
	Route::put('/update/{benefit}', 'update')->name('benefit.update');

    Route::get('/search', 'search');
    Route::get('/search/{benefit}', 'getData')->name('benefit.getData');
    Route::get('/segments/{benefit}', 'getSegments')->name('benefit.getSegments');
    Route::post('/segments/save', 'saveSegment');
	Route::get('/form-selects', 'getFormSelects');

    Route::put('/{benefit}/status', 'status');
    Route::delete('/{benefit}/destroy', 'destroy');

    // Speakers
    Route::get('/speakers/search', 'getSpeakers');

});
