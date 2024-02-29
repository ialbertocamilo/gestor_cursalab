<?php

use App\Http\Controllers\DiplomaController;

Route::controller(DiplomaController::class)->group(function () {
    Route::view('/create', 'stages.diplomas.create_edit')->name('stage.diploma.create');
    Route::view('/edit/{diploma}', 'stages.diplomas.create_edit')->name('stage.diploma.edit');
});

?>
