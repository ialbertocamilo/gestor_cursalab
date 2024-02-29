<?php

use App\Http\Controllers\Induction\ApiRest\RestProcessController;

Route::controller(RestProcessController::class)->group(function () {

    Route::get('/', 'getProcesses');
    Route::get('/{process}/data', 'getProcess');

    Route::get('/info_student/{user}', 'getInfoStudent');
    Route::post('/{process}/save_attendance', 'saveAttendance');
    Route::get('/users_absences_massive', 'getUsersAbsencesMassive');
    Route::post('/save_attendance_massive', 'saveAttendanceMassive');

});

Route::prefix('/{process}/activity')->group(base_path('routes/app/activities.php'));
