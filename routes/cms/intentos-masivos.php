<?php

use \App\Http\Controllers\UsuarioController;

Route::controller(UsuarioController::class)->group(function () {
    Route::get('/', 'index_reinicios')->name('usuarios.index_reinicios');
    Route::get('reinicios_data', 'reinicios_data');
    Route::get('buscarEscuelasxModulo/{subworkspace_id}', 'buscarEscuelasxModulo');
    Route::get('buscarCursosxEscuela/{school_id}', 'buscarCursosxEscuela');
    Route::get('buscarTemasxCurso/{course_id}', 'buscarTemasxCurso');
    Route::post('validarReinicio', 'validarReinicioIntentos');
    Route::post('reiniciarIntentosMasivos', 'reiniciarIntentosMasivos');
});
?>