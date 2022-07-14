<?php

use App\Http\Controllers\PollController;

Route::controller(PollController::class)->group(function() {

    Route::view('/', 'encuestas.list')->name('encuestas.list');
    // Route::view('/', 'encuestas.list')->name('encuestas.list')->middleware('permission:encuestas.list');

    Route::get('/search', 'search');
    // Route::get('/get-list-selects', 'getListSelects');
    Route::get('/form-selects', 'getFormSelects');
    Route::get('/{poll}/search', 'search');

    Route::get('/create', 'create');
    Route::post('/store', 'store');
    Route::get('/{poll}/edit', 'edit');
    Route::put('/{poll}/update', 'update');

    Route::put('/{poll}/status', 'status');
    Route::delete('/{poll}/destroy', 'destroy');
});

Route::prefix('/{poll}/preguntas')
    ->group(base_path('routes/cms/encuestas_preguntas.php'));
