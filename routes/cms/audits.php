<?php

use App\Http\Controllers\AuditController;

Route::controller(AuditController::class)->group(function() {

	Route::get('/search', 'search');

	// Route::get('/{user}/last-activity', 'lastActivity');

	Route::get('/last-activity', 'lastActivity');
	Route::get('/get-form-data', 'getFormData');
	Route::get('/{audit}/show', 'show');
});
