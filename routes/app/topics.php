<?php

use App\Http\Controllers\ApiRest\RestTopicController;

Route::controller(RestTopicController::class)->group(function() {

    Route::get('/{course}', 'topics');
    Route::prefix('v2')->group(function () {
        Route::get('/{course}', 'topicsv2');
    });

    Route::get('/topics/update-plays/{topic}', 'updateTopicPlays');
    Route::get('/topics/update-resets-count/{topic}', 'updateActivity');


    Route::get('/actividad_tema_revisado/{topic}', 'reviewTopic');
    Route::post('/actividad_contenido_revisado/{topic}/{media}', 'reviewTopicMedia');
    Route::post('/actividad_contenido_duracion/{topic}/{media}', 'reviewTopicMediaDuration');


});
