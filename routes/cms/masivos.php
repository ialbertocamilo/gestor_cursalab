<?php

use \App\Http\Controllers\MasivoController;
use \App\Http\Controllers\UploadTopicGradesController;

Route::controller(MasivoController::class)->group(function () {
    Route::view('/', 'masivo.index');
    Route::get('/download-template-user', 'downloadTemplateUser');

    Route::post('/create-update-users', 'createUpdateUsers');
    Route::post('/active-users', 'activeUsers');
    Route::post('/inactive-users', 'inactiveUsers');

    Route::view('/importar-notas', 'masivo.upload-topic-grades');
    Route::get('/importar-notas/form-selects', 'getFormSelects');

});
