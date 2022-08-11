<?php

use App\Http\Controllers\ApiRest\HelperController;

use App\Http\Controllers\RoleController;
use App\Http\Controllers\AbconfigController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\TipoCriterioController;
use App\Http\Controllers\CriteriosController;
use App\Http\Controllers\CarreraController;
use App\Http\Controllers\CicloController;
use App\Http\Controllers\CursosController;
use App\Http\Controllers\CurriculasGruposController;
use App\Http\Controllers\PosteoController;
use App\Http\Controllers\PreguntaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\Encuestas_respuestaController;
use App\Http\Controllers\Post_electivoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\Usuario_vigenciaController;
use App\Http\Controllers\CompatibleController;
use App\Http\Controllers\MallasController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\GestorController;
use App\Http\Controllers\MasivoController;
use App\Http\Controllers\ErroresMasivoController;
use App\Http\Controllers\IncidenciaController;
use App\Http\Controllers\DuplicarController;
use App\Http\Controllers\PushNotificationsFirebaseController;
use App\Http\Controllers\AyudaAppController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportesSupervisoresController;
use App\Http\Controllers\MigrarAvanceController;


Route::get('dashboard_pbi', function () {
	return view('powerbi.index');
})->name('dashboard_pbi');
// DOCUMENTACIÓN DE APIS
Route::view('/documentation-api/{list_apis?}', 'documentation-api.index')->name('documentation-api.index');
// Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
// Route::post('register', 'Auth\RegisterController@register');


// ROLES

// Route::controller(RoleController::class)->group(function() {

// 	// Crear
// 	Route::post('roles/store', 'store')->name('roles.store');
// 	// ->middleware('permission:roles.create');
// 	// Listar
// 	Route::get('roles/index', 'index')->name('roles.index');
// 	// ->middleware('permission:roles.index');
// 	// Formulario de creacion
// 	Route::get('roles/create', 'create')->name('roles.create');
// 	// ->middleware('permission:roles.create');
// 	// Actualizar
// 	Route::put('roles/{role}', 'update')->name('roles.update');
// 	// ->middleware('permission:roles.edit');
// 	// Ver el detalle
// 	Route::get('roles/{role}', 'show')->name('roles.show');
// 	// ->middleware('permission:roles.show');
// 	// Eliminar
// 	Route::delete('roles/{role}', 'destroy')->name('roles.destroy');
// 	// ->middleware('permission:roles.destroy');
// 	// Formulario Edicion
// 	Route::get('roles/{role}/edit', 'edit')->name('roles.edit');
// 	// ->middleware('permission:roles.edit');

// });

Route::controller(AbconfigController::class)->group(function() {

	// AB CONFIG
	Route::get('abconfigs/{abconfig}/usuarios', 'usuarios')->name('abconfigs.usuarios');
	Route::get('abconfigs/{abconfig}/categorias', 'categorias')->name('abconfigs.categorias');
	//
	Route::post('abconfigs/store', 'store')->name('abconfigs.store');
	// ->middleware('permission:abconfigs.create');
	Route::get('abconfigs/index', 'index')->name('abconfigs.index');
	// ->middleware('permission:abconfigs.index');
	Route::get('abconfigs/create', 'create')->name('abconfigs.create');
	// ->middleware('permission:abconfigs.create');
	Route::put('abconfigs/{abconfig}', 'update')->name('abconfigs.update');
	// ->middleware('permission:abconfigs.edit');
	Route::get('abconfigs/{abconfig}', 'show')->name('abconfigs.show');
	// ->middleware('permission:abconfigs.show');
	Route::delete('abconfigs/{abconfig}', 'destroy')->name('abconfigs.destroy');
	// ->middleware('permission:abconfigs.destroy');
	Route::get('abconfigs/{abconfig}/edit', 'edit')->name('abconfigs.edit');
	// ->middleware('permission:abconfigs.edit');

});

Route::controller(CategoriaController::class)->group(function() {

	// Categorias
	Route::get('abconfigs/{abconfig}/categorias/{categoria}/cursos', 'cursos')->name('categorias.cursos');
	Route::post('abconfigs/{abconfig}/categorias/store', 'store')->name('categorias.store');
	// ->middleware('permission:categorias.create');
	Route::get('abconfigs/{abconfig}/categorias/create', 'create')->name('categorias.create');
	// ->middleware('permission:categorias.create');
	Route::put('abconfigs/{abconfig}/categorias/{categoria}', 'update')->name('categorias.update');
	// ->middleware('permission:categorias.edit');
	Route::delete('abconfigs/{abconfig}/categorias/{categoria}', 'destroy')->name('categorias.destroy');
	// ->middleware('permission:categorias.destroy');
	Route::get('abconfigs/{abconfig}/categorias/{categoria}/edit', 'edit')->name('categorias.edit');
	// ->middleware('permission:categorias.edit');

});

Route::controller(TipoCriterioController::class)->group(function() {

	//TIPO CRITERIOS
	Route::get('tipo_criterios/','index')->name('tipo_criterio.index');
	Route::get('tipo_criterios/{tipo_criterio}/edit','edit')->name('tipo_criterio.edit');
	Route::get('tipo_criterios/create','create')->name('tipo_criterio.create');
	Route::post('tipo_criterios/store','store')->name('tipo_criterio.store');
	Route::put('tipo_criterios/{tipoCriterio}/update','update')->name('tipo_criterio.update');
	Route::patch('tipo_criterios/{tipoCriterio}/cambiar-orden/{new}', 'changeOrder')->name('tipo_criterio.change-order');
	// ->middleware('permission:tipo_criterio.edit');
});

Route::controller(CriteriosController::class)->group(function() {

	// Criterios
	Route::get('tipo_criterios/criterios', 'index')->name('criterio.index');
	Route::get('criterios/create', 'create')->name('criterios.create');
	Route::get('criterios/getInitialData', 'getInitialData');
	Route::post('criterios/insert_or_edit', 'insert_or_edit');
	Route::get('criterios/buscar/{criterio_nombre}', 'buscar_criterio');
});

Route::controller(CarreraController::class)->group(function() {
	// Carreras
	Route::post('carreras', 'carreras_x_modulo');
	Route::get('abconfigs/{abconfig}/carreras/{carrera}/ciclos', 'ciclos')->name('carreras.ciclos');
	Route::post('abconfigs/{abconfig}/carreras/store', 'store')->name('carreras.store');
	// ->middleware('permission:carreras.create');
	Route::get('carreras/index', 'index')->name('carreras.index');
	// ->middleware('permission:carreras.index');
	Route::get('abconfigs/{abconfig}/carreras/create', 'create')->name('carreras.create');
	// ->middleware('permission:carreras.create');
	Route::put('abconfigs/{abconfig}/carreras/{carrera}', 'update')->name('carreras.update');
	// ->middleware('permission:carreras.edit');
	Route::delete('abconfigs/{abconfig}/carreras/{carrera}', 'destroy')->name('carreras.destroy');
	// ->middleware('permission:carreras.destroy');
	Route::get('abconfigs/{abconfig}/carreras/{carrera}/edit', 'edit')->name('carreras.edit');
	// ->middleware('permission:carreras.edit');
});

Route::controller(CicloController::class)->group(function() {

	// Ciclos
	Route::post('ciclos', 'ciclos_x_carrera');
	Route::get('carreras/{carrera}/ciclos/{ciclo}/cursos', 'temas')->name('ciclos.cursos');
	Route::post('carreras/{carrera}/ciclos/store', 'store')->name('ciclos.store');
	// ->middleware('permission:ciclos.create');
	Route::get('carreras/{carrera}/ciclos/create', 'create')->name('ciclos.create');
	// ->middleware('permission:ciclos.create');
	Route::put('carreras/{carrera}/ciclos/{ciclo}', 'update')->name('ciclos.update');
	// ->middleware('permission:ciclos.edit');
	Route::delete('carreras/{carrera}/ciclos/{ciclo}', 'destroy')->name('ciclos.destroy');
	// ->middleware('permission:ciclos.destroy');
	Route::get('carreras/{carrera}/ciclos/{ciclo}/edit', 'edit')->name('ciclos.edit');
	// ->middleware('permission:ciclos.edit');
});

Route::controller(CursosController::class)->group(function() {

	// Cursos
	Route::get('categorias/{categoria}/cursos/{curso}/temas', 'temas')->name('cursos.temas');
	Route::post('categorias/{categoria}/cursos/store', 'store')->name('cursos.store');
	// ->middleware('permission:cursos.create');
	Route::get('categorias/{categoria}/cursos/create', 'create')->name('cursos.create');
	// ->middleware('permission:cursos.create');
	Route::put('categorias/{categoria}/cursos/{curso}', 'update')->name('cursos.update');
	// ->middleware('permission:cursos.edit');
	Route::delete('categorias/{categoria}/cursos/{curso}', 'destroy')->name('cursos.destroy');
	// ->middleware('permission:cursos.destroy');
	Route::get('categorias/{categoria}/cursos/{curso}/edit', 'edit')->name('cursos.edit');
	// ->middleware('permission:cursos.edit');
	Route::post('categorias/get_requisitos', 'get_requisitos');

	// CURSO ENCUESTA
	Route::get('cursos/{curso}/curso_encuesta/create', 'create_CE')->name('curso_encuesta.create');
	// ->middleware('permission:cursos.create');
	Route::post('cursos/{curso}/curso_encuesta/store', 'store_CE')->name('curso_encuesta.store');
	// ->middleware('permission:cursos.create');
	Route::put('cursos/{curso}/curso_encuesta/{ce}', 'update_CE')->name('curso_encuesta.update');
	// ->middleware('permission:cursos.edit');
	Route::delete('cursos/{curso}/curso_encuesta/{ce}', 'destroy_CE')->name('curso_encuesta.destroy');
	// ->middleware('permission:cursos.destroy');
	Route::get('cursos/{curso}/curso_encuesta/{ce}/edit', 'edit_CE')->name('curso_encuesta.edit');
	// ->middleware('permission:cursos.edit');

});

Route::controller(CurriculasGruposController::class)->group(function() {

	// CURRICULA 3 COMPONENTES
	Route::get('curriculas_grupos', 'index')->name('curriculas_grupos');
	Route::get('getCurriculaGrupos', 'getCurriculaGrupos');
	Route::get('getCurriculaXCurso/{curso_id}', 'getCurriculaXCurso');
	Route::get('getCarreras', 'getCarreras');
	Route::get('getGrupos', 'getGrupos');
	Route::post('guardarCurricula', 'guardarCurricula');
	Route::get('getCurriculaxCurso/{cursoid}/{carrera_id}', 'getCurriculaxCurso');
	Route::get('getGruposXCurricula/{curricula_id}', 'getGruposXCurricula');
	// SELECCIONAR TODOS LOS GRUPOS DEL CAMPO grupo en USUARIOS Y AGREGARLOS A LA TABLA CRITERIOS
	Route::get('llenarTablaGrupos', 'llenarTablaGrupos');

	Route::get('temporal', 'temporal');

});

Route::controller(PosteoController::class)->group(function() {

	// POSTEOS
	Route::get('cursos/{curso}/posteos/{posteo}/preguntas', 'preguntas')->name('posteos.preguntas');
	// Route::get('cursos/{curso}/posteos/{posteo}/encuesta', 'encuesta')->name('posteos.encuesta');

	Route::post('cursos/{curso}/posteos/store', 'store')->name('posteos.store');
	// ->middleware('permission:posteos.create');
	Route::get('cursos/{curso}/posteos/create', 'create')->name('posteos.create');
	// ->middleware('permission:posteos.create');
	Route::put('cursos/{curso}/posteos/{posteo}', 'update')->name('posteos.update');
	// ->middleware('permission:posteos.edit');
	Route::delete('cursos/{curso}/posteos/{posteo}', 'destroy')->name('posteos.destroy');
	// ->middleware('permission:posteos.destroy');
	Route::get('cursos/{curso}/posteos/{posteo}/edit', 'edit')->name('posteos.edit');
	// ->middleware('permission:posteos.edit');

	Route::get('posteos/{posteo}/del_attached_video', 'del_attached_video')->name('posteos.del_attached_video');
	// ->middleware('permission:posteos.edit');
	Route::get('posteos/{posteo}/del_attached_archivo', 'del_attached_archivo')->name('posteos.del_attached_archivo');
	// ->middleware('permission:posteos.edit');

});


Route::controller(PreguntaController::class)->group(function() {

	// PREGUNTAS1
	Route::get('cursos/{curso}/posteos/{posteo}/examen/subir', 'subirExamen')->name('preguntas.examen.create');
	// ->middleware('permission:preguntas.create');
	Route::post('cursos/{curso}/posteos/{posteo}/examen/store', 'guardarExamen')->name('preguntas.examen.store');
	// ->middleware('permission:preguntas.create');

	Route::post('posteos/{posteo}/preguntas/store', 'store')->name('preguntas.store');
	// ->middleware('permission:preguntas.create');
	Route::get('posteos/{posteo}/preguntas/create', 'create')->name('preguntas.create');
	// ->middleware('permission:preguntas.create');
	Route::put('posteos/{posteo}/preguntas/{pregunta}', 'update')->name('preguntas.update');
	// ->middleware('permission:preguntas.edit');
	Route::delete('posteos/{posteo}/preguntas/{pregunta}', 'destroy')->name('preguntas.destroy');
	// ->middleware('permission:preguntas.destroy');
	Route::get('posteos/{posteo}/preguntas/{pregunta}/edit', 'edit')->name('preguntas.edit');
	// ->middleware('permission:preguntas.edit');


	Route::get('evaluaciones/preguntas/getInitalData/{pregunta_id}', 'getInitalData');
	Route::post('evaluaciones/preguntas/createOrUpdate', 'createOrUpdate');
});

Route::controller(UserController::class)->group(function() {

	// Users (administradores)
	Route::post('users/store', 'store')->name('users.store');
	// ->middleware('permission:users.create');
	Route::get('users/index', 'index')->name('users.index');
	// ->middleware('permission:users.index');
	Route::get('users/create', 'create')->name('users.create');
	// ->middleware('permission:users.create');
	Route::put('users/{user}', 'update')->name('users.update');
	// ->middleware('permission:users.edit');
	Route::delete('users/{user}', 'destroy')->name('users.destroy');
	// ->middleware('permission:users.destroy');
	Route::get('users/{user}/edit', 'edit')->name('users.edit');
	// ->middleware('permission:users.edit');
});


// ANUNCIOS
// Route::post('anuncios/store', 'AnuncioController@store')->name('anuncios.store');
// // ->middleware('permission:anuncios.create');
// Route::get('anuncios/index', 'AnuncioController@index')->name('anuncios.index');
// // ->middleware('permission:anuncios.index');
// Route::get('anuncios/create', 'AnuncioController@create')->name('anuncios.create');
// // ->middleware('permission:anuncios.create');
// Route::put('anuncios/{anuncio}', 'AnuncioController@update')->name('anuncios.update');
// // ->middleware('permission:anuncios.edit');
// Route::delete('anuncios/{anuncio}', 'AnuncioController@destroy')->name('anuncios.destroy');
// // ->middleware('permission:anuncios.destroy');
// Route::get('anuncios/{anuncio}/edit', 'AnuncioController@edit')->name('anuncios.edit');
// // ->middleware('permission:anuncios.edit');

// Route::get('anuncios/{anuncio}/del_attached_video', 'AnuncioController@del_attached_video')->name('anuncios.del_attached_video');
// // ->middleware('permission:anuncios.edit');
// Route::get('anuncios/{anuncio}/del_attached_archivo', 'AnuncioController@del_attached_archivo')->name('anuncios.del_attached_archivo');
// // ->middleware('permission:anuncios.edit');


// pregunta_frecuentes frecuentes
// Route::post('pregunta_frecuentes/store', 'Pregunta_frecuenteController@store')->name('pregunta_frecuentes.store');
// // ->middleware('permission:pregunta_frecuentes.create');
// Route::get('pregunta_frecuentes/index', 'Pregunta_frecuenteController@index')->name('pregunta_frecuentes.index');
// // ->middleware('permission:pregunta_frecuentes.index');
// Route::get('pregunta_frecuentes/create', 'Pregunta_frecuenteController@create')->name('pregunta_frecuentes.create');
// // ->middleware('permission:pregunta_frecuentes.create');
// Route::put('pregunta_frecuentes/{pregunta_frecuente}', 'Pregunta_frecuenteController@update')->name('pregunta_frecuentes.update');
// // ->middleware('permission:pregunta_frecuentes.edit');
// Route::get('pregunta_frecuentes/{pregunta_frecuente}', 'Pregunta_frecuenteController@show')->name('pregunta_frecuentes.show');
// // ->middleware('permission:pregunta_frecuentes.show');
// Route::delete('pregunta_frecuentes/{pregunta_frecuente}', 'Pregunta_frecuenteController@destroy')->name('pregunta_frecuentes.destroy');
// // ->middleware('permission:pregunta_frecuentes.destroy');
// Route::get('pregunta_frecuentes/{pregunta_frecuente}/edit', 'Pregunta_frecuenteController@edit')->name('pregunta_frecuentes.edit');
// ->middleware('permission:pregunta_frecuentes.edit');

Route::controller(GrupoController::class)->group(function() {

	// grupos
	// adicional
	Route::get('grupos/{grupo}/usuarios', 'usuarios')->name('grupos.usuarios');
	Route::post('grupos/store', 'store')->name('grupos.store');
	// ->middleware('permission:grupos.create');
	Route::get('grupos/index', 'index')->name('grupos.index');
	// ->middleware('permission:grupos.index');
	Route::get('grupos/create', 'create')->name('grupos.create');
	// ->middleware('permission:grupos.create');
	Route::put('grupos/{grupo}', 'update')->name('grupos.update');
	// ->middleware('permission:grupos.edit');
	Route::delete('grupos/{grupo}', 'destroy')->name('grupos.destroy');
	// ->middleware('permission:grupos.destroy');
	Route::get('grupos/{grupo}/edit', 'edit')->name('grupos.edit');
	// ->middleware('permission:grupos.edit');

});

Route::controller(Encuestas_respuestaController::class)->group(function() {

	// ENCUENTAS RESPUESTAS
	Route::get('encuestas_respuestas/index', 'index')->name('encuestas_respuestas.index');
	// ->middleware('permission:encuestas_respuestas.index');
	Route::put('encuestas_respuestas/{encuestas_respuesta}', 'update')->name('encuestas_respuestas.update');
	// ->middleware('permission:encuestas_respuestas.edit');
	Route::delete('encuestas_respuestas/{encuestas_respuesta}', 'destroy')->name('encuestas_respuestas.destroy');
	// ->middleware('permission:encuestas_respuestas.destroy');
	Route::get('encuestas_respuestas/{encuestas_respuesta}/edit', 'edit')->name('encuestas_respuestas.edit');
	// ->middleware('permission:encuestas_respuestas.edit');
});




Route::controller(Post_electivoController::class)->group(function() {

	// POST_ELECTIVOS
	Route::post('post_electivos/store', 'store')->name('post_electivos.store');
	// ->middleware('permission:post_electivos.create');
	Route::get('post_electivos/index', 'index')->name('post_electivos.index');
	// ->middleware('permission:post_electivos.index');
	Route::get('post_electivos/create', 'create')->name('post_electivos.create');
	// ->middleware('permission:post_electivos.create');
	Route::put('post_electivos/{post_electivo}', 'update')->name('post_electivos.update');
	// ->middleware('permission:post_electivos.edit');
	Route::delete('post_electivos/{post_electivo}', 'destroy')->name('post_electivos.destroy');
	// ->middleware('permission:post_electivos.destroy');
	Route::get('post_electivos/{post_electivo}/edit', 'edit')->name('post_electivos.edit');
	// ->middleware('permission:post_electivos.edit');
});

Route::controller(UsuarioController::class)->group(function() {

	// USUARIOS
	Route::post('usuarios/cambia_modulo_carga_ciclos', 'cambia_modulo_carga_ciclos');

	Route::post('usuarios/store', 'store')->name('usuarios.store');
	// ->middleware('permission:usuarios.create');
	Route::get('usuarios/index', 'index')->name('usuarios.index');
	// ->middleware('permission:usuarios.index');
	Route::get('usuarios/create', 'create')->name('usuarios.create');
	// ->middleware('permission:usuarios.create');
//	Route::put('usuarios/{usuario}', 'update')->name('usuarios.update');
	// ->middleware('permission:usuarios.edit');
	Route::delete('usuarios/{usuario}', 'destroy')->name('usuarios.destroy');
	// ->middleware('permission:usuarios.destroy');
	Route::patch('usuarios/{usuario}', 'status')->name('usuarios.status');
	// ->middleware('permission:usuarios.edit');
//	Route::get('usuarios/{usuario}/edit', 'edit')->name('usuarios.edit');
	// ->middleware('permission:usuarios.edit');

	// USUARIOS RESET MASIVO
	Route::prefix('/masivo/usuarios')->group(function () {
		Route::get('index_reinicios', 'index_reinicios')->name('usuarios.index_reinicios');
		// ->middleware('permission:usuarios.index_reinicios');
		Route::get('reinicios_data', 'reinicios_data');
		Route::get('buscarCursosxEscuela/{categoria_id}', 'buscarCursosxEscuela');
		Route::get('buscarTemasxCurso/{curso_id}', 'buscarTemasxCurso');
		Route::post('validarReinicio', 'validarReinicioIntentos');
		Route::post('reiniciarIntentosMasivos', 'reiniciarIntentosMasivos');
	});

	Route::get('usuarios/getInitialData/{usuario_id}', 'getInitialData');
	Route::get('usuarios/getCarrerasxModulo/{config_id}', 'getCarrerasxModulo');
	Route::get('usuarios/getCarrerasxGrupo/{grupo_id}/{config_id}', 'getCarrerasxGrupo');
	Route::get('usuarios/getCiclosxCarrera/{carrera_id}/{grupo_id}', 'getCiclosxCarrera');
	Route::get('usuarios/getCiclosxCarreraFilter/{carrera_id}', 'getCiclosxCarreraFilter');
	Route::get('usuarios/getDataCiclo/{ciclo_id}/{carrera_id}/{grupo_id}', 'getDataCiclo');
	Route::get('usuarios/getCiclo/{ciclo_id}/{carrera_id}/{grupo_id}', 'getCiclo');

	Route::get('usuarios/getCursosxUsuario/{usuario_id}', 'getCursosxUsuario');

	Route::post('usuarios/crear', 'crear');
});


// CONVALIDACIONES
Route::get('convalidaciones/index', [ConvalidacionesController::class, 'index'])->name('convalidaciones.index');

Route::controller(Usuario_vigenciaController::class)->group(function() {

	// USUAIRO_VIGENCIA
	Route::post('usuario_vigencias/store', 'store')->name('usuario_vigencias.store');
	// ->middleware('permission:usuario_vigencias.create');
	Route::get('usuario_vigencias/index', 'index')->name('usuario_vigencias.index');
	// ->middleware('permission:usuario_vigencias.index');
	Route::get('usuario_vigencias/create', 'create')->name('usuario_vigencias.create');
	// ->middleware('permission:usuario_vigencias.create');
	Route::put('usuario_vigencias/{usuario_vigencia}', 'update')->name('usuario_vigencias.update');
	// ->middleware('permission:usuario_vigencias.edit');
	Route::delete('usuario_vigencias/{usuario_vigencia}', 'destroy')->name('usuario_vigencias.destroy');
	// ->middleware('permission:usuario_vigencias.destroy');
	Route::get('usuario_vigencias/{usuario_vigencia}/edit', 'edit')->name('usuario_vigencias.edit');
	// ->middleware('permission:usuario_vigencias.edit');
});

Route::controller(CompatibleController::class)->group(function() {

	//COMPATIBLES
	Route::get('compatibles', function () {
		return view('compatibles.index');
	})->name('compatibles.index');
	Route::get('/get_cursos_compatibles','get_cursos_compatibles');
	Route::post('/get_coincidencias','get_coincidencias');
	Route::post('/guardar_compatibles','guardar_compatibles');
	Route::post('/compatibles_lista','compatibles_lista');
	Route::post('/search_tema','search_tema');
	Route::get('/compatible/reporte','reporte');
	Route::get('/migracion_compatibles_x_usuario/{carrera_id}', 'migracion_compatibles_x_usuario');

});

Route::controller(CompatibleController::class)->group(function() {
	// Permisos
	Route::post('permisos/store', 'store')->name('permisos.store');
	// ->middleware('permission:permisos.create');
	Route::get('permisos/index', 'index')->name('permisos.index');
	// ->middleware('permission:permisos.index');
	Route::get('permisos/create', 'create')->name('permisos.create');
	// ->middleware('permission:permisos.create');
	Route::put('permisos/{permiso}', 'update')->name('permisos.update');
	// ->middleware('permission:permisos.edit');
	Route::delete('permisos/{permiso}', 'destroy')->name('permisos.destroy');
	// ->middleware('permission:permisos.destroy');
	Route::get('permisos/{permiso}/edit', 'edit')->name('permisos.edit');
	// ->middleware('permission:permisos.edit');
});

Route::controller(MallasController::class)->group(function() {

	// MALLAS
	Route::post('mallas/store', 'store')->name('mallas.store');
	// ->middleware('permission:mallas.create');
	Route::get('mallas/index', 'index')->name('mallas.index');
	// ->middleware('permission:mallas.index');
	Route::get('mallas/create', 'create')->name('mallas.create');
	// ->middleware('permission:mallas.create');
	Route::put('mallas/{malla}', 'update')->name('mallas.update');
	// ->middleware('permission:mallas.edit');
	Route::delete('mallas/{malla}', 'destroy')->name('mallas.destroy');
	// ->middleware('permission:mallas.destroy');
	Route::get('mallas/{malla}/edit', 'edit')->name('mallas.edit');
	// ->middleware('permission:mallas.edit');
});


Route::controller(MediaController::class)->group(function() {

	// MEDIA
	Route::post('media/fileupload', 'fileupload')->name('media.fileupload');
	Route::get('media/eliminar/{id}', 'eliminar')->name('media.eliminar');
	Route::get('media/index', 'index')->name('media.index');
	Route::get('media/create', 'create')->name('media.create');
	Route::get('media/modal_list_media_asigna', 'modal_list_media_asigna')->name('media.modal_list_media_asigna');
	Route::get('media/search', 'search');
	Route::get('media/{media}/download', 'downloadExternalFile')->name('media.download');
});

Route::controller(HomeController::class)->group(function() {
	//Resumen Encuestas
	// Encuestas
	Route::get('resumen_encuesta/index', 'encuestas')->name('resumen_encuesta.index');
	// ->middleware('permission:resumen_encuesta.index');
	// Encuesta Grupos
	Route::post('resumen_encuesta/cambiar_encuesta_mod', 'cambiar_encuesta_mod');
	Route::post('resumen_encuesta/cambia_curso', 'cambia_curso');
	Route::post('resumen_encuesta/cambia_grupo', 'cambia_grupo');

	// Resumen Encuestas - Califica
	Route::get('ver/encuestaxgp/{enc}/{mod}/{curso}/{grupo}', 'verEncuentaxGrupoPosteo');
	Route::get('export/encuestaxgp/{enc}/{mod}/{curso}/{grupo}', 'exportarEncuentaxGrupoPosteo');
	Route::get('ver/encuestaxgel/{enc}/{mod}/{curso}/{grupo}', 'verEncuentaxGrupoEnc');
	Route::get('export/encuestaxgel/{enc}/{mod}/{curso}/{grupo}', 'exportarEncuentaxGrupoEnc');


	// Resumen Encuestas - texto
	Route::get('ver/enc_pos_text/{enc}/{mod}/{curso}/{grupo}', 'verEncPostText');
	Route::get('export/enc_pos_text/{enc}/{mod}/{curso}/{grupo}', 'exportarEncPostText');
	Route::get('ver/enc_lib_text/{enc}/{mod}/{curso}/{grupo}', 'verEncLibText');
	Route::get('export/enc_lib_text/{enc}/{mod}/{curso}/{grupo}', 'exportarEncLibText');


	Route::post('/upload-image/{type}', 'uploadImage')->name('CompatibleController.image');
});





// AULAS VIRTUALES
//	Route::get('aulas_virtuales', 'AulasVirtualesController@index')->name('aulas_virtuales');

// COMPATIBLES
Route::get('tools/compatibles', [MigracionController::class, 'compatibles'])->name('tools.compatibles');

Route::controller(GestorController::class)->group(function() {

	// Generar password
	Route::get('genpass', 'generarPass')->name('gen.pass');

	// Generar vigencias
	Route::get('genuv', 'generarVigencia')->name('gen.pass');

});

// AUDITORIA //

// Route::prefix('/auditoria')->group(function () {
//     Route::get('/', function(){
//         return view('auditoria.auditoria_index');
//     })->name('auditoria.index');
//     Route::get('/get-selects', 'AuditController@getSelects');
//     Route::get('/search', 'AuditController@search');

//     Route::get('/{class}', 'AuditController@index')->name('auditoria.index2');
//     Route::get('/show/{class}/{id}', 'AuditController@show')->name('auditoria.show');
//     Route::get('/user/{class}/{id}', 'AuditController@showUser')->name('auditoria.user');

// });

// Route::prefix('/log')->group(function () {
//     Route::get('/', 'AuditController@getAll');
// //        Route::post('/export', 'AuditController@export');
//     Route::get('/export', 'AuditController@export');
//     Route::get('/show/{id}', 'AuditController@exportShow');
//     Route::get('/dependencia/{class}/{id}/{date}', 'AuditController@exportDependencia');
// });





// AUDITORIA //


// PROCESOS MASIVOS //
Route::controller(MasivoController::class)->group(function() {

	Route::get('masivo/index', 'index')->name('masivo.index');
	Route::post('masivo/migrar_usuarios', 'migrar_usuarios')->name('masivo.migrar_usuarios');
	Route::post('masivo/actualizar_ciclo', 'actualizar_ciclo')->name('masivo.actualizar_ciclo');
	Route::post('masivo/cesar_usuarios', 'cesar_usuarios')->name('masivo.cesar_usuarios');
	Route::post('masivo/migrar_avance_x_curso', 'migrar_avance_x_curso')->name('masivo.migrar_avance_x_curso');
	Route::post('masivo/recuperar_data_cesados', 'recuperar_data_cesados')->name('masivo.recuperar_data_cesados');
	Route::get('masivo/migrar_data_cesados_a_historial', 'migrar_data_cesados_a_historial')->name('masivo.migrar_data_cesados_a_historial');
	Route::get('masivo/depurar_tablas', 'depurar_tablas')->name('masivo.depurar_tablas');
	Route::post('masivo/migrar_farma_historial', 'migrar_farma_historial')->name('masivo.migrar_farma_historial');
	Route::post('masivo/subir_cursos', 'subir_cursos');

	Route::post('masivo/subir_usuarios', 'subir_usuarios')->name('masivo.subir_usuarios');

	Route::post('/masivo/restaurar_bd2019', 'restaurar_bd2019')->name('masivo.restaurar_bd2019');

});

Route::controller(ErroresMasivoController::class)->group(function() {

	//ERRORES
	Route::get('/masivo/reporte_errores/{tipo}','reporte_errores');
	Route::get('/masivo/errores/get_errores/{tipo}','get_errores');
	Route::post('/masivo/errores/get_change','get_change');
	Route::post('/masivo/errores/guardar_data','guardar_data');

	Route::get('masivo/errores', 'obtener_errores');
	Route::post('masivo/comprobar_data', 'comprobar_data');
	Route::delete('/masivo/eliminar_error/{id}', 'eliminar_error');
	Route::post('/masivo/arreglar_errores', 'arreglar_errores');
	Route::post('/masivo/eliminar_errores', 'eliminar_errores');

	Route::post('/masivo/fix_err_cic_carr', 'fix_err_cic_carr');

	Route::get('masivo/err_cesados', 'obtener_err_cesados');
	Route::post('/masivo/fix_err_cesados', 'fix_err_cesados');

	Route::get('masivo/err_rec_cesados', 'obtener_err_rec_cesados');
	Route::post('/masivo/fix_err_rec_cesados', 'fix_err_rec_cesados');
});

Route::controller(IncidenciaController::class)->group(function() {

	//INCIDENCIAS
	Route::get('incidencias/index', 'index')->name('incidencias.index');
	// ->middleware('permission:incidencias.index');
	Route::post('incidencias/ejecutar_comando', 'ejecutar_comando')->name('incidencias.ejecutar');
	Route::delete('incidencias/destroy/{id}', 'destroy')->name('incidencias.destroy');
});

Route::controller(DuplicarController::class)->group(function() {

	// DUPLICAR DATA //

	Route::get('/duplicar/get_data/{tipo}/{id}', 'get_data');
	Route::post('/duplicar/save_copy', 'save_copy');
});

// SOPORTE //

Route::controller(PushNotificationsFirebaseController::class)->group(function() {

	// NOTIFICACIONES PUSH PERSONALIZADAS DESDE EL GESTOR //
	Route::get('notificaciones_push', 'index');
	// ->middleware('permission:notificaciones_push.index')->name('notificaciones_push.index');
	Route::get('notificaciones_push/getData', 'getData');
	Route::post('notificaciones_push/enviarNotificacionCustom', 'enviarNotificacionCustom');
});

Route::controller(HelperController::class)->group(function() {

	// NOTIFICACIONES PUSH PERSONALIZADAS DESDE EL GESTOR //
	//	Funciones para migrar boticas
	Route::get('actualizar_curricula_x_criterio', 'actualizarCurriculaxCriterio');
	Route::get('actualizar_boticas_x_criterio', 'actualizarBoticasxCriterio');
	Route::get('actualizar_usuarios_x_criterio', 'actualizarUsuariosxCriterio');
	Route::get('migracion_boticas', 'migracionBoticas');
	Route::get('actualizar_matricula_x_criterio', 'actualizarMatriculasxCriterio');
});

//	Funciones para migrar boticas

Route::prefix('/ayuda_app')->controller(AyudaAppController::class)->group(function () {

	// Route::get('ayuda_app/getData', 'getData');
	// Route::post('ayuda_app/saveData', 'saveData');
	Route::get('getData', 'getData');
	Route::post('saveData', 'saveData');
});

// HOME - Dashboard - Gráficas
Route::prefix('/dashboard')->controller(DashboardController::class)->group(function () {

    Route::get('/get-data-for-top-boticas', 'getDataForGraphicTopBoticas');
    Route::get('/get-data-for-visitas-por-fecha', 'getDataForGraphicVisitasPorfecha');
    Route::get('/get-data-for-evaluaciones-por-fecha', 'getDataForGraphicEvaluacionesPorfecha');
    Route::get('/clear-cache', 'clearCache');

});

// Reportes para Supervisores
Route::prefix('/reportes-supervisores')->controller(ReportesSupervisoresController::class)->group(function () {

    Route::view('/index', 'reportes_supervisores.index')->name('reportes_supervisores.index');
    // ->middleware('permission:reportes_supervisores.index');;
    Route::get('/search', 'search');
    Route::get('/get-list-selects', 'getListSelects');
    Route::get('/get-area-modulo/{modulo}/{tipo}', 'getCriterioxModulo');
    Route::post('/get-usuarios', 'getUsuarios');
    Route::post('/store-supervisor', 'storeSupervisor');
    Route::post('/delete-supervisor', 'deleteSupervisor');
	Route::post('/import', 'importSupervisores');
});

// MIGRAR AVANCE
Route::prefix('/migrar_avance')->controller(MigrarAvanceController::class)->group(function () {
	Route::view('/', 'migracion.avance')->name('masivo.migrar_avance');
	Route::get('/getData', 'getData');
	Route::get('/list_categorias/{config_id}', 'listCategorias');
	Route::get('/list_cursos/{categoriad_id}', 'listCursos');
	Route::get('/list_temas/{curso_id}', 'listTemas');
	Route::get('/get_duplicates_data/{tipo}/{id}/{categoria_id}', 'getDuplicatesData');
	Route::post('/migrar_temas', 'migrarTemas');
});
