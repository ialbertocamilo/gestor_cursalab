<?php

use App\Http\Controllers\ApiRest\RestUserController;

Route::controller(RestUserController::class)->group(function() {

    Route::post('/password/reset', 'resetPassword');

    Route::get('/notifications/', 'loadNotifications');

    Route::put('/notification/{userNotification}', 'updateNoficationStatus');

    // Route::get('/cargar_encuestas_curso/{course}', 'loadPoll');

    // Route::post('/guardar_encuesta_curso_id', 'savePollAnswers');

    // Route::get('/diplomas', 'getCertificates');

    // Route::post('/aceptar-diploma/{course}', 'acceptCertification');


});
