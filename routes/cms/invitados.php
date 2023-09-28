<?php

use App\Http\Controllers\GuestController;
use App\Http\Controllers\GuestLinkController;

Route::controller(GuestController::class)->group(function() {
	Route::view('/', 'guest.list')->name('guest.list');

	Route::get('search', 'search');
	Route::get('list-guest-url', 'listGuestUrl');
	Route::post('send_invitation', 'send_invitation');
	Route::get('limitation_admin', 'limitation_admin');
	Route::post('send_requirement', 'send_requirement');
	Route::post('users_activation', 'activateMultipleUsers');

});
Route::controller(GuestLinkController::class)->group(function() {
	Route::post('add-url', 'addUrl');
	Route::delete('/delete-url/{url_id}', 'destroy');
});