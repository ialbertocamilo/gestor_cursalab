<?php

use App\Http\Controllers\WorkspaceController;

Route::controller(WorkspaceController::class)->group(function () {

    Route::get('/list', 'list');
    Route::get('/create', 'create');
    Route::post('/store', 'store');
    Route::get('/criterios', 'list_criterios')->name('criteriawk.list');
    Route::get('/criterios/{criterion}/valores', 'list_criterios_values')->name('criterion_values_wk.list');
    // Route::get('/search', 'search');
    Route::get('/{workspace}/edit', 'edit');
    Route::put('/{workspace}/update', 'update');
});
