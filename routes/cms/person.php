<?php

use App\Http\Controllers\PersonController;

Route::controller(PersonController::class)->group(function () {
    Route::post('/store', 'storeRequest')->name('person.store');
});
