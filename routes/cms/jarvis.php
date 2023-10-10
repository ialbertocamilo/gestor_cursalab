<?php

use \App\Http\Controllers\JarvisController;

Route::controller(JarvisController::class)->group(function () {
    Route::post('/generate-description-jarvis', 'generateDescriptionJarvis');
    Route::post('/generate-questions', 'generateQuestionsJarvis');
});
