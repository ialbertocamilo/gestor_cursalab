<?php

use App\Http\Controllers\ApiRest\RestCourseInPersonController;

Route::controller(RestCourseInPersonController::class)->group(function() {
    Route::get('/list', 'listCoursesByUser');
    Route::get('/{course}/assigned', 'listGuestsByCourse');
    Route::get('/course/{course}/topic/{topic}', 'listResources');

    Route::post('/topic/evaluation', 'changeStatusEvaluation');
});
