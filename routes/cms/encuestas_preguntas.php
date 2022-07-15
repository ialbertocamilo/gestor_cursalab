<?php

use App\Http\Controllers\PollQuestionController;

Route::controller(PollQuestionController::class)->group(function() {

    // Route::view('/', 'encuestas_preguntas.list')->name('encuestas_preguntas.list')->middleware('permission:encuestas_preguntas.list');
    Route::view('/', 'encuestas_preguntas.list')->name('encuestas_preguntas.list');

    Route::get('/search', 'search');
    // Route::get('/get-list-selects', 'getListSelects');
    // Route::get('/form-selects', 'getFormSelects');

    Route::get('/create', 'create');
    Route::post('/store', 'store');
    Route::get('/{pollquestion}/edit', 'edit');
    Route::put('/{pollquestion}/update', 'update');
    Route::put('/{pollquestion}/status', 'status');
    Route::delete('/{pollquestion}/destroy', 'destroy');

});
