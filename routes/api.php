<?php

use App\Http\Controllers\ApiRest\AuthController;
use App\Http\Controllers\Test\TestController;
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

Route::group(['prefix' => 'auth'], function () {

    Route::post('/login', [AuthController::class, 'login']);

});

Route::group(['middleware' => 'auth:api', 'prefix' => 'rest'], function () {

    Route::prefix('announcements')->group(base_path('routes/app/announcements.php'));


    Route::prefix('meetings')->group(base_path('routes/app/meetings.php'));


    Route::prefix('cursos')->group(base_path('routes/app/courses.php'));
    Route::prefix('temas')->group(base_path('routes/app/topics.php'));
    Route::prefix('/')->group(base_path('routes/app/quizzes.php'));
});

Route::controller(TestController::class)->group(function() {

    Route::get('/test/users', 'users');
    Route::get('/test/workspaces', 'workspaces');
    Route::get('/test/schools', 'schools');
    Route::get('/test/courses', 'courses');

    Route::get('/test/blocks', 'blocks');
});
