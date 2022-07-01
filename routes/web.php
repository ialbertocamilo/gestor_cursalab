<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;

Route::redirect('/', 'login', 301);

Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login_post', [LoginController::class, 'login'])->name('login_post');
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

Route::get('home', [DashboardController::class, 'index'])->name('home');

// DESCARGAS
Route::get('dnx/{id}', 'GestorController@descargaArchivo');
Route::get('dnv/{id}', 'GestorController@descargaVideo');
Route::get('tools/ver_diploma/{iduser}/{idvideo}', 'GestorController@verCertificado');
Route::get('tools/dnc/{iduser}/{idvideo}', 'GestorController@descargaCertificado');

Route::get('tools/ver_diploma/escuela/{usuario_id}/{categoria_id}', 'GestorController@verCertificadoEscuela');
Route::get('tools/dnc/escuela/{usuario_id}/{categoria_id}', 'GestorController@descargaCertificadoEscuela');
/**************************** ADJUNTAR ARCHIVOS **************************************/
Route::group(['middleware' => ['jwt.verify']], function() {
    Route::get('adjuntar_archivo', 'ApiRest\AdjuntarArchivosController@index')->name('adjuntar_archivo');
});
/*Información entra de la app-web*/
Route::get('informacion_app', function(){
    return view('informacion_app');
});

Route::middleware(['auth'])->group(function () {

    Route::prefix('/')->group(base_path('routes/cms/temp.php'));

    Route::prefix('general')->group(base_path('routes/cms/general.php'));
    Route::prefix('common')->group(base_path('routes/cms/common.php'));
    Route::prefix('usuarios')->group(base_path('routes/cms/usuarios.php'));
    Route::prefix('modulos')->group(base_path('routes/cms/modulos.php'));
    Route::prefix('media')->group(base_path('routes/cms/media.php'));
    Route::prefix('anuncios')->group(base_path('routes/cms/anuncios.php'));
    Route::prefix('ayudas')->group(base_path('routes/cms/ayudas.php'));
    Route::prefix('cargos')->group(base_path('routes/cms/cargos.php'));
    Route::prefix('boticas')->group(base_path('routes/cms/boticas.php'));
    Route::prefix('tipo-criterios')->group(base_path('routes/cms/tipo_criterios.php'));
    Route::prefix('encuestas')->group(base_path('routes/cms/encuestas.php'));
    Route::prefix('glosario')->group(base_path('routes/cms/glosario.php'));
    Route::prefix('vademecum')->group(base_path('routes/cms/vademecum.php'));
    Route::prefix('preguntas-frecuentes')->group(base_path('routes/cms/preguntas_frecuentes.php'));
    // Route::prefix('aulas_virtuales')->group(base_path('routes/cms/aulas_virtuales.php'));
    Route::prefix('entrenadores')->group(base_path('routes/cms/entrenadores.php'));
    Route::prefix('notificaciones_push')->group(base_path('routes/cms/notificaciones_push.php'));
    Route::prefix('multimedia')->group(base_path('routes/cms/multimedia.php'));
    Route::prefix('supervisores')->group(base_path('routes/cms/supervisores.php'));
    Route::prefix('soporte')->group(base_path('routes/cms/soporte.php'));
    Route::prefix('tags')->group(base_path('routes/cms/tags.php'));
    Route::prefix('videoteca')->group(base_path('routes/cms/videoteca.php'));
    Route::prefix('roles')->group(base_path('routes/cms/roles.php'));
    Route::prefix('entrenamiento')->group(base_path('routes/cms/entrenamiento.php'));
    Route::prefix('/')->group(base_path('routes/cms/reportes.php'));
    // Route::prefix('reportes')->group(base_path('routes/cms/reportes.php'));
    // Route::prefix('exportar')->group(base_path('routes/cms/reportes-exportar.php'));
    // Route::prefix('cuentas-zoom')->group(base_path('routes/cms/cuentas_zoom.php'));
    Route::prefix('errores')->group(base_path('routes/cms/errores.php'));
    Route::prefix('incidencias')->group(base_path('routes/cms/incidencias.php'));

    Route::prefix('aulas-virtuales')->group(base_path('routes/cms/meetings.php'));
});