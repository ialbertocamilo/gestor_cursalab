<?php

use App\Http\Controllers\AyudaAppController;

Route::controller(AyudaAppController::class)->group(function() {

    Route::view('/', 'usuario_ayuda.app_list')->name('ayuda_app.list');
    // ->middleware('permission:usuarios_ayuda.list');

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

// Route::get('{ayuda}/usuarios', 'usuarios')->name('modulos.usuarios');

