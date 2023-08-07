<?php

use App\Http\Controllers\CampaignController;

// use App\Http\Controllers\{ AnnouncementsController, SummonedsController, PostulatesController, VotationsController };

Route::controller(CampaignController::class)->group(function () {
	Route::view('/', 'votaciones.list')->name('votaciones.list');
	Route::get('/search', 'search')->name('votacion.search');

	Route::get('/form-selects', 'getFormSelects')->name('votacion.form-selects');

    Route::get('/modules/get-data', 'getFiltersSelects')->name('votacion.filters_selects_modules');

    /* criterios*/
    Route::get('/criterion/get-data', 'getFilterCriterion')->name('votacion.filters_selects_criterion');
    Route::get('/criterion/values/{criterion}', 'getFilterCriterionValues')->name('votacion.filters_selects_criterion_values');
    /* criterios*/

    /* verify -  */
    Route::get('/verify', 'getVerifyRequirements')->name('votacion.verify');
    /* verify */

    /* store - edit - update - duplicate */
    Route::post('/store', 'store')->name('votacion.store');
    Route::post('/update/{campaign}', 'update')->name('votacion.update');
    Route::put('/{campaign}/status', 'status')->name('votacion.status');
    Route::delete('/{campaign}/destroy', 'destroy')->name('votacion.destroy');
    Route::put('/{campaign}/duplicate', 'duplicate')->name('votacion.duplicate');
    Route::get('/check_duplicate/{campaign}', 'check_duplicate')->name('votacion.check_duplicate');
    Route::get('/edit/{campaign}', 'edit_campaign')->name('votacion.edit_campaign');
    /* store - edit - update - duplicate*/

	/* update stages*/
    Route::put('/status/stages/{campaign}', 'update_stages')->name('votacion.update_stages');
	/* update stages*/

	/* campaign_id*/
    Route::get('/get_campaign/{campaign}', 'get_campaign')->name('votacion.get_campaign');
	/* campaign_id*/

    /* reportes */
    Route::get('/report/{campaign}/candidates', 'report_candidates')->name('votacion.report_candidates');
    Route::get('/report/{campaign}/postulates', 'report_postulates')->name('votacion.report_postulates');
    /* reportes */
});

Route::prefix('{campaign}/postulacion')->group(base_path('routes/cms/postulacion.php'));
Route::prefix('{campaign}/votacion')->group(base_path('routes/cms/votacion.php'));