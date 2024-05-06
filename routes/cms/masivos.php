<?php

use \App\Http\Controllers\MasivoController;
use \App\Http\Controllers\UploadTopicGradesController;

Route::controller(MasivoController::class)->group(function () {
    Route::view('/', 'masivo.index');
    Route::get('/download-template-user', 'downloadTemplateUser');

    Route::post('/create-update-users', 'createUpdateUsers');
    Route::post('/update-users', 'updateUsers');
    Route::post('/active-users', 'activeUsers');
    Route::post('/inactive-users', 'inactiveUsers');

});
