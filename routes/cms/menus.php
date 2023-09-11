<?php

use \App\Http\Controllers\MenuController;

Route::controller(MenuController::class)->group(function () {
    Route::get('/list', 'list');
    Route::post('/update', 'updateItems');

});
