<?php

use App\Http\Controllers\ApiRest\RestVideotecaController;

Route::controller(RestVideotecaController::class)->group(function() {

    Route::get('/search', 'search');
    Route::get('/get-selects', 'getSelects');

    Route::get('/show/{videoteca}', 'show');
    Route::get('/related/{videoteca}', 'getRelated');

    Route::get('/store-visit/{videoteca}', 'storeVisit');
});
