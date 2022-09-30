<?php

use App\Http\Controllers\ApiRest\AuthController;
use App\Http\Controllers\ApiRest\RestAyudaController;
use App\Http\Controllers\Auth\ForgotPasswordApiController;
use App\Http\Controllers\Auth\ResetPasswordApiController;
use App\Http\Controllers\FirebaseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::post('users/login', [AuthenticationController::class, 'login']);

// Route::group(['middleware' => 'auth:api'], function () {

//     Route::patch('fcm_token', [AuthenticationController::class, 'updateToken']);

//     Route::get('users/me', [AuthenticationController::class, 'me']);
//     Route::get('users/profile', [AuthenticationController::class, 'profile']);
//     Route::get('users/test', [AuthenticationController::class, 'test']);
//     Route::post('users/logout', [AuthenticationController::class, 'logout']);

//     Route::prefix('users')->group(base_path('routes/cms/users.php'));
//     Route::prefix('audits')->group(base_path('routes/cms/audits.php'));
//     Route::prefix('roles')->group(base_path('routes/cms/roles.php'));

// });

Route::get('/rest/app_versions', [FirebaseController::class, 'appVersions']);

Route::group(['prefix' => 'auth'], function () {

    Route::post('/login', [AuthController::class, 'login']);
});

Route::group(['middleware' => 'auth:api', 'prefix' => 'auth'], function () {
    Route::post('logout', [AuthController::class, 'logout']);
});

Route::group(['middleware' => 'auth:api', 'prefix' => 'rest'], function () {

    Route::post('/guardar_token_firebase', [FirebaseController::class, 'guardarToken']);


    Route::prefix('announcements')->group(base_path('routes/app/announcements.php'));


    Route::prefix('meetings')->group(base_path('routes/app/meetings.php'));


    Route::prefix('progreso')->group(base_path('routes/app/progreso.php'));
    Route::prefix('cursos')->group(base_path('routes/app/courses.php'));
    Route::prefix('temas')->group(base_path('routes/app/topics.php'));
    Route::prefix('/')->group(base_path('routes/app/quizzes.php'));
    Route::prefix('/ranking')->group(base_path('routes/app/ranking.php'));


    Route::get('preguntas_seccion_ayuda', [RestAyudaController::class, 'preguntas_seccion_ayuda']);
    Route::get('preguntas_frecuentes', [RestAyudaController::class, 'preguntas_frecuentes']);
    Route::post('registra_ayuda', [RestAyudaController::class, 'registra_ayuda']);
});

Route::group(['middleware' => 'api', 'prefix' => 'rest'], function () {
    Route::post('registrar_soporte_login', [RestAyudaController::class, 'registra_ayuda_login']);
    Route::get('listar_empresas', [RestAyudaController::class, 'listar_empresas']);
});

Route::post('password/email', [ForgotPasswordApiController::class, 'sendResetLinkEmail']);
Route::post('password/reset', [ResetPasswordApiController::class, 'reset']);

Route::get('notifications', function () {
    return response()->json([
        'showNewAppNotification' => env('SHOW_NEW_APP_NOTIFICATION'),
        'showCloseNotificationButton' => env('SHOW_CLOSE_NOTIFICATION_BUTTON')
    ]);
});

//Route::controller(TestController::class)->group(function () {
//
//    Route::get('/test/users', 'users');
//    Route::get('/test/workspaces', 'workspaces');
//    Route::get('/test/schools', 'schools');
//    Route::get('/test/courses', 'courses');
//
//    Route::get('/test/blocks', 'blocks');
//});
