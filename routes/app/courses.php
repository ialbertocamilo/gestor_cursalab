<?php

use App\Http\Controllers\ApiRest\RestCourseController;

Route::controller(RestCourseController::class)->group(function() {

    Route::get('/', 'courses');

});
