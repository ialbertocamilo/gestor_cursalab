<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FirebaseController;
use App\Http\Controllers\GuestRestController;
use App\Http\Controllers\ApiRest\AuthController;
use App\Http\Controllers\ApiRest\RestController;
use App\Http\Controllers\ApiRest\RestDataController;
use App\Http\Controllers\ApiRest\RestAyudaController;
use App\Http\Controllers\ApiRest\RestMeetingController;
use App\Http\Controllers\ApiRest\RestVademecumController;
use App\Http\Controllers\Auth\ResetPasswordApiController;
use App\Http\Controllers\ApiRest\RestReportesSupervisores;
use App\Http\Controllers\Auth\ForgotPasswordApiController;
use App\Http\Controllers\ApiRest\RestReportsUsersController;
use App\Http\Controllers\ApiRest\AuthImpersonationController;

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

Route::post('/quizz', [AuthController::class, 'quizz']);
Route::post('/reset', [AuthController::class, 'reset_password']);

// Route::get('/test/get-data', [AuthImpersonationController::class, 'getData']);
// Route::post('/test/send-log', [AuthImpersonationController::class, 'login']);

// === endpoint para configuracion de ambiente ===
Route::get('/config_ambiente', [AuthController::class, 'configuracion_ambiente']);

Route::group(['prefix' => 'auth', 'middleware' => 'throttle:800'], function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/login/external/{token}', [AuthImpersonationController::class, 'external'])
        ->name('login.external')->middleware('signed');
});

Route::group(['middleware' => 'auth:api', 'prefix' => 'auth'], function () {
    Route::post('logout', [AuthController::class, 'logout']);
});

Route::group(['middleware' => 'auth:api'], function () {
    Route::prefix('users')->group(base_path('routes/app/users.php'));
});

Route::group(['middleware' => ['auth:api', 'validated-session'], 'prefix' => 'rest'], function () {

    // Route::impersonate();

    Route::post('/usuario_upload_file', [RestController::class, 'usuario_upload_file']);
    Route::post('/guardar_token_firebase', [FirebaseController::class, 'guardarToken']);

    Route::get('/glosario/selects', [RestDataController::class,'glosarioSelects']);
    Route::get('/glosario/search', [RestDataController::class,'glosarioSearch']);


    Route::prefix('announcements')->group(base_path('routes/app/announcements.php'));

    Route::prefix('meetings')->group(base_path('routes/app/meetings.php'));

    Route::prefix('progreso')->group(base_path('routes/app/progreso.php'));
    Route::prefix('cursos')->group(base_path('routes/app/courses.php'));
    Route::prefix('courses-in-person')->group(base_path('routes/app/course-in-person.php'));
    Route::prefix('temas')->group(base_path('routes/app/topics.php'));
    Route::prefix('/')->group(base_path('routes/app/quizzes.php'));
    Route::prefix('/ranking')->group(base_path('routes/app/ranking.php'));
    Route::prefix('videoteca')->group(base_path('routes/app/videoteca.php'));
    Route::prefix('votaciones')->group(base_path('routes/app/votaciones.php'));
    Route::prefix('tareas')->group(base_path('routes/app/tareas.php'));

    Route::get('preguntas_seccion_ayuda', [RestAyudaController::class, 'preguntas_seccion_ayuda']);
    Route::get('preguntas_frecuentes', [RestAyudaController::class, 'preguntas_frecuentes']);
    Route::post('registra_ayuda', [RestAyudaController::class, 'registra_ayuda']);

    Route::get('reportes-supervisores/init', [RestReportesSupervisores::class, 'init']);

    Route::prefix('entrenamiento')->group(base_path('routes/app/checklist.php'));

    Route::get('vademecum/selects', [RestVademecumController::class, 'getSelects']);
    Route::get('vademecum/search', [RestVademecumController::class, 'loadUserModuleVademecum']);
    Route::get('vademecum/subcategorias/{categoryId}', [RestVademecumController::class, 'getSubCategorias']);
    Route::post('vademecum/store-visit/{vademecum}', [RestVademecumController::class, 'storeVisit']);
    Route::get('reports/user-history/{workspaceId?}/{schoolId?}', [RestReportsUsersController::class, 'fetchUserHistory']);
    Route::get('reports/user-history-filters', [RestReportsUsersController::class, 'fetchUserHistoryFilters']);
    Route::prefix('benefits')->group(base_path('routes/app/benefit.php'));
    Route::prefix('processes')->group(base_path('routes/app/processes.php'));

    Route::post('actualizar_correo', [RestAyudaController::class, 'solicitud_cambio_correo']);
});

Route::group(['middleware' => 'api', 'prefix' => 'rest'], function () {
    Route::get('existe_email/{email?}', [RestAyudaController::class, 'existe_email']);
    Route::post('registrar_soporte_login', [RestAyudaController::class, 'registra_ayuda_login']);
    Route::get('listar_empresas', [RestAyudaController::class, 'listar_empresas']);
    Route::prefix('checklist')->group(base_path('routes/app/checklist.php'));

    Route::post('/meetings/zoom/webhook-end-meeting', [RestMeetingController::class, 'zoomWebhookEndMeeting']);
    Route::post('/meetings/{meeting}/finish', [RestMeetingController::class,'finishMeeting']);
    /* apis para el formulario de invitados */
    Route::get('/verify-guest-url', [GuestRestController::class,'verifyGuestUrl']);
    Route::get('/child-criterion-values', [GuestRestController::class,'childCriterionValues']);
    Route::post('/send-code-guest', [GuestRestController::class,'sendGuestCodeVerificationByEmail']);
    Route::post('/verify-code-guest', [GuestRestController::class,'verifyGuestCodeVerificationByEmail']);
    Route::post('/store-guest', [GuestRestController::class,'storeGuest']);
    /* */
});

Route::post('password/email', [ForgotPasswordApiController::class, 'sendResetLinkEmail']);
Route::post('password/reset', [ResetPasswordApiController::class, 'reset']);
Route::post('cambiar-contrasenia', [ResetPasswordApiController::class, 'reset']);
Route::get('notifications', [FirebaseController::class, 'notificationValues']);


// Route::get('notifications', function () {
//     return response()->json([
//         'showModalM1' => env('SHOW_MODAL_M1'),
//         'showCloseButtonM1' => env('SHOW_CLOSE_BUTTON_M1'),
//         'showModalM2' => env('SHOW_MODAL_M2'),
//         'showCloseButtonM2' => env('SHOW_CLOSE_BUTTON_M2'),
//         'showModalM3' => env('SHOW_MODAL_M3'),
//         'showCloseButtonM3' => env('SHOW_CLOSE_BUTTON_M3'),
//         'showMessageM4' => env('SHOW_MESSAGE_M4'),
//         'showIosLink' => env('SHOW_IOS_LINK')
//     ]);
// });



//Route::controller(TestController::class)->group(function () {
//
//    Route::get('/test/users', 'users');
//    Route::get('/test/workspaces', 'workspaces');
//    Route::get('/test/schools', 'schools');
//    Route::get('/test/courses', 'courses');
//
//    Route::get('/test/blocks', 'blocks');
//});
