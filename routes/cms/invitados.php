<?php

use App\Http\Controllers\GuestController;

Route::controller(GuestController::class)->group(function() {
	Route::view('/', 'guest.list')->name('guest.list');
});