<?php

use App\Http\Controllers\RegistroCapacitacionTrainerController;

Route::controller(RegistroCapacitacionTrainerController::class)->group(function () {
    Route::post('/store', 'storeRequest');
});
