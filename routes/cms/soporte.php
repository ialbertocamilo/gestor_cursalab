<?php

use App\Http\Controllers\UsuarioAyudaController;

Route::controller(UsuarioAyudaController::class)->group(function() {

	Route::view('/', 'usuario_ayuda.list')->name('soporte.list');
	// ->middleware('permission:usuarios_ayuda.show');

	Route::get('/search', 'search');
	Route::get('/get-list-selects', 'getListSelects');

	Route::prefix('formulario-ayuda')->group(base_path('routes/cms/soporte-ayuda.php'));
	// Route::get('/form-selects', 'getFormSelects');

	// Route::get('/create', 'create');
	// Route::post('/store', 'store');


	Route::get('/{usuario_ayuda}/show', 'show');
	Route::get('/{usuario_ayuda}/edit', 'edit');
	Route::put('/{usuario_ayuda}/update', 'update');

	Route::put('/{usuario_ayuda}/status', 'status');
	Route::delete('/{usuario_ayuda}/destroy', 'destroy');

});
