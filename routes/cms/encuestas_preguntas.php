<?php

use App\Http\Controllers\Encuestas_preguntaController;

Route::controller(Encuestas_preguntaController::class)->group(function() {

// Route::view('/', 'encuestas_preguntas.list')->name('encuestas_preguntas.list')->middleware('permission:encuestas_preguntas.list');
Route::view('/', 'encuestas_preguntas.list')->name('encuestas_preguntas.list');

Route::get('/search', 'search');
// Route::get('/get-list-selects', 'getListSelects');
// Route::get('/form-selects', 'getFormSelects');

Route::get('/create', 'create');
Route::post('/store', 'store');
Route::get('/{encuestas_pregunta}/edit', 'edit');
Route::put('/{encuestas_pregunta}/update', 'update');
Route::put('/{encuestas_pregunta}/status', 'status');
Route::delete('/{encuestas_pregunta}/destroy', 'destroy');

});
