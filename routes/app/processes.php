<?php

use App\Http\Controllers\Induction\ApiRest\RestProcessController;

Route::controller(RestProcessController::class)->group(function () {

    Route::get('/', 'getProcesses');
    Route::get('/{process}/data', 'getProcess');
});

Route::prefix('/{process}/activity')->group(base_path('routes/app/activities.php'));
