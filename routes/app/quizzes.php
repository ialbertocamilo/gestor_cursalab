<?php

use App\Http\Controllers\ApiRest\RestQuizController;

Route::controller(RestQuizController::class)->group(function() {

    // Route::get('/rest/evaluarpreguntas_v2/{id_video}/{id_user}/{rpta_ok}/{rpta_fail}/{usu_rptas}', 'ApiRest\RestAvanceController@evaluarpreguntas_v2');
    // Route::get('evaluarpreguntas_v2/{topic_id}/{user_id}/{rpta_ok}/{rpta_fail}/{usu_rptas}', 'evaluarpreguntas_v2');

    Route::post('evaluar_preguntas', 'evaluar_preguntas');
    Route::post('evaluar_abiertas', 'guardaEvaluacionAbierta');

    Route::get('cargar_preguntas/{topic_id}', 'cargar_preguntas');

    Route::post('upd_reproducciones/{topic}', 'guarda_visitas_post');
    Route::post('contador_tema_reseteo/{topic}', 'contador_tema_reseteo');
    
    // Route::get('eval_pendientes2/{user_id}/{cate_id}', 'evalPendientes2');

    // Route::get('usuario_respuestas_eval/{user_id}/{post_id}', 'usuarioRespuestasEval');
    // 
    Route::get('preguntas_rptas_usuario/{topic}', 'preguntasRptasUsuario');

});
