<?php

use App\Http\Controllers\ApiRest\RestQuizController;

Route::controller(RestQuizController::class)->group(function() {

    // Route::post('/rest/evaluar_preguntas', 'ApiRest\RestController@evaluar_preguntas');
    // Route::get('/rest/evaluarpreguntas_v2/{id_video}/{id_user}/{rpta_ok}/{rpta_fail}/{usu_rptas}', 'ApiRest\RestAvanceController@evaluarpreguntas_v2');

    // Route::post('/rest/evaluar_abiertas', 'ApiRest\RestAvanceController@guardaEvaluacionAbierta');
    // Route::get('/rest/eval_pendientes2/{user_id}/{cate_id}', 'ApiRest\RestController@evalPendientes2');

    // Route::get('/rest/usuario_respuestas_eval/{user_id}/{post_id}', 'ApiRest\RestController@usuarioRespuestasEval');

    

});
