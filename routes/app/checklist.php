<?php

use App\Http\Controllers\ApiRest\RestChecklistController;

Route::controller(RestChecklistController::class)->group(function () {

    Route::post('/alumnos', 'alumnos');
    Route::get('/checklist_alumnos/{alumno}', 'getChecklistsByAlumno');
    Route::post('/marcar_actividad', 'marcarActividad');
    Route::get('/trainer/checklists', 'getChecklistsByTrainer');
    Route::get('/checklist/{checklist}/students', 'getStudentsByChecklist');
});
