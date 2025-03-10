<?php

use App\Http\Controllers\CursosController;

Route::controller(CursosController::class)->group(function () {

    Route::view('/', 'cursos.segmentation-list')->name('curso.list');
    Route::get('/search', 'search')->name('curso.search');
    Route::get('/get-selects', 'getSelects');

    Route::get('/download-segmentation', 'downloadCourseSegmentations');
    
    Route::view('/create', 'cursos.create_edit')->name('curso.createCurso');
    Route::view('/edit/{curso}', 'cursos.create_edit')->name('curso.editCurso');

    Route::get('/schools/get-data', 'getFiltersSelects')->name('segmentation.filters_selects');
    Route::get('/schools/subworkspace/{subworkspace}/get-data', 'getSchoolsBySubworkspace')->name('segmentation.filters_selects');
    Route::get('/form-selects', 'getFormSelects')->name('curso.form_selects_segmentation');

    Route::post('/store', 'storeCurso')->name('cursos.storeCurso');

    Route::get('/search/{course}', 'searchCursoSegmentation')->name('curso.search_segmentation');
    Route::put('/update/{course}', 'updateCursoSegmentation')->name('curso.updateCurso_segmentation');

    Route::get('/{course}/encuesta', 'getEncuestaSegmentation')->name('curso.encuesta_segmentation');
    Route::post('/{course}/encuesta', 'storeUpdateEncuestaSegmentation')->name('curso.encuesta_segmentation');

    Route::get('/{course}/compatibilities', 'getCompatibilities');
    Route::put('/{course}/compatibilities/update', 'updateCompatibilities');

    Route::post('/{curso}/delete', 'destroyCurso')->name('curso.destroyCurso');
    Route::post('/{curso}/mover_curso', 'moverCurso')->name('curso.moverCurso');

    Route::put('/{curso}/status', 'updateStatus');
	Route::get('/{course}/medias', 'listMediaTopics');
});

// CURSOS
Route::prefix('{curso}/temas')->group(base_path('routes/cms/tema.php'));
