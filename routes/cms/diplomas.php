<?php

use App\Http\Controllers\DiplomaController;

Route::controller(DiplomaController::class)->group(function() {

    Route::view('/', 'diploma.list')->name('diploma.list');

    Route::get('/search', 'search')->name('diploma.search');
    Route::get('/init-data', 'initData');
    Route::get('/search/{diploma}', 'searchDiploma')->name('diploma.search');
    Route::put('/update/{diploma}', 'update')->name('diploma.updateDiploma');

    Route::post('/get_preview_data', 'get_preview_data');
    Route::post('/save-font', 'saveFont');
    Route::post('/save', 'save');
    // Route::post('/get_data_diploma', 'get_data_diploma');
    Route::put('/{diploma}/status', 'status');
    Route::delete('/{diploma}/destroy', 'destroy');

});
