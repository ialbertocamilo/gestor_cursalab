<?php

use App\Http\Controllers\RestProjectController;

Route::controller(RestProjectController::class)->group(function() {
    Route::get('/{project}/search', 'searchProjectUser');
    Route::get('/user/{type}', 'userProjects');
    Route::get('/summary', 'userSummary');
    Route::post('/{project}/store-update', 'storeUpdateUserProject');
});
