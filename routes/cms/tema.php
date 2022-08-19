<?php

use App\Http\Controllers\TemaController;

Route::controller(TemaController::class)->group(function () {

    Route::view('/', 'temas.list')->name('tema.list');
    // ->middleware('permission:abconfigs.list');
    Route::get('/search', 'search')->name('tema.search');

    Route::view('/create', 'temas.create_edit')->name('tema.create');
    // ->middleware('permission:temas.create');
    Route::view('/edit/{tema}', 'temas.create_edit')->name('tema.editTema');
    // ->middleware('permission:temas.editTema');

    Route::get('/form-selects', 'getFormSelects')->name('tema.form-selects');

    Route::post('/store', 'store')->name('tema.store');

    Route::put('/update/{tema}', 'update')->name('tema.update');

    Route::get('/search/{tema}', 'searchTema')->name('tema.search');

    Route::post('/{tema}', 'destroy')->name('tema.destroy');

    Route::put('/{tema}/status', 'updateStatus');
});

// PREGUNTAS
Route::prefix('{tema}/preguntas')->group(base_path('routes/cms/tema_preguntas.php'));
