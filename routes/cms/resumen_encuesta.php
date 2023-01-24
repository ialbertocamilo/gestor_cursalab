<?php
    
use App\Http\Controllers\PollReportController;
Route::controller(PollReportController::class)->group(function() {
    Route::view('/', 'resumen_encuesta.indexv2')->name('encuestas.list');
    Route::get('initial-data','loadInititalData');
    Route::get('schools/{poll_id}','loadSchools');
    Route::post('courses','loadCourses');
    Route::post('poll-data','loadPollReportData');
});
?>