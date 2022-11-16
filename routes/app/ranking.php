<?php

use App\Http\Controllers\ApiRest\RestRankController;

Route::controller(RestRankController::class)->group(function() {

    Route::get('/', 'ranking');

    Route::get('/v2', 'ranking_v2');

    Route::get('/criterion-code/{type}', 'rankingByCriterionCode');

});
