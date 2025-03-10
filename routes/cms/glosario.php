<?php

use App\Http\Controllers\GlossaryController;

Route::controller(GlossaryController::class)->group(function() {

    Route::view('/', 'glosarios.list')->name('glosario.list');
    // ->middleware('permission:glosario.list');

    Route::get('/search', 'search');
    Route::get('/get-list-selects', 'getListSelects');
    Route::get('/form-selects', 'getFormSelects');

    Route::get('/import', 'import');
    // ->middleware('permission:glosarios.create');
    Route::post('/import', 'importFile');
    // ->middleware('permission:glosarios.create');

    Route::get('/carreras-categorias', 'carreerCategories');
    // ->middleware('permission:glosarios.create');
    Route::put('/carreras-categorias', 'carreerCategoriesStore');
    // ->middleware('permission:glosarios.create');

    Route::get('/create', 'create');
    Route::post('/store', 'store');
    Route::get('/{glossary}/edit', 'edit');
    Route::put('/{glossary}/update', 'update');
    Route::put('/{glossary}/status', 'status');
    Route::delete('/{glossary}/destroy', 'destroy');
});
