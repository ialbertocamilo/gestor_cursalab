<?php

use App\Http\Controllers\CursosController;

Route::controller(CursosController::class)->group(function() {

	Route::view('/', 'cursos.list')->name('cursos.list');
	// ->middleware('permission:abconfigs.list');
	Route::get('/search', 'search')->name('modulos.escuelas.cursos.search');

	Route::view('/create', 'cursos.create_edit')->name('cursos.createCurso');
	// ->middleware('permission:cursos.create');
	Route::view('/edit/{curso}', 'cursos.create_edit')->name('cursos.editCurso');
	// ->middleware('permission:cursos.edit');

	Route::get('/search/{curso}', 'searchCurso')->name('cursos.search');
	Route::get('/form-selects', 'getFormSelects')->name('cursos.search');

	Route::post('/store', 'storeCurso')->name('cursos.storeCurso');
	Route::put('/update/{curso}', 'updateCurso')->name('cursos.updateCurso');

	Route::get('/{curso}/encuesta', 'getEncuesta')->name('modulos.escuelas.cursos.encuesta');
	Route::post('/{curso}/encuesta', 'storeUpdateEncuesta')->name('modulos.escuelas.cursos.encuesta');

	Route::post('/{curso}', 'destroyCurso')->name('cursos.destroyCurso');
	Route::post('/{curso}/mover_curso', 'moverCurso')->name('cursos.moverCurso');

	Route::put('/{curso}/status', 'updateStatus');

});

// CURSOS
Route::prefix('{curso}/temas')->group(base_path('routes/cms/temas.php'));
