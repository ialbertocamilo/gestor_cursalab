<?php

use App\Http\Controllers\ApiRest\RestuserController;

Route::controller(RestuserController::class)->group(function() {

    Route::get('/', 'courses');

    // Route::get('/cargar_encuestas_curso/{course}', 'loadPoll');

    // Route::post('/guardar_encuesta_curso_id', 'savePollAnswers');

    // Route::get('/diplomas', 'getCertificates');

    // Route::post('/aceptar-diploma/{course}', 'acceptCertification');


});
