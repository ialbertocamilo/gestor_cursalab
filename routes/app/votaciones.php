<?php

use App\Http\Controllers\ApiRest\RestCampaignController;

Route::controller(RestCampaignController::class)->group(function() {

    /* === CAMPAÑAS - CONTENIDOS ===*/
    Route::get('/campaigns/{user}', 'campaigns');
    Route::get('/campaign/contents/{campaign}', 'campaingsContents');
    Route::get('/campaign/v2/contents/{campaign}', 'campaingsContentsV2');
    Route::get('/campaign/v2/check-content/{campaign_id}/{media_id}', 'campaingsContentsV2');
    /* === CAMPAÑAS - CONTENIDOS === */

    /* usuario estados: insignias - requerimientos */
    Route::get('/campaign/requirements/{campaign}/{user}', 'campaignUserRequirements');
    Route::get('/campaign/badges/{campaign}/{user}', 'campaignUserBadges');
    /* usuario estados: insignias - requerimientos */

    Route::post('/campaign/contents/send_answer', 'contentSaveAnswer'); # responder encuesta

    /* validacion para layouts */
    Route::get('/campaign/check/user_answer/{campaign_id}/{user_id}', 'campaignUserCheckAnswer');
    Route::get('/campaign/check/user_postulate/{campaign_id}/{user_id}', 'campaignUserCheckPostulates');
    Route::get('/campaign/check/user_votation/{campaign_id}/{user_id}', 'campaignUserCheckVotations');
    /* validacion para layouts */

    /* === POSTULACION ===*/
    Route::get('/campaign/postulation/{campaign_id}/{user_id}', 'postulates');
    Route::post('/campaign/postulation/send_sustent', 'postulatesUserSustent');
    /* === POSTULACION ===*/
    
    /* === VOTACION ===*/
    Route::get('/campaign/votation/candidates/{campaign_id}/{user_id}/{criterio_id}', 'votationCandidates');
    Route::get('/campaign/votation/user_votes/{campaign_id}/{user_id}', 'votationUserVotes');
    Route::get('/campaign/votation/total_votes/{campaign_id}/{criterio_id}', 'votationRanking');
    Route::post('/campaign/votation/send_votes', 'votationUserSendVotes'); # enviar votos
    /* === VOTACION ===*/
});
