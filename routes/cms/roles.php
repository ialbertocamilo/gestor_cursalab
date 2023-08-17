<?php

use App\Http\Controllers\RoleController;

Route::controller(RoleController::class)->group(function () {

    Route::get('/search', 'search')->name('roles.search');
    // Route::get('/get-list-selects', 'getListSelects');
    Route::get('/form-selects', 'formData');
    Route::get('/', 'index')->name('roles.index');

    Route::get('/create', 'create')->name('roles.create');
    Route::post('/store', 'store')->name('roles.store');

    Route::get('/{role}/edit', 'edit')->name('roles.edit');
    Route::put('/{role}/update', 'update')->name('roles.update');

    Route::put('/{role}/status', 'status')->name('roles.status');
    Route::get('/{role}/show', 'show')->name('roles.show');
    Route::delete('/{role}/destroy', 'destroy')->name('roles.destroy');
    // Route::post('roles/store', 'store')->name('roles.store');
    // // ->middleware('permission:roles.create');
    // // Listar
    // Route::get('roles/index', 'index')->name('roles.index');
    // // ->middleware('permission:roles.index');
    // // Formulario de creacion
    // Route::get('roles/create', 'create')->name('roles.create');
    // // ->middleware('permission:roles.create');
    // // Actualizar
    // Route::put('roles/{role}', 'update')->name('roles.update');
    // // ->middleware('permission:roles.edit');
    // // Ver el detalle
    // Route::get('roles/{role}', 'show')->name('roles.show');
    // // ->middleware('permission:roles.show');
    // // Eliminar
    // Route::delete('roles/{role}', 'destroy')->name('roles.destroy');
    // // ->middleware('permission:roles.destroy');
    // // Formulario Edicion
    // Route::get('roles/{role}/edit', 'edit')->name('roles.edit');
    // // ->middleware('permission:roles.edit');
});
