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
use App\Http\Middleware\CheckRol;

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
Route::group(['middleware' => ['jwt.verify']], function () {
    Route::get('adjuntar_archivo', 'ApiRest\AdjuntarArchivosController@index')->name('adjuntar_archivo');
});
/*Información entra de la app-web*/
Route::get('informacion_app', function () {
    return view('informacion_app');
});

Route::middleware(['auth'])->group(function () {


    // Route::prefix('users')->middleware('checkrol:qqqq,admin,dasdas,dasda')->group(base_path('routes/cms/users.php'));
    Route::prefix('/')->group(base_path('routes/cms/temp.php'));

    Route::prefix('general')->group(base_path('routes/cms/general.php'));
    Route::prefix('common')->group(base_path('routes/cms/common.php'));

    // Route::middleware(['checkrol:config'])->group(function () {
    //     groupRoles();
    // });
    // Route::middleware(['checkrol:admin'])->group(function () {
    //     groupCursos();
    //     groupContenido();
    //     groupEntrenamiento();
    // });
    // Route::middleware(['checkrol:content-manager'])->group(function () {
    //     groupContenido();
    // });
    // Route::middleware(['checkrol:trainer'])->group(function () {
    //     groupCursos();
    //     groupEntrenamiento();
    // });
    // Route::middleware(['checkrol:reports'])->group(function () {
    //     groupRoles();
    // });
    // Route::middleware(['checkrol:super-admin'])->group(function () {
    groupCursos();
    groupEntrenamiento();
    groupUsuarios();
    groupAuditoria();
    groupContenido();
    groupRoles();
    groupSoporte();

    Route::prefix('programas')->group(base_path('routes/cms/blocks.php'));
    Route::prefix('media')->group(base_path('routes/cms/media.php'));


    Route::prefix('workspaces')->group(base_path('routes/cms/workspaces.php'));
    Route::prefix('/')->group(base_path('routes/cms/reportes.php'));

    Route::prefix('aulas-virtuales')->group(base_path('routes/cms/meetings.php'));
    // });
});

function groupCursos()
{
    Route::prefix('modulos')->group(base_path('routes/cms/modulos.php'));
    Route::prefix('segments')->group(base_path('routes/cms/segments.php'));
    Route::prefix('entrenadores')->group(base_path('routes/cms/entrenadores.php'));
    Route::prefix('escuelas')->group(base_path('routes/cms/escuelas.php'));
}

function groupEntrenamiento()
{
    Route::prefix('entrenamiento')->group(base_path('routes/cms/entrenamiento.php'));
}

function groupUsuarios()
{
    Route::prefix('usuarios')->group(base_path('routes/cms/usuarios.php'));
    Route::prefix('cargos')->group(base_path('routes/cms/cargos.php'));
    Route::prefix('boticas')->group(base_path('routes/cms/boticas.php'));
    Route::prefix('criterios')->group(base_path('routes/cms/criteria.php'));
    Route::prefix('supervisores')->group(base_path('routes/cms/supervisores.php'));
}

function groupAuditoria()
{
    Route::prefix('errores')->group(base_path('routes/cms/errores.php'));
    Route::prefix('incidencias')->group(base_path('routes/cms/incidencias.php'));
    Route::prefix('auditoria')->group(base_path('routes/cms/audits.php'));
}

function groupContenido()
{
    Route::prefix('anuncios')->group(base_path('routes/cms/anuncios.php'));
    Route::prefix('encuestas')->group(base_path('routes/cms/encuestas.php'));
    Route::prefix('multimedia')->group(base_path('routes/cms/multimedia.php'));
    Route::prefix('glosario')->group(base_path('routes/cms/glosario.php'));
    Route::prefix('vademecum')->group(base_path('routes/cms/vademecum.php'));
    Route::prefix('videoteca')->group(base_path('routes/cms/videoteca.php'));
    Route::prefix('tags')->group(base_path('routes/cms/tags.php'));
}

function groupRoles()
{
    Route::prefix('users')->group(base_path('routes/cms/users.php'));
    Route::prefix('permisos')->group(base_path('routes/cms/permisos.php'));
    Route::prefix('roles')->group(base_path('routes/cms/roles.php'));
}

function groupSoporte()
{
    Route::prefix('ayudas')->group(base_path('routes/cms/ayudas.php'));
    Route::prefix('preguntas-frecuentes')->group(base_path('routes/cms/preguntas_frecuentes.php'));
    Route::prefix('notificaciones_push')->group(base_path('routes/cms/notificaciones_push.php'));
    Route::prefix('soporte')->group(base_path('routes/cms/soporte.php'));
}
