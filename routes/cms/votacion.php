<?php

use App\Http\Controllers\CampaignVotationController;

Route::controller(CampaignVotationController::class)->group(function () {

    Route::get('/search', 'search')->name('votacion.votacion.search');
    Route::get('/search_status', 'search_status')->name('votacion.votacion.search_status');
});