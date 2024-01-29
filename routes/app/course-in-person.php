<?php

use App\Http\Controllers\ApiRest\RestCourseInPersonController;

Route::controller(RestCourseInPersonController::class)->group(function() {
    Route::get('/list', 'listCoursesByUser'); //USER AND HOST
    Route::get('/course/{course}/topic/{topic}', 'listResources'); // USER AND HOST
    Route::get('/topic/{topic}/menu', 'getListMenu'); // USER AND HOST
    
    Route::get('/course/{course}/topic/{topic}/assigned', 'listGuestsByCourse');  // HOST
    Route::post('/topic/evaluation', 'changeStatusEvaluation'); // HOST
});
