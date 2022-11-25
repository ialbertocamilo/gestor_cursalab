<?php

use App\Http\Controllers\CursosController;

Route::controller(CursosController::class)->group(function () {

    Route::view('/', 'cursos.segmentation-list')->name('curso.list');
    Route::get('/search', 'search')->name('curso.search');

    Route::view('/create', 'cursos.create_edit')->name('curso.createCurso');
    Route::view('/edit/{curso}', 'cursos.create_edit')->name('curso.editCurso');
    Route::get('/search/{curso}', 'searchCurso')->name('curso.search');
    Route::get('/form-selects', 'getFormSelects')->name('curso.search');

    Route::post('/store', 'storeCurso')->name('curso.storeCurso');
    Route::put('/update/{curso}', 'updateCurso')->name('curso.updateCurso');

    Route::get('/{curso}/encuesta', 'getEncuesta')->name('curso.encuesta');
    Route::post('/{curso}/encuesta', 'storeUpdateEncuesta')->name('curso.encuesta');

    Route::post('/{curso}', 'destroyCurso')->name('curso.destroyCurso');
    Route::post('/{curso}/mover_curso', 'moverCurso')->name('curso.moverCurso');

    Route::put('/{curso}/status', 'updateStatus');

    Route::get('/{course}/compatibilities', 'getCompatibilities');
    Route::put('/{course}/compatibilities/update', 'updateCompatibilities');
});

// CURSOS
Route::prefix('{curso}/temas')->group(base_path('routes/cms/tema.php'));
