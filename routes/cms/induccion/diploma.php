<?php

use App\Http\Controllers\DiplomaController;

Route::controller(DiplomaController::class)->group(function () {
    Route::view('/create', 'processes.diplomas.create_edit')->name('process.diploma.create');
    Route::view('/edit/{diploma}', 'processes.diplomas.create_edit')->name('process.diploma.edit');
});

?>
