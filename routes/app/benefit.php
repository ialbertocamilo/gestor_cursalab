<?php

use App\Http\Controllers\ApiRest\RestBenefitController;

Route::controller(RestBenefitController::class)->group(function () {

    Route::post('/', 'getBenefits');
    Route::get('/{benefit}/show', 'getInfo');
    Route::get('/speakers/{speaker}/show', 'getInfoSpeaker');
});
