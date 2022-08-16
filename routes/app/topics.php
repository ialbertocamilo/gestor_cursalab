<?php

use App\Http\Controllers\ApiRest\RestTopicController;

Route::controller(RestTopicController::class)->group(function() {

    Route::get('/topics/{topic}', 'topic');

    Route::get('/topics/update-plays/{topic}', 'updateTopicPlays');
    Route::get('/topics/update-resets-count/{topic}', 'updateActivity');






});
