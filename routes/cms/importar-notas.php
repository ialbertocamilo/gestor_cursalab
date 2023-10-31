<?php

use \App\Http\Controllers\UploadTopicGradesController;

Route::controller(UploadTopicGradesController::class)->group(function () {

        Route::view('/', 'masivo.upload-topic-grades');
        Route::get('/form-selects', 'getFormSelects');
        // Route::get('/form-selects', 'getFormSelects');

        Route::post('/upload', 'upload');


});
