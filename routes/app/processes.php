<?php

use App\Http\Controllers\Induction\ApiRest\RestProcessController;

Route::controller(RestProcessController::class)->group(function () {

    // api para supervisor
    Route::get('/supervisor/processes', 'getSupervisorProcesses');
    Route::get('/supervisor/processes/{process}/data', 'getSupervisorProcess');
    Route::get('/supervisor/processes/{process}/data/students', 'getSupervisorProcessOnlyStudents');
    Route::get('/supervisor/processes/{process}/data/supervisors', 'getSupervisorProcessOnlySupervisors');

    Route::get('/supervisor/processes/info_student/{user}', 'getInfoStudent');
    Route::post('/supervisor/processes/{process}/save_attendance', 'saveAttendance');
    Route::get('/supervisor/processes/users_absences_massive', 'getUsersAbsencesMassive');
    Route::post('/supervisor/processes/save_attendance_massive', 'saveAttendanceMassive');

    // api para colaborador
    Route::get('/user/processes/{process}/data', 'getUserProcess');
    Route::get('/user/processes/{process}/data/instructions', 'getUserProcessInstructions');
    Route::post('/user/processes/{process}/data/instructions/save', 'saveUserProcessInstructions');

    
    Route::get('/faqs', 'getFaqs');

    // momentaneo
    Route::get('/', 'getSupervisorProcesses');
    Route::get('/{process}/data', 'getProcess');

    Route::get('/info_student/{user}', 'getInfoStudent');
    Route::post('/{process}/save_attendance', 'saveAttendance');
    Route::get('/users_absences_massive', 'getUsersAbsencesMassive');
    Route::post('/save_attendance_massive', 'saveAttendanceMassive');

});

Route::prefix('/user/processes/{process}/activity')->group(base_path('routes/app/activities.php'));
Route::prefix('/supervisor/processes/{process}/activity')->group(base_path('routes/app/activities.php'));
Route::prefix('/{process}/activity')->group(base_path('routes/app/activities.php'));
