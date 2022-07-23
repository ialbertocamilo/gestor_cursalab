<?php

use App\Http\Controllers\FaqsController;

Route::controller(FaqsController::class)->group(function() {

    Route::view('/', 'pregunta_frecuentes.list')->name('pregunta_frecuentes.list');
    // ->middleware('permission:pregunta_frecuentes.list');

    Route::get('/search', 'search');
    // Route::get('/get-list-selects', 'getListSelects');
    // Route::get('/form-selects', 'getFormSelects');

    Route::get('/create', 'create');
    Route::post('/store', 'store');
    Route::get('/{post}/edit', 'edit');
    Route::put('/{post}/update', 'update');

    Route::put('/{post}/status', 'status');
    Route::delete('/{post}/destroy', 'destroy');

});

// Route::get('{pregunta_frecuente}/usuarios', 'usuarios')->name('modulos.usuarios');

