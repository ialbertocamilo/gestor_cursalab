<?php
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\IntegrationRest\IntegrationsController;

    Route::controller(IntegrationsController::class)->group(function() {
        Route::post('/secret-key', 'getSecretKey');
        Route::post('/auth-user', 'authUser');
        Route::group(['middleware' => ['auth.guard:api','secretKey']],function () {
            Route::get('/criteria', 'getCriteria');
            Route::get('/criterion/{criterion_id}/values', 'getValuesCriterion');
            Route::post('/update-create-users', 'updateCreateUsers');
            Route::post('/inactivate-users', 'inactivateUsers');
            Route::post('/activate-users', 'activateUsers');
        });
    });
?>