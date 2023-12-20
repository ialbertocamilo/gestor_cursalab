<?php

use App\Http\Middleware\CheckRol;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CursosController;
use App\Http\Controllers\GestorController;
use App\Http\Controllers\DiplomaController;
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
//temporary route
// Route::get('email_info',function(){
//     $mail_data=[
//         'init_date'=> date('d/m/Y', strtotime('-1 day')). ' 6:00 am',
//         'final_date'=> date('d/m/Y').' 5:30 am',
//         'workspaces'=>[
//             [
//               "workspace_name" => "Intercorp Retail",
//               "download_url" => "http://localhost:3000/reports/general_api_report_2023-06-05.xlsx"
//             ],
//             [
//               "workspace_name" => "Financiera Oh",
//               "download_url" => "http://localhost:3000/reports/Financiera-Oh_2023-06-05.xlsx"
//             ]
//         ]
//     ];
//     return view('emails.email_information_apis',['data'=>$mail_data]);
// });
// Route::view('email_limite','emails.email_limite_usuarios');
Route::view('welcome_email','emails.welcome_email');
Route::view('plataforma-suspendida','platform-cutoff')->middleware('platform-access-blocked');

Route::get('email_reset',function(){
    $mail_data=[
        'user'=>'Aldo López',
        'url_to_reset'=> 'aslkdjasldk',
        'minutes' => 60
    ];
    return view('emails.reset_password_gestor',['data'=>$mail_data]);
});

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
Route::get('tools/ver_diploma/{user_id}/{course_id}', [DiplomaController::class, 'downloadCertificate'])->name('diplomas.view');
// Route::get('tools/ver_diploma/{user_id}/{course_id}', [GestorController::class, 'verCertificado']);
//Route::get('tools/dnc/{iduser}/{idvideo}', 'GestorController@descargaCertificado');
Route::get('tools/dnc/{user_id}/{course_id}', [DiplomaController::class, 'downloadCertificate'])->name('diplomas.download');
// Route::get('tools/dnc/{user_id}/{course_id}', [GestorController::class, 'descargaCertificado']);

Route::get('multimedia/topic/{media_topic_id}/download', [\App\Http\Controllers\MediaController::class, 'downloadMediaTopicExternalFile'])->name('media.download.media_topic');
Route::get('tareas/resource/download', [\App\Http\Controllers\RestProjectController::class, 'downloadFile'])->name('tareas.resources.download');

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

Route::middleware(['auth_2fa', 'auth', 'validated-admin-session'])->group(function () {

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
    Route::prefix('intentos-masivos')->middleware('hasHability:attemps-massive')->group(base_path('routes/cms/intentos-masivos.php'));
    Route::prefix('notificaciones-push')->middleware('hasHability:push-notification')->group(base_path('routes/cms/notificaciones-push.php'));
    Route::prefix('votacion')->middleware('hasHability:create-campaign')->group(base_path('routes/cms/votacion-views.php'));
    Route::prefix('diploma')->middleware('hasHability:create-certificate')->group(base_path('routes/cms/diploma.php'));

    

    
    Route::get('dashboard_pbi', [GeneralController::class, 'getPowerBiView'])->middleware('hasHability:learning-analytics');

    Route::prefix('general')->middleware('hasHability:dashboard')->group(base_path('routes/cms/general.php'));
    Route::prefix('common')->group(base_path('routes/cms/common.php'));


    Route::prefix('anuncios')->middleware('hasHability:announcement')->group(base_path('routes/cms/anuncios.php'));
    Route::prefix('encuestas')->middleware('hasHability:pool')->group(base_path('routes/cms/encuestas.php'));
    Route::prefix('multimedia')->middleware('hasHability:media')->group(base_path('routes/cms/multimedia.php'));
    Route::prefix('glosario')->middleware('hasHability:glossary')->group(base_path('routes/cms/glosario.php'));
    Route::prefix('protocolos-y-documentos')->middleware('hasHability:vademecun')->group(base_path('routes/cms/vademecum.php'));
    Route::prefix('videoteca')->middleware('hasHability:videoteca')->group(base_path('routes/cms/videoteca.php'));
    // Route::prefix('tags')->middleware('checkrol:admin,content-manager')->group(base_path('routes/cms/tags.php'));


    // Route::prefix('ayudas')->middleware('checkrol:admin')->group(base_path('routes/cms/ayudas.php'));
    Route::prefix('preguntas-frecuentes')->middleware('hasHability:frequent-questions')->group(base_path('routes/cms/preguntas_frecuentes.php'));
    Route::prefix('notificaciones_push')->middleware('hasHability:push-notification')->group(base_path('routes/cms/notificaciones_push.php'));
    Route::prefix('soporte')->middleware('hasHability:support')->group(base_path('routes/cms/soporte.php'));


    Route::prefix('users')->middleware('checkrol:super-user')->group(base_path('routes/cms/users.php'));
    Route::controller(UserController::class)->group(function () {
        Route::get('users/{document}/current-courses', 'currentCourses');
    });
    // Route::prefix('permisos')->middleware('checkrol:super-user')->group(base_path('routes/cms/permisos.php'));
    Route::prefix('roles')->middleware('checkrol:super-user')->group(base_path('routes/cms/roles.php'));


    Route::prefix('errores')->middleware('checkrol:admin')->group(base_path('routes/cms/errores.php'));
    // Route::prefix('incidencias')->middleware('checkrol:admin')->group(base_path('routes/cms/incidencias.php'));
    Route::prefix('auditoria')->middleware('checkrol:super-user')->group(base_path('routes/cms/audits.php'));
    // Route::prefix('auditoria')->middleware('checkrol:super-user')->group(base_path('routes/cms/audits.php'));


    Route::prefix('usuarios')->middleware('hasHability:users')->group(base_path('routes/cms/usuarios.php'));
    // Route::prefix('cargos')->middleware('checkrol:admin')->group(base_path('routes/cms/cargos.php'));
    // Route::prefix('boticas')->middleware('checkrol:admin')->group(base_path('routes/cms/boticas.php'));
    Route::prefix('criterios')->middleware('hasHability:criteria')->group(base_path('routes/cms/criteria.php'));
    Route::prefix('supervisores')->middleware('hasHability:supervisor')->group(base_path('routes/cms/supervisores.php'));


    Route::prefix('modulos')->middleware('hasHability:modules')->group(base_path('routes/cms/modulos.php'));
    Route::prefix('segments')->group(base_path('routes/cms/segments.php'));
    Route::prefix('entrenadores')->middleware('hasHability:trainer')->group(base_path('routes/cms/entrenadores.php'));
    Route::prefix('escuelas')->middleware('hasHability:school')->group(base_path('routes/cms/escuelas.php'));

    Route::prefix('cursos')->middleware('hasHability:course')->group(base_path('routes/cms/curso.php'));

    Route::prefix('entrenamiento')->group(base_path('routes/cms/entrenamiento.php'));

    // Route::prefix('programas')->middleware('checkrol:admin')->group(base_path('routes/cms/blocks.php'));
    Route::prefix('media')->group(base_path('routes/cms/media.php'));

    Route::prefix('ambiente')->middleware('hasHability:configuration-environment')->group(base_path('routes/cms/ambiente.php'));

    Route::prefix('workspaces')->group(base_path('routes/cms/workspaces.php'));
    Route::prefix('/')->middleware('hasHability:report')->group(base_path('routes/cms/reportes.php'));

    Route::prefix('aulas-virtuales')->group(base_path('routes/cms/meetings.php'));

    Route::prefix('procesos-masivos')->middleware('hasHability:process-massive')->group(base_path('routes/cms/masivos.php'));
    Route::prefix('importar-notas')->middleware('hasHability:upload-grades-massive')->group(base_path('routes/cms/importar-notas.php'));

    Route::view('/documentation-api/{list_apis?}', 'documentation-api.index')->name('documentation-api.index');

    Route::prefix('resumen_encuesta')->middleware('hasHability:poll-report')->group(base_path('routes/cms/resumen_encuesta.php'));
    
    Route::prefix('resumen_evaluaciones')->middleware('hasHability:evaluation-report')->group(base_path('routes/cms/resumen_evaluaciones.php'));

    Route::prefix('beneficios')->middleware('hasHability:benefits')->group(base_path('routes/cms/beneficios.php'));
    Route::prefix('speakers')->middleware('hasHability:speaker')->group(base_path('routes/cms/speakers.php'));
    Route::prefix('diplomas')->middleware('hasHability:list-certificate')->group(base_path('routes/cms/diplomas.php'));
    
    // === votaciones === 
    Route::prefix('votaciones')->middleware('hasHability:create-campaign')->group(base_path('routes/cms/votaciones.php'));

    Route::prefix('projects')->middleware('hasHability:projects')->group(base_path('routes/cms/projects.php'));
    Route::prefix('jarvis')->group(base_path('routes/cms/jarvis.php'));

    Route::prefix('menus')->middleware('checkrol:super-user')->group(base_path('routes/cms/menus.php'));

    Route::prefix('invitados')->middleware('checkrol:super-user')->group(base_path('routes/cms/invitados.php'));
    Route::prefix('testing')->middleware('checkrol:super-user')->group(base_path('routes/cms/testing.php'));

});
