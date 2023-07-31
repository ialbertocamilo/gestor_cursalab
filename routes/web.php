<?php

use App\Http\Middleware\CheckRol;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CursosController;
use App\Http\Controllers\GestorController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\TwoFAController;
use App\Http\Controllers\ImpersonateController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\ApiRest\AdjuntarArchivosController;

Route::redirect('/', 'login', 301);

// login routes
Route::get('login', [LoginController::class, 'showLoginFormInit'])->name('login');
Route::post('login_post', [LoginController::class, 'login'])->name('login_post');
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// 2FA routes
Route::get('2fa', [TwoFAController::class, 'showAuth2faForm'])->name('2fa');
Route::post('login_auth2fa', [LoginController::class, 'auth2fa'])->name('login_auth2fa');
Route::get('login_auth2fa_resend', [LoginController::class, 'auth2fa_resend'])->name('login_auth2fa_resend');

// laravel reset pass
Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

// custom reset pass
Route::get('reset/{token}', [ResetPasswordController::class, 'showResetFormInit'])->name('reset');
Route::post('password_reset', [LoginController::class, 'reset_pass'])->name('password_reset');


Route::get('home', [DashboardController::class, 'index'])->name('home');
Route::get('welcome', [DashboardController::class, 'index'])->name('home');

// DESCARGAS
Route::get('dnx/{id}', [GestorController::class, 'descargaArchivo']);
Route::get('dnv/{id}', [GestorController::class, 'descargaVideo']);

//Route::get('tools/ver_diploma/{iduser}/{idvideo}', 'GestorController@verCertificado');
Route::get('tools/ver_diploma/{user_id}/{course_id}', [GestorController::class, 'verCertificado']);
//Route::get('tools/dnc/{iduser}/{idvideo}', 'GestorController@descargaCertificado');
Route::get('tools/dnc/{user_id}/{course_id}', [GestorController::class, 'descargaCertificado']);

Route::get('multimedia/topic/{media_topic_id}/download', [\App\Http\Controllers\MediaController::class, 'downloadMediaTopicExternalFile'])->name('media.download.media_topic');


Route::get('tools/ver_diploma/escuela/{usuario_id}/{categoria_id}', [GestorController::class, 'verCertificadoEscuela']);
Route::get('tools/dnc/escuela/{usuario_id}/{categoria_id}', [GestorController::class, 'descargaCertificadoEscuela']);
/**************************** ADJUNTAR ARCHIVOS **************************************/
Route::middleware(['web'])->group(function () {
    Route::get('adjuntar_archivo', [AdjuntarArchivosController::class, 'index'])->name('adjuntar_archivo');
});
/*Información entra de la app-web*/
Route::get('informacion_app', function () {
    return view('informacion_app');
});

Route::middleware(['auth_2fa','auth'])->group(function () {

    Route::get('/impersonate/leave', [ImpersonateController::class, 'leave'])->name('impersonate.leave');
    Route::get('/impersonate/take/{value}', [ImpersonateController::class, 'take'])->name('impersonate')->middleware('checkrol:super-user');

    // Route::view('welcome', 'welcome');

    Route::get('/workspaces/search', [WorkspaceController::class, 'search']);
    Route::put('/usuarios/session/workspace/{workspace}', [UsuarioController::class, 'updateWorkspaceInSession']);
    Route::get('/usuarios/session', [UsuarioController::class, 'session']);

    // cambiar contraseña gestor
    Route::view('/reset_password', 'usuarios.reset-pass');
    Route::post('/user_password_reset', [UsuarioController::class, 'updatePasswordUser'])->name('usuarios.user_password_reset');

    Route::prefix('/')->middleware('checkrol:admin')->group(base_path('routes/cms/temp.php'));
    Route::get('dashboard_pbi', [GeneralController::class, 'getPowerBiView'])->middleware('checkrol:admin,reports');

    Route::prefix('general')->middleware('checkrol:admin')->group(base_path('routes/cms/general.php'));
    Route::prefix('common')->middleware('checkrol:admin')->group(base_path('routes/cms/common.php'));


    Route::prefix('anuncios')->middleware('checkrol:admin,content-manager')->group(base_path('routes/cms/anuncios.php'));
    Route::prefix('encuestas')->middleware('checkrol:admin,content-manager')->group(base_path('routes/cms/encuestas.php'));
    Route::prefix('multimedia')->middleware('checkrol:admin,content-manager')->group(base_path('routes/cms/multimedia.php'));
    Route::prefix('glosario')->middleware('checkrol:admin,content-manager')->group(base_path('routes/cms/glosario.php'));
    Route::prefix('protocolos-y-documentos')->middleware('checkrol:admin,content-manager')->group(base_path('routes/cms/vademecum.php'));
    Route::prefix('videoteca')->middleware('checkrol:admin,content-manager')->group(base_path('routes/cms/videoteca.php'));
    Route::prefix('tags')->middleware('checkrol:admin,content-manager')->group(base_path('routes/cms/tags.php'));


    Route::prefix('ayudas')->middleware('checkrol:admin')->group(base_path('routes/cms/ayudas.php'));
    Route::prefix('preguntas-frecuentes')->middleware('checkrol:config')->group(base_path('routes/cms/preguntas_frecuentes.php'));
    Route::prefix('notificaciones_push')->middleware('checkrol:admin')->group(base_path('routes/cms/notificaciones_push.php'));
    Route::prefix('soporte')->middleware('checkrol:admin')->group(base_path('routes/cms/soporte.php'));


    Route::prefix('users')->middleware('checkrol:config')->group(base_path('routes/cms/users.php'));
    Route::prefix('permisos')->middleware('checkrol:admin')->group(base_path('routes/cms/permisos.php'));
    Route::prefix('roles')->middleware('checkrol:config')->group(base_path('routes/cms/roles.php'));


    Route::prefix('errores')->middleware('checkrol:admin')->group(base_path('routes/cms/errores.php'));
    Route::prefix('incidencias')->middleware('checkrol:admin')->group(base_path('routes/cms/incidencias.php'));
    Route::prefix('auditoria')->middleware('checkrol:super-user')->group(base_path('routes/cms/audits.php'));
    // Route::prefix('auditoria')->middleware('checkrol:super-user')->group(base_path('routes/cms/audits.php'));


    Route::prefix('usuarios')->middleware('checkrol:admin')->group(base_path('routes/cms/usuarios.php'));
    Route::prefix('cargos')->middleware('checkrol:admin')->group(base_path('routes/cms/cargos.php'));
    Route::prefix('boticas')->middleware('checkrol:admin')->group(base_path('routes/cms/boticas.php'));
    Route::prefix('criterios')->middleware('checkrol:config,admin')->group(base_path('routes/cms/criteria.php'));
    Route::prefix('supervisores')->middleware('checkrol:admin')->group(base_path('routes/cms/supervisores.php'));


    Route::prefix('modulos')->middleware('checkrol:admin,content-manager')->group(base_path('routes/cms/modulos.php'));
    Route::prefix('segments')->middleware('checkrol:admin,content-manager')->group(base_path('routes/cms/segments.php'));
    Route::prefix('entrenadores')->middleware('checkrol:admin,content-manager')->group(base_path('routes/cms/entrenadores.php'));
    Route::prefix('escuelas')->middleware('checkrol:admin,content-manager')->group(base_path('routes/cms/escuelas.php'));

    Route::prefix('cursos')->middleware('checkrol:admin,content-manager')->group(base_path('routes/cms/curso.php'));

    Route::prefix('entrenamiento')->middleware('checkrol:admin,content-manager')->group(base_path('routes/cms/entrenamiento.php'));

    Route::prefix('programas')->middleware('checkrol:admin')->group(base_path('routes/cms/blocks.php'));
    Route::prefix('media')->middleware('checkrol:admin,content-manager')->group(base_path('routes/cms/media.php'));

    Route::prefix('ambiente')->middleware('checkrol:admin,super-user')->group(base_path('routes/cms/ambiente.php'));

    Route::prefix('workspaces')->middleware('checkrol:admin')->group(base_path('routes/cms/workspaces.php'));
    Route::prefix('/')->middleware(['checkrol:admin,reports,only-reports'])->group(base_path('routes/cms/reportes.php'));

    Route::prefix('aulas-virtuales')->middleware('checkrol:admin')->group(base_path('routes/cms/meetings.php'));

    Route::prefix('procesos-masivos')->middleware('checkrol:admin')->group(base_path('routes/cms/masivos.php'));
    Route::prefix('importar-notas')->middleware('checkrol:admin')->group(base_path('routes/cms/importar-notas.php'));

    Route::view('/documentation-api/{list_apis?}', 'documentation-api.index')->name('documentation-api.index')->middleware('checkrol:admin,super-user');

    Route::prefix('resumen_encuesta')->middleware('checkrol:admin,reports,only-reports')->group(base_path('routes/cms/resumen_encuesta.php'));
    
    Route::prefix('resumen_evaluaciones')->middleware('checkrol:admin,reports,only-reports')->group(base_path('routes/cms/resumen_evaluaciones.php'));

    Route::prefix('beneficios')->middleware('checkrol:admin,content-manager')->group(base_path('routes/cms/beneficios.php'));
    Route::prefix('speakers')->middleware('checkrol:admin,content-manager')->group(base_path('routes/cms/speakers.php'));
    Route::prefix('diplomas')->middleware('checkrol:admin')->group(base_path('routes/cms/diplomas.php'));
    
    // === votaciones === 
    Route::prefix('votaciones')->middleware('checkrol:admin')->group(base_path('routes/cms/votaciones.php'));
    // === votaciones === 
});
