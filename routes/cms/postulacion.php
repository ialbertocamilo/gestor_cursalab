<?php

use App\Http\Controllers\CampaignPostulationController;

Route::controller(CampaignPostulationController::class)->group(function () {

    Route::get('/search', 'search')->name('votacion.postulacion.search');
    Route::get('/requirements', 'requirements')->name('votacion.postulacion.requirements');
    Route::put('/status', 'status')->name('votacion.postulacion.status');

    /* search - checks - email*/
    Route::get('/postulate/{summoned_id}/sustents', 'search_sustents')->name('votacion.postulacion.search_sustents');
    Route::get('/postulate/{summoned_id}/sustents/checks', 'count_checks')->name('votacion.postulacion.count_checks');
    Route::put('/postulate/{summoned_id}/send_email', 'send_email')->name('votacion.postulacion.send_email');
    /* search - checks - email */
    
    /* reset - update */
    Route::put('/postulate/{summoned_id}/sustents/reset', 'reset_sustents')->name('votacion.postulacion.reset_sustents');
    Route::put('/postulate/{summoned_id}/sustents/update', 'update_sustents')->name('votacion.postulacion.update_sustents');
    Route::put('/postulate/{summoned_id}/sustents/update_sub', 'update_sub_sustents')->name('votacion.postulacion.update_sub_sustents');
    /* reset - update */
});