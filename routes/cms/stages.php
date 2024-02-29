<?php

use App\Http\Controllers\Induction\StageController;

Route::controller(StageController::class)->group(function() {

    Route::view('/', 'stages.index')->name('stages.index');
    Route::get('/search', 'search');
	Route::get('/form-selects', 'getFormSelects');

    Route::post('/store','store');
    Route::get('/{stage}/edit', 'edit');
    Route::put('/{stage}/update','update');
    Route::put('/{stage}/status', 'status');
    Route::delete('/{stage}/delete', 'destroy');

});


// Activities
Route::prefix('{stage}/activity')->group(base_path('routes/cms/activities.php'));
Route::prefix('{stage}/diploma')->group(base_path('routes/cms/induccion/stages/diploma.php'));
