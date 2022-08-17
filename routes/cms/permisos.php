<?php

use App\Http\Controllers\PermisoController;

Route::controller(PermisoController::class)->group(function () {
    // Permisos
    Route::post('store', 'store')->name('permisos.store');
    // ->middleware('permission:permisos.create');
    Route::get('/', 'index')->name('permisos.index');
    // ->middleware('permission:permisos.index');
    Route::get('create', 'create')->name('permisos.create');
    // ->middleware('permission:permisos.create');
    Route::put('{permiso}', 'update')->name('permisos.update');
    // ->middleware('permission:permisos.edit');
    Route::delete('{permiso}', 'destroy')->name('permisos.destroy');
    // ->middleware('permission:permisos.destroy');
    Route::get('{permiso}/edit', 'edit')->name('permisos.edit');
    // ->middleware('permission:permisos.edit');
});
