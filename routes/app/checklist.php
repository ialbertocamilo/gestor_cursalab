<?php

use App\Http\Controllers\ApiRest\RestChecklistController;

Route::controller(RestChecklistController::class)->group(function () {

    Route::post('/alumnos', 'alumnos');
    Route::get('/checklist_alumnos/{alumno}', 'getChecklistsByAlumno');
    Route::post('/marcar_actividad', 'marcarActividad')->middleware('extend-session');
    Route::post('/trainer/checklists', 'getChecklistsByTrainer');
    Route::post('/checklist/students', 'getStudentsByChecklist');
    Route::get('/checklist/{checklist_id}', 'getChecklistInfo');

    Route::prefix('v2')->group(function () {
        Route::get('/trainer/init-data', 'getInitData');
        Route::get('/trainer/checklists', 'checklistsTrainer');
        Route::get('/trainer/checklists/{checklist}/activities', 'activitiesByChecklist');
        Route::post('/checklist/{checklist}/save_activities', 'saveActivities');
        Route::post('/activity/{activity}/verify_photo', 'verifyPhoto');
    });
});
