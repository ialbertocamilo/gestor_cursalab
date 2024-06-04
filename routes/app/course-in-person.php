<?php

use App\Http\Controllers\ApiRest\RestCourseInPersonController;

Route::controller(RestCourseInPersonController::class)->group(function() {
    Route::get('/list', 'listCoursesByUser'); //USER AND HOST
    Route::get('/course/{course}/topic/{topic}', 'listResources'); // USER AND HOST
    Route::get('/topic/{topic}/menu', 'getListMenu'); // USER AND HOST
    Route::get('/topic/{topic}/validate-resource', 'validateResource'); // USER
    Route::post('/topic/{topic}/upload-signature', 'uploadSignature'); // USER
    Route::get('/get-data', 'getData');// USER
    Route::get('/topic/{topic}/load-poll-questions', 'loadPoll');
    Route::get('/topic/{topic}', 'loadTopicInfo');

    Route::get('/course/{course}/topic/{topic}/assigned', 'listUsersBySession');  // HOST
    Route::post('/topic/evaluation', 'changeStatusEvaluation'); // HOST
    Route::post('/topic/{topic}/take-assistance', 'takeAssistance'); // HOST
    Route::get('/topic/{topic}/start-poll', 'startPoll'); // HOST
    Route::get('/topic/{topic}/verify-evaluation-time', 'verifyEvaluationTime'); // HOST
    Route::get('/topic/{topic}/users-init-data', 'usersInitData'); // HOST
});
