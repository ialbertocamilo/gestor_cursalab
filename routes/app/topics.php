<?php

use App\Http\Controllers\ApiRest\RestTopicController;

Route::controller(RestTopicController::class)->group(function() {

    Route::get('/{course}', 'topics');

    Route::get('/topics/update-plays/{topic}', 'updateTopicPlays');
    Route::get('/topics/update-resets-count/{topic}', 'updateActivity');


    Route::get('/actividad_tema_revisado/{topic}', 'reviewTopic');


});
