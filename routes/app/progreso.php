<?php

use App\Http\Controllers\ApiRest\RestUserProgressController;

Route::controller(RestUserProgressController::class)->group(function() {

    Route::get('/', 'userProgress');
    Route::get('/school-progress/{school}', 'getSchoolProgress');

});
