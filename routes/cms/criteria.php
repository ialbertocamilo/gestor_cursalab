<?php

//use App\Http\Controllers\TipoCriterioController;
use App\Http\Controllers\CriterionController;

//Route::controller(TipoCriterioController::class)->group(function() {
Route::controller(CriterionController::class)->group(function () {

    Route::view('/', 'criteria.list')->name('criteria.list');
    // ->middleware('permission:tipo_criterio.list');

    Route::get('/search', 'search');
    Route::get('/search-wk', 'searchWk');
    // Route::get('/get-list-selects', 'getListSelects');
    Route::get('/form-selects', 'getFormSelects');

    Route::post('/store', 'store');
    Route::get('/{criterion}/edit', 'edit');
    Route::put('/{criterion}/update', 'update');

    Route::get('/workspace', 'getWorkspaceCriteria');
});

// Route::get('{anuncio}/usuarios', 'usuarios')->name('modulos.usuarios');

// CRITERIOS
Route::prefix('{criterion}/valores')->group(base_path('routes/cms/criterion_values.php'));
