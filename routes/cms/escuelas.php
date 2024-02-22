<?php

use App\Http\Controllers\EscuelaController;

Route::controller(EscuelaController::class)->group(function () {

    Route::view('/', 'escuelas.list')->name('escuelas.list');
    // ->middleware('permission:abconfigs.list');
    Route::get('/search', 'search')->name('modulos.escuelas.search');


    Route::view('/create', 'escuelas.create_edit')->name('escuelas.create');
    // ->middleware('permission:escuelas.create');
    Route::view('/edit/{school}', 'escuelas.create_edit')->name('escuelas.edit');
    // ->middleware('permission:escuelas.edit');

    Route::post('/store', 'store')->name('escuelas.store');
    Route::put('/update/{school}', 'update')->name('escuelas.update');


    Route::get('/search/{school}', 'searchCategoria')->name('escuelas.searchEscuela');
    Route::get('/copy/{school}', 'copy')->name('escuelas.copy');
    Route::get('/form-selects', 'getFormSelects')->name('escuelas.formSelects');

    Route::delete('/{school}/destroy', 'destroyEscuela')->name('escuelas.destroyEscuela');
    Route::post('/{school}/copy-content', 'copyContent')->name('escuelas.copyContent');

    Route::put('/{categoria}/status', 'updateStatus');
});

// CURSOS
Route::prefix('{school}/cursos')->middleware('check-school-workspace')->group(base_path('routes/cms/cursos.php'));
