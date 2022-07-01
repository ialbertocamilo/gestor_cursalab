<?php

use App\Http\Controllers\EntrenamientoController;

//Route::view('/', 'entrenamiento.entrenadores.index')->name('entrenadores');

//Route::get('/search', [EntrenadorController::class, 'search']);
//Route::get('/{entrenador}/alumnos', [EntrenadorController::class, 'alumnos']);

// GENERAR DATA FAKE
//Route::get('/dataPruebaEntrenadores', 'dataPruebaEntrenadores');

Route::controller(EntrenamientoController::class)->group(function() {

	// ENTRENADORES
	Route::prefix('/entrenadores')->group(function () {
		Route::view('/', 'entrenamiento.entrenadores.index')->name('entrenamiento.entrenadores');
		// ->middleware('permission:entrenamiento.index');
		Route::get('/search', 'search');
		Route::post('/listar_entrenadores', 'listarEntrenadores');
		Route::post('/asignar', 'asignar');
		Route::post('/asignar_masivo', 'asignarMasivo');
		Route::post('/cambiar_estado_entrenador_alumno', 'cambiarEstadoEntrenadorAlumno');
		Route::post('/buscar_alumno', 'buscarAlumno');
		Route::post('/eliminar_relacion_entrenador_alumno', 'eliminarRelacionEntrenadorAlumno');

	});

	// ENTRENADORES
	Route::prefix('/alumno')->group(function () {
		Route::get('/filtrar_alumno/{dni}', 'buscarAlumnoGestor');
	});

	// CHECKLIST
	Route::prefix('/checklists')->group(function () {
		Route::view('/', 'entrenamiento.checklist.index')->name('entrenamiento.checklist');
		// ->middleware('permission:entrenamiento.index');
		Route::get('/search', 'searchChecklist');
		Route::post('/listar_checklist', 'listarChecklist');
		Route::post('/import', 'importChecklist');
		Route::post('/save_checklist', 'guardarChecklist');
		Route::post('/save_actividad_by_id', 'guardarActividadByID');
		Route::post('/delete_actividad_by_id', 'eliminarActividadByID');
		Route::post('/buscar_curso', 'buscarCurso');
	});

});
