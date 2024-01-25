<?php

use App\Http\Controllers\ApiRest\RestCourseInPersonController;

Route::controller(RestCourseInPersonController::class)->group(function() {
    Route::get('/list', 'listCoursesByUser');
    Route::get('/{course}/assigned', 'listGuestsByCourse');
});
