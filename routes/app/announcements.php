<?php

use App\Http\Controllers\ApiRest\RestAnnouncementController;

Route::controller(RestAnnouncementController::class)->group(function() {

    Route::get('/', 'announcements');

});
