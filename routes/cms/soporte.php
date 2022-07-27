<?php

use App\Http\Controllers\UsuarioAyudaController;

Route::controller(UsuarioAyudaController::class)->group(function() {

    Route::view('/', 'usuario_ayuda.list')->name('soporte.list');
    // ->middleware('permission:usuarios_ayuda.show');

    Route::get('/search', 'search');
    Route::get('/get-list-selects', 'getListSelects');

    Route::prefix('formulario-ayuda')->group(
        base_path('routes/cms/soporte-ayuda.php')
    );
    // Route::get('/form-selects', 'getFormSelects');

    // Route::get('/create', 'create');
    // Route::post('/store', 'store');


    Route::get('/{ticket}/show', 'show');
    Route::get('/{ticket}/edit', 'edit');
    Route::put('/{ticket}/update', 'update');
    Route::delete('/{ticket}/destroy', 'destroy');

});
