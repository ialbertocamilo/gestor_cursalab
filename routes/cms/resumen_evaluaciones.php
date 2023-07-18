<?php
    
use App\Http\Controllers\EvaluationsReportController;
Route::controller(EvaluationsReportController::class)->group(function() {
    Route::view('/', 'resumen_evaluaciones.index')->name('encuestas.list');
    Route::get('initial-data','loadInititalData');
    Route::post('schools/{poll_id}','loadSchools');
    Route::post('courses','loadCourses');
    Route::post('poll-data','loadPollReportData');
});
?>