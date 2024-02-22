<?php

use App\Http\Controllers\AmbienteController;

Route::controller(AmbienteController::class)->group(function() {

    Route::view('/', 'ambiente.index')->name('ambiente.index');
    Route::put('/store', 'updateStore');
    Route::get('/edit/{type}', 'edit');
});

