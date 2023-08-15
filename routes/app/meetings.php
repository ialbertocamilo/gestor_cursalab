<?php

use \App\Http\Controllers\ApiRest\RestMeetingController;

Route::controller(RestMeetingController::class)->group(function() {

	Route::get('/get-data', 'getData');
	Route::get('/list', 'listUserMeetings');

	// Route::post('/zoom/webhook-end-meeting', 'zoomWebhookEndMeeting');

	Route::get('/get-form-data', 'getFormData')->middleware('extend-session');
	Route::post('/store', 'store');

	Route::get('/search-attendants', 'searchAttendants');
	Route::post('/upload-attendants', 'uploadAttendants');

	Route::post('/{meeting}/start', 'startMeeting')->middleware('extend-session');
	Route::post('/{meeting}/join', 'joinMeeting')->middleware('extend-session');

	Route::post('/{meeting}/finish', 'finishMeeting');
	Route::put('/{meeting}/cancel', 'cancel');
	Route::delete('/{meeting}/destroy', 'destroy');


	Route::get('/{meeting}/duplicate-data', 'getDuplicatedData');
	Route::get('/{meeting}/edit', 'edit');
	Route::put('/{meeting}/update', 'update');

});
