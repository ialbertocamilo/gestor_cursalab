<?php

use App\Http\Controllers\ApiRest\RestRankController;

Route::controller(RestRankController::class)->group(function() {

    Route::get('/list', 'ranking');

    Route::get('/criterion-code/{type?}', 'rankingByCriterionCode');

});
