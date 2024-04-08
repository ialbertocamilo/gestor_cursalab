<?php

use App\Http\Controllers\GeneralController;

Route::controller(GeneralController::class)->group(function() {

	Route::get('/modulos', 'getModulos');
	Route::get('/cards-info', 'getCardsInfo');
	Route::get('/evaluaciones-por-fecha', 'getEvaluacionesPorfecha');
	Route::get('/visitas-por-fecha', 'loadVisitsByDate');
	Route::get('/top-boticas', 'loadTopBoticas');


	Route::get('/workspaces-status', 'workspaces_status')->name('general.workspaces_status');
	Route::get('/workspace-current-status', 'workspace_current_status')->name('general.workspace_current_status');
	Route::get('/subworkspace-status/{subworkspace_id?}', 'subworkspace_status')->name('general.subworkspace_status');
	Route::put('/workspace-plan', 'workspace_plan')->name('general.workspace_plan');

	Route::put('/workspaces-storage', 'workspace_storage')->name('general.workspaces_storage');
	Route::get('/workspaces-users', 'workspace_users')->name('general.workspaces_users');
	// Route::put('/workspace-plan', 'workspace_plan')->name('general.workspace_plan');
	Route::get('/execute-command-jarvis', 'executeCommandJarvis');

	Route::get('/sync-courses-cursalab-university/{workspace_id}', 'syncCoursesCursalabUniversity');
});


