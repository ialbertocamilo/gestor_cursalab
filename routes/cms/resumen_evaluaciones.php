<?php
    
use App\Http\Controllers\EvaluationsReportController;

Route::controller(EvaluationsReportController::class)->group(function() {
    Route::view('/', 'resumen_evaluaciones.index')->name('encuestas.list');
    Route::get('modules', 'loadModules');
    Route::get('schools/{modules}', 'loadSchools');
    Route::get('courses/{schools}', 'loadCourses');
    Route::get('topics/{courses}', 'loadTopics');
    
    Route::put('evaluations_data', 'loadEvaluationReportData');
    Route::put('evaluations_data_detail/{topic}', 'loadEvaluationDetailReportData');
});

