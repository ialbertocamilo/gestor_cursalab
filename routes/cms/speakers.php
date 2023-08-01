<?php

use App\Http\Controllers\SpeakerController;

Route::controller(SpeakerController::class)->group(function() {

    Route::view('/', 'speakers.index')->name('speaker.index');
    Route::view('/create', 'speakers.create_edit')->name('speaker.createSpeaker');
    Route::view('/edit/{speaker}', 'speakers.create_edit')->name('speaker.editSpeaker');

	Route::post('/store', 'store')->name('speaker.store');
	Route::put('/update/{speaker}', 'update')->name('speaker.update');

    Route::get('/search', 'search');
    Route::get('/search/{speaker}', 'getData')->name('speaker.getData');

    Route::put('/{speaker}/status', 'status');
    Route::delete('/{speaker}/destroy', 'destroy');

});
