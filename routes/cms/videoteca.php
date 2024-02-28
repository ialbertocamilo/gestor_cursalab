<?php

use App\Http\Controllers\VideotecaController;

Route::controller(VideotecaController::class)->group(function() {


    // Route::prefix('/videoteca')->group(function () {
    Route::get('/fake-data', 'fakeData');
    Route::view('/list', 'videoteca.index')->name('videoteca.list');
    // ->middleware('permission:videoteca.index');
    Route::get('/search', 'search')->name('videoteca.search');
    Route::get('/create', 'create')->name('videoteca.create');
    Route::post('/store', 'store')->name('videoteca.store');
    Route::get('/{videoteca}/show', 'show')->name('videoteca.show');
    Route::get('/{videoteca}/edit', 'edit')->name('videoteca.edit');
    Route::put('/{videoteca}/update', 'update')->name('videoteca.update');
    Route::put('/{videoteca}/status', 'status')->name('videoteca.status');
    Route::delete('/{videoteca}/destroy', 'delete')->name('videoteca.delete');

    Route::prefix('/tags')->group(function () {
        Route::get('/list', 'tagsList');
        Route::put('/{tag}/update', 'tagEdit');
        Route::delete('/{tag}/delete', 'tagDelete');
    });

    Route::prefix('/categorias')->group(function () {
        Route::get('/list', 'categoriasList');
        Route::post('/', 'categoriasStore');
        Route::put('/{categoria}/update', 'categoriasEdit');
    });

});
