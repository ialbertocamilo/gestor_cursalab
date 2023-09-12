
<?php

use App\Http\Controllers\CampaignController;

Route::controller(CampaignController::class)->group(function() {
    Route::view('/create', 'votaciones.create_edit')->name('votacion.create');
    Route::view('/edit/{campaign}', 'votaciones.create_edit')->name('votacion.edit');
    Route::view('/detail/{campaign}', 'votaciones.view_detail')->name('votacion.detail');
});

?>