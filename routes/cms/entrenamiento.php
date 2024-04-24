<?php

use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\EntrenamientoController;

//Route::view('/', 'entrenamiento.entrenadores.index')->name('entrenadores');

//Route::get('/search', [EntrenadorController::class, 'search']);
//Route::get('/{entrenador}/alumnos', [EntrenadorController::class, 'alumnos']);

// GENERAR DATA FAKE
//Route::get('/dataPruebaEntrenadores', 'dataPruebaEntrenadores');

Route::controller(EntrenamientoController::class)->group(function() {

	// ENTRENADORES
	Route::prefix('/entrenadores')->middleware('hasHability:trainer')->group(function () {
		Route::view('/', 'entrenamiento.entrenadores.index')->name('entrenamiento.entrenadores');
		// ->middleware('permission:entrenamiento.index');
		Route::get('/search', 'search');
		Route::get('/list-students/{entrenador_id}', 'listStudents');
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
	Route::prefix('/checklists')->middleware('hasHability:checklist')->group(function () {
		Route::view('/', 'entrenamiento.checklist.index')->name('entrenamiento.checklist');
		// ->middleware('permission:entrenamiento.index');

		Route::get('/search', 'searchChecklist');
		Route::get('/init-data', 'getInitData');

		Route::post('/search_checklist', 'searchChecklistByID');
		Route::post('/listar_checklist', 'listarChecklist');
		Route::post('/import', 'importChecklist');
		Route::post('/save_checklist', 'guardarChecklist');
		Route::post('/save_actividad_by_id', 'guardarActividadByID');
		Route::post('/delete_actividad_by_id', 'eliminarActividadByID');
		Route::post('/buscar_curso', 'buscarCurso');
		Route::delete('/{id}/destroy', 'deleteChecklist');
        Route::put('/{checklist}/status', 'status');
	});
});
//apis to checklist v3
Route::controller(ChecklistController::class)->group(function() {
	Route::prefix('/checklist')->middleware('hasHability:checklist')->group(function () {
		Route::prefix('/v2')->group(function () {
			Route::get('/search', 'searchChecklist');
			Route::get('/{checklist}/verify-next-step', 'verifyNextStep');
			Route::get('/form-selects', 'getFormSelects');
			Route::post('/store', 'storeChecklist');
			Route::get('/{checklist}/edit', 'editChecklist');
			Route::put('/{checklist}/update', 'updateChecklist');

			Route::get('/activity/form-selects', 'formSelectsActivities');
			Route::get('/{checklist}/activities', 'listActivitiesByChecklist');
			Route::post('/{checklist}/activities/save', 'saveActivitiesByChecklist');

			Route::get('/segments/{checklist}', 'getSegments')->name('checklist.getSegments');
			Route::get('/{checklist}/supervisor-segmentation', 'supervisorSegmentation')->name('checklist.supervisorSegmentation');
			Route::post('/{checklist}/save-supervisor-segmentation', 'saveSupervisorSegmentation')->name('checklist.saveSupervisorSegmentation');
		});
	});
});

?>
