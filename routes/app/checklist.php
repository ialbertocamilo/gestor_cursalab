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
        Route::get('/trainer/checklists/{checklist}/list-entities', 'listEntities');
        Route::get('/trainer/checklists/{checklist}/activities', 'activitiesByChecklist');
        Route::get('/trainer/checklists/{checklist}/users', 'listUsers');
        Route::post('/trainer/checklists/{checklist}/save_activity', 'saveActivity');

        Route::post('/checklist/{checklist}/save_activities', 'saveActivities');

        Route::get('/checklist/{checklist}/list_progress', 'listProgress');
        Route::post('/activity/{activity}/verify_photo', 'verifyPhoto');


    });
});
