<?php

use App\Http\Controllers\VademecumController;

Route::controller(VademecumController::class)->group(function() {

    /*
     * Vademecum routes
     ======================================== */

    Route::view('/', 'vademecum.list')->name('vademecum.list');
    // ->middleware('permission:vademecum.index');

    Route::get('/search', 'search');
    // ->middleware('permission:vademecum.index');
    Route::get('/form-selects', 'getFormSelects');
    // ->middleware('permission:vademecum.index');
    Route::get('/get-list-selects', 'getListSelects');
    // ->middleware('permission:vademecum.index');
    Route::get('/get-subcategories', 'getSubCategoriesByCategory');
    // ->middleware('permission:vademecum.index');

    Route::get('/create', 'create');
    // ->middleware('permission:vademecum.create');
    Route::post('/store', 'store');
    // ->middleware('permission:vademecum.create');
    Route::get('/{vademecum}/edit', 'edit');
    // ->middleware('permission:vademecum.edit');
    Route::put('/{vademecum}/update', 'update');
    // ->middleware('permission:vademecum.edit');
    Route::put('/{vademecum}/status', 'status');
    // ->middleware('permission:vademecum.edit');
    Route::delete('/{vademecum}/destroy', 'destroy');
    // ->middleware('permission:vademecum.destroy');


    /*
     * Categories routes
     ======================================== */

    Route::prefix('categorias')->group(function () {

        Route::view('/', 'vademecum.categorias.list')->name('vademecum.categorias.list');
        // ->middleware('permission:vademecum.categorias');
        Route::get('/search', 'categorias_search');
        // Route::get('/', 'categorias');
        // ->middleware('permission:vademecum.categorias');
        Route::get('/create', 'categorias_create');
        Route::post('/store', 'categorias_store');
        Route::get('/{categoria}/edit', 'categorias_edit');
        Route::put('/{categoria}/update', 'categorias_update');
        Route::delete('/{categoria}/destroy', 'categorias_destroy');

        /*
         * Subcategories routes
         ======================================== */

        Route::prefix('{categoria}/subcategorias')->group(function () {

            Route::view('/', 'vademecum.categorias.subcategorias.list')->name('vademecum.categorias.subcategorias.list');
            Route::get('/search', 'subcategorias_search');
            // Route::get('/', 'subcategorias');
            // ->middleware('permission:vademecum.subcategorias');
            Route::get('/create', 'subcategorias_create');
            Route::post('/store', 'subcategorias_store');
            Route::get('/{subcategoria}/edit', 'subcategorias_edit');
            Route::put('/{subcategoria}/update', 'subcategorias_update');
            Route::delete('{subcategoria}/destroy', 'subcategorias_destroy');
        });
    });

});


// Route::get('vademecum/subcategorias_x_categoria/{categoria}', 'VademecumController@getSubCategoriaByCategoriaId');
