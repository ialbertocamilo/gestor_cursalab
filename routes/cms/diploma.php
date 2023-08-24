<?php

use App\Http\Controllers\DiplomaController;

Route::controller(DiplomaController::class)->group(function () {
    Route::view('/create', 'diploma.create_edit')->name('diploma.create');
    Route::view('/edit/{diploma}', 'diploma.create_edit')->name('diploma.edit');
});

?>