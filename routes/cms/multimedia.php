<?php

use App\Http\Controllers\MediaController;

Route::controller(MediaController::class)->group(function() {

    Route::view('/', 'media.list')->name('multimedia.index');

    Route::get('/search', 'search');
    Route::get('/get-list-selects', 'getListSelects');
    Route::get('/show/{multimedia}', 'show');
    Route::get('/eliminar/{media}', 'delete')->name('media.eliminar');

    Route::post('/upload', 'upload');
    Route::get('/{media}/download', 'downloadExternalFile');
    Route::get('/topic/{media_topic_id}/download', 'downloadMediaTopicExternalFile')->name('media.download.media_topic');
});
