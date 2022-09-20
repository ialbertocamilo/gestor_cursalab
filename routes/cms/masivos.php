<?php

use \App\Http\Controllers\MasivoController;
use \App\Http\Controllers\UploadTopicGradesController;

Route::controller(MasivoController::class)->group(function () {
    Route::view('/', 'masivo.index');
    Route::get('/download-template-user', 'downloadTemplateUser');

    Route::post('/create-update-users', 'createUpdateUsers');


    Route::view('/importar-notas', 'masivo.upload-topic-grades');
    Route::get('/importar-notas/form-selects', 'getFormSelects');

});


Route::controller(UploadTopicGradesController::class)->group(function () {

    Route::prefix('importar-notas')->group(function () {
        Route::view('/', 'masivo.upload-topic-grades');
        Route::get('/form-selects', 'getFormSelects');
        Route::get('/form-selects', 'getFormSelects');

        Route::post('/upload', 'upload');
    });

});
