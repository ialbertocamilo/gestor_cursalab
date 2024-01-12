<?php

use App\Http\Controllers\Induction\ApiRest\RestActivityController;

Route::controller(RestActivityController::class)->group(function () {

    Route::get('/meeting/{meeting}', 'ActivityMeeting');
    Route::get('/topic/{topic}', 'ActivityTopic');
    Route::get('/poll/{poll}', 'ActivityPoll');
    Route::get('/checklist/{checklist}', 'ActivityChecklist');
    Route::get('/questions/{topic}', 'ActivityAssessment');

});
