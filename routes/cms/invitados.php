<?php

use App\Http\Controllers\GuestController;
use App\Http\Controllers\GuestLinkController;

Route::controller(GuestController::class)->group(function() {
	Route::view('/', 'guest.list')->name('guest.list');

	Route::get('search', 'search');
	Route::post('send-invitation', 'sendInvitation');
	Route::get('limits-workspace', 'limitsWorspace');
	Route::post('delete', 'deleteGuest');
	Route::get('/user/{guest}/edit', 'editGuestUser');
	Route::put('/user/{user}/update', 'updateGuestUser');
	Route::put('/user/{guest}/status', 'statusGuestUser');

	Route::post('send_requirement', 'send_requirement');
	Route::post('users_activation', 'activateMultipleUsers');

});
Route::controller(GuestLinkController::class)->group(function() {
	Route::get('list-guest-url', 'listGuestUrl');
	Route::get('init-data', 'initData');
	Route::post('add-url', 'addUrl');
	Route::delete('register-url/{url_id}/destroy', 'destroy');
});