<?php

use App\Http\Controllers\ApiRest\RestCourseController;

Route::controller(RestCourseController::class)->group(function() {

    Route::get('/', 'courses');

    Route::get('/cargar_encuestas_curso/{course}', 'loadPoll')->middleware('extend-session');

    Route::post('/guardar_encuesta_curso_id', 'savePollAnswers');

    Route::get('/diplomas', 'getCertificatesV2');

    Route::post('/aceptar-diploma/{course}', 'acceptCertification')->middleware('extend-session');

    Route::post('/registro-capacitacion', 'generateRegistroCapacitacion');
    Route::post('/audit-offline', 'auditOffline');
});
