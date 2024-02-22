<?php

use App\Http\Controllers\CursosController;

Route::controller(CursosController::class)->group(function() {

	Route::view('/', 'cursos.list')->name('cursos.list');
	// ->middleware('permission:abconfigs.list');
	Route::get('/search', 'search')->name('modulos.escuelas.cursos.search');

	Route::view('/create', 'cursos.create_edit')->name('cursos.createCurso');
	// ->middleware('permission:cursos.create');
	Route::view('/edit/{course}', 'cursos.create_edit')->name('cursos.editCurso');
	Route::get('/copy/{course}', 'copy')->name('cursos.copyCurso');
	Route::post('/{course}/copy-content', 'copyContent')->name('cursos.copyContent');
	// ->middleware('permission:cursos.edit');

	Route::get('/search/{course}', 'searchCurso')->name('cursos.search');
	Route::get('/form-selects', 'getFormSelects');

	Route::post('/store', 'storeCurso')->name('cursos.storeCurso');
	Route::put('/update/{course}', 'updateCurso')->name('cursos.updateCurso');

	Route::get('/{course}/encuesta', 'getEncuesta')->name('modulos.escuelas.cursos.encuesta');
	Route::post('/{course}/encuesta', 'storeUpdateEncuesta')->name('modulos.escuelas.cursos.encuesta');

	Route::post('/{course}/delete', 'destroyCurso')->name('cursos.destroyCurso');
	// Route::post('/{course}/mover_curso', 'moverCurso')->name('cursos.moverCurso');

	Route::put('/{course}/status', 'updateStatus');

	

});

// CURSOS
Route::prefix('{course}/temas')->group(base_path('routes/cms/temas.php'));
