<?php

use \App\Http\Controllers\MasivoController;

Route::controller(MasivoController::class)->group(function() {
    Route::view('/', 'masivo.index');
    Route::get('/download-template-user','downloadTemplateUser');

    Route::post('/create-update-users','createUpdateUsers');
});