<?php

use App\Http\Controllers\WorkspaceController;

Route::controller(WorkspaceController::class)->group(function () {

    Route::get('/list', 'list')->name('workspaces-list');
    Route::get('/create', 'create');
    Route::post('/store', 'store');
    Route::get('/criterios', 'list_criterios')->name('criteriawk.list');
    Route::get('/criterios/{criterion}/valores', 'list_criterios_values')->name('criterion_values_wk.list');
    // Route::get('/search', 'search');
    Route::get('/{workspace}/edit', 'edit');
    Route::put('/{workspace}/update', 'update');
    Route::get('/{workspace}/copy', 'copy');
    Route::put('/{workspace}/status', 'status');
    Route::post('/{workspace}/duplicate', 'duplicate')->middleware('checkrol:super-user');
    Route::delete('/{workspace}/destroy', 'destroy')->middleware('checkrol:super-user');
});
