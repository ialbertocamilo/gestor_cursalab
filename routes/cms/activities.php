<?php

use App\Http\Controllers\Induction\ActivityController;

Route::controller(ActivityController::class)->group(function() {




    // Route::view('/', 'stages.index')->name('stages.index');
    // Route::get('/search', 'search');

    // Route::post('/store','store');
    // Route::get('/{stage}/edit', 'edit');
    Route::post('/{activity}/update','update');
    Route::put('/{activity}/status', 'status');
    Route::delete('/{activity}/delete', 'destroy');

    // Tareas
    Route::post('/tareas/store','TareasStore');
    Route::get('/tareas/form-selects', 'ActivitiesGetFormSelects');
    Route::get('/tareas/edit/{activity}', 'editActivityTareas');

    // Sesiones
    Route::post('/sesiones/store','SesionesStore');
    Route::get('/sesiones/get-list-selects', 'getListSelects');
    Route::get('/sesiones/form-selects', 'SesionesGetFormSelects');

    // Temas
    Route::post('/temas/store','TemasStore');
    // Route::get('/temas/get-list-selects', 'getListSelects');
    Route::get('/temas/form-selects', 'TemasGetFormSelects');

    // Checklist
    Route::post('/checklist/store','ChecklistStore');
    // Route::get('/checklist/get-list-selects', 'getListSelects');
    Route::get('/checklist/form-selects', 'ChecklistGetFormSelects');

    // Encuestas
    Route::post('/encuestas/store','EncuestasStore');
    Route::get('/encuestas/form-selects', 'ActivitiesGetFormSelects');

    // Evaluaciones
    Route::post('/evaluaciones/store','EvaluacionesStore');
    Route::get('/evaluaciones/form-selects', 'EvaluacionesGetFormSelects');
    Route::get('/evaluaciones/topic/{topic}', 'getDataTopicByAssessmentsActivity');
	Route::get('/evaluaciones/temas/{topic}/preguntas/search', 'TemasSearchPreguntas');

});
