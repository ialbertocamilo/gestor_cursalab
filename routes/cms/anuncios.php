<?php

use App\Http\Controllers\AnnouncementController;

Route::controller(AnnouncementController::class)->group(function() {

    Route::view('/', 'anuncios.list')->name('anuncios.list');

    Route::get('/set-dates', 'setPublicationDates');
    Route::get('/search', 'search');
    Route::get('/get-list-selects', 'getListSelects');
    Route::get('/form-selects', 'getFormSelects');

    Route::get('/create', 'create');
    Route::post('/store', 'store');

    Route::get('/{announcement}/edit', 'edit');
    Route::put('/{announcement}/update', 'update');

    Route::put('/{announcement}/status', 'status');
    Route::delete('/{announcement}/destroy', 'destroy');

});
