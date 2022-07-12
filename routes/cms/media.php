<?php

use \App\Http\Controllers\MediaController;
use \App\Http\Controllers\HomeController;

Route::controller(MediaController::class)->group(function() {

    // MEDIA
    Route::post('media/fileupload', 'fileupload')->name('media.fileupload');
    Route::get('media/eliminar/{id}', 'delete')->name('media.eliminar');
    Route::get('media/index', 'index')->name('media.index');
    Route::get('media/create', 'create')->name('media.create');
    Route::get('media/modal_list_media_asigna', 'modal_list_media_asigna')->name('media.modal_list_media_asigna');


    Route::get('media/search', 'search');
    Route::get('media/{media}/download', 'downloadExternalFile')->name('media.download');

});

Route::post('/upload-image/{type}', [HomeController::class, 'uploadImage'])->name('upload.image');
