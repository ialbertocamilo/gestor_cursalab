<?php

use App\Http\Controllers\Induction\StageController;

Route::controller(StageController::class)->group(function() {

    Route::view('/', 'stages.index')->name('stages.index');
    Route::get('/search', 'search');

    Route::post('/store','store');
    Route::get('/{stage}/edit', 'edit');
    Route::post('/{stage}/update','update');
    Route::delete('/{stage}/delete', 'destroy');

});


// Activities
Route::prefix('{stage}/activity')->group(base_path('routes/cms/activities.php'));
