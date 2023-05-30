<?php

use App\Http\Controllers\CampaignController;

// use App\Http\Controllers\{ AnnouncementsController, SummonedsController, PostulatesController, VotationsController };

Route::controller(CampaignController::class)->group(function () {
	Route::view('/', 'votaciones.list')->name('votaciones.list');
	Route::get('/search', 'search')->name('votacion.search');

/*	// ==== CAMPAÑAS ====
	Route::get('/index', [AnnouncementsController::class, 'index']);
	Route::get('/indexid/{id}', [AnnouncementsController::class, 'indexId']);
	Route::get('/duplicate/{id}', [AnnouncementsController::class, 'duplicate']);
	Route::put('/state', [AnnouncementsController::class, 'state']);
	Route::put('/statestage', [AnnouncementsController::class, 'stateStage']);

	Route::post('/create', [AnnouncementsController::class, 'create']);
	Route::post('/update', [AnnouncementsController::class, 'update']);

	Route::get('/announ/data', [AnnouncementsController::class, 'data']); // criterio y/o modulos
	Route::get('/announ/verify', [AnnouncementsController::class, 'verify']);

	Route::delete('/delete/{id}', [AnnouncementsController::class, 'delete']);
	Route::get('/report/{id}/{module}', [AnnouncementsController::class, 'report']);

	Route::get('/test/{id}', [AnnouncementsController::class, 'test']);
*/

});

// ==== CONVOCADOS ====
/*Route::post('/convocados/create', [SummonedsController::class, 'create']);
Route::post('/convocados/createcheck', [SummonedsController::class, 'checkPostulate']);

Route::get('/convocados/index/{id}', [SummonedsController::class, 'index']);
Route::get('/convocados/requirement/{id}', [SummonedsController::class, 'requirement']);
Route::put('/convocados/state', [SummonedsController::class, 'state']);

// ==== POSTULADOS ====
Route::get('/postulados/sustents/index/{id}', [PostulatesController::class, 'index']);
Route::put('/postulados/sustents/state', [PostulatesController::class, 'state']);

Route::get('/postulados/sustents/checks/{id}', [PostulatesController::class, 'checks']);

Route::put('/postulados/notification',[PostulatesController::class, 'notification']);
Route::put('/postulados/sustents/updatesub',[PostulatesController::class, 'updatesub']);
Route::put('/postulados/sustents/update', [PostulatesController::class, 'update']);
Route::put('/postulados/sustents/reset', [PostulatesController::class, 'reset']);

// ==== VOTACIONES ====
Route::get('/resultados/votos/index/{id}', [VotationsController::class, 'index']);
Route::get('/resultados/votos/repeats/{id}', [VotationsController::class, 'repeats']);
Route::put('/resultados/votos/confirm', [VotationsController::class, 'confirm']);

Route::get('/resultados/votos/porcent/{id}/{id_requirement}', [VotationsController::class, 'indexValidate']);
*/