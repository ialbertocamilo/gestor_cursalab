<?php

namespace App\Http\Controllers;

use App\Models\Massive\UserMassiveUpdate;
use DB;
use App\Models\SectionUpload;
use App\Models\User;
use App\Models\Ciclo;
use App\Models\Curso;
use App\Models\Error;
use App\Models\Posteo;
use App\Models\Prueba;
use App\Models\Visita;
use App\Models\Carrera;
use App\Models\Diploma;
use App\Models\Usuario;
use App\Models\Abconfig;
use App\Models\Criterio;
use App\Models\Reinicio;
use App\Models\Curricula;
use App\Models\Matricula;
use App\Models\Ev_abierta;
use App\Models\PosteoCompas;
use App\Models\Usuario_rest;
use Illuminate\Http\Request;
use App\Models\ReporteBD2019;
use App\Models\CambiosMasivos;
use App\Models\Usuario_upload;
use App\Imports\UsuariosImport;
use App\Models\Resumen_general;
use App\Models\Resumen_x_curso;
use App\Models\Usuario_version;
use App\Models\UsuariosActivos;
use App\Models\UsuariosMasivos;
use App\Imports\FirstPageImport;

use App\Imports\CursosSubirImport;
use App\Models\Curricula_criterio;
use App\Models\Matricula_criterio;
use App\Models\UsuariosDesactivos;

use App\Models\Encuestas_respuesta;
use App\Models\Massive\UserMassive;

use App\Exports\ExportReporteBD2019;
use App\Exports\UserMassiveTemplate;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\MigracionPerfilImport;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\UsuarioController;
use App\Imports\UsuariosFarmaHistorialImport;
use App\Models\Massive\ChangeStateUserMassive;
use App\Http\Controllers\ApiRest\HelperController;
use App\Http\Controllers\ApiRest\RestAvanceController;

class MasivoController extends Controller
{

    // public function index(Request $request){
    //     $data = [];
    //     $q_err_usu = DB::table('err_masivos')->where('type_masivo','usuarios')->count();
    //     $q_err_cambio = DB::table('err_masivos')->where('type_masivo','ciclos_carreras')->count();
    //     $q_err_desct_usu = DB::table('err_masivos')->where('type_masivo','cesados')->count();
    //     $q_err_activ_usu = DB::table('err_masivos')->where('type_masivo','activos')->count();
    //     $q_err_cur_tem_eva = DB::table('err_masivos')->where(function ($q) {
    //         $q->where('type_masivo', 'cursos')->orWhere('type_masivo', 'temas')->orWhere('type_masivo', 'evaluaciones');
    //     })->count();
    //     $info_error = json_encode(compact(
    //         'q_err_usu',
    //         'q_err_cambio',
    //         'q_err_activ_usu',
    //         'q_err_desct_usu',
    //         'q_err_cur_tem_eva'
    //     ));
    //     return view('masivo.index')->with(compact('data','info_error'));
    // }
    public function downloadTemplateUser()
    {
        ob_end_clean();
        ob_start();
        return Excel::download(new UserMassiveTemplate, 'plantilla_usuarios.xlsx');
    }

    public function createUpdateUsers(Request $request)
    {
        // try {
            //code...
            $validator = $this->validateFile($request);
            if (!$validator) {
                return response()->json(['message' => 'Se encontró un error, por favor vuelva a cargar el archivo.'], 500);
            }
            $current_workspace = get_current_workspace();

            $data = [
                'number_socket' => $request->get('number_socket') ?? null
            ];
            $import = new UserMassive($data);
            Excel::import(new FirstPageImport($import), $request->file('file'));

            $headers = $import->excelHeaders;

            // === guardar archivo log ===
            $codes = [  'code_section' => 'massive-upload',
                        'code_type' => 'upload' ];
            SectionUpload::storeRequestLog($request, $codes);
            // === guardar archivo log ===

            if ($import->error_message):

                return $this->success([
                    'workspace_limit' => number_format($current_workspace->getLimitAllowedUsers()),
                    'users_to_activate' => number_format($import->rows_to_activate)
                ], $import->error_message, 422);

            else:
                return $this->success([
                    'message' => "Usuarios creados correctamente.",
                    'headers' => $headers,
                    'datos_procesados' => $import->processed_users,
                    'errores' => $import->errors
                ]);
            endif;
        // } catch (\Throwable $exception) {
        //     Error::storeAndNotificateException($exception, $request);
        //     $errorMessage = $exception->getMessage();
        //     $message = strpos($errorMessage, 'cantidad máxima') !== false ? $errorMessage : 'Ha ocurrido un problema. Contáctate con el equipo de soporte.';
        //     return response()->json([
        //         'message'=>$message
        //     ],500);
        // }
    }

    public function updateUsers(Request $request)
    {

        $validator = $this->validateFile($request);
        if (!$validator) {
            return response()->json(['message' => 'Se encontró un error, por favor vuelva a cargar el archivo.'], 500);
        }
        $current_workspace = get_current_workspace();

        $data = [
            'number_socket' => $request->get('number_socket') ?? null
        ];
        $import = new UserMassiveUpdate($data, true);
        Excel::import(new FirstPageImport($import), $request->file('file'));

        $headers = $import->excelHeaders;

        // === guardar archivo log ===
        $codes = [  'code_section' => 'massive-upload',
            'code_type' => 'upload' ];
        SectionUpload::storeRequestLog($request, $codes);
        // === guardar archivo log ===

        if ($import->error_message) {

            return $this->success([
                'workspace_limit' => number_format($current_workspace->getLimitAllowedUsers()),
                'users_to_activate' => number_format($import->rows_to_activate)
            ], $import->error_message, 422);

        } else {
            return $this->success([
                'message' => "Usuarios actualizados correctamente.",
                'headers' => $headers,
                'datos_procesados' => $import->processed_users,
                'errores' => $import->errors
            ]);
        }
    }

    public function activeUsers(Request $request)
    {
        try {
            //code...
            $validator = $this->validateFile($request);
            if (!$validator) {
                return response()->json(['message' => 'Se encontró un error, porfavor vuelva a cargar el archivo.']);
            }
            $current_workspace = get_current_workspace();

            $data = [
                'number_socket' => $request->get('number_socket') ?? null
            ];
            $import = new ChangeStateUserMassive($data);
            $import->identificator = 'document';
            $import->state_user_massive = 1;
            Excel::import(new FirstPageImport($import), $request->file('file'));

            // === guardar archivo log ===
            $codes = [  'code_section' => 'massive-upload',
                        'code_type' => 'upload' ];
            SectionUpload::storeRequestLog($request, $codes);
            // === guardar archivo log ===

            if ($import->error_message):

                return $this->success([
                    'workspace_limit' => number_format($current_workspace->getLimitAllowedUsers()),
                    'users_to_activate' => number_format($import->rows_to_activate)
                ], $import->error_message, 422);

            else:
                return $this->success([
                    'message' => "Usuarios activados correctamente.",
                    'headers' => $import->getStaticHeader(true, true),
                    'datos_procesados' => $import->q_change_status,
                    'errores' => $import->errors
                ]);
            endif;
        } catch (\Throwable $exception) {
            Error::storeAndNotificateException($exception, $request);
            $errorMessage = $exception->getMessage();
            $message = strpos($errorMessage, 'cantidad máxima') !== false ? $errorMessage : 'Ha ocurrido un problema. Contáctate con el equipo de soporte.';
            return response()->json([
                'message'=>$message
            ],500);
        }
    }

    public function inactiveUsers(Request $request)
    {
        try {
            //code...
            $validator = $this->validateFile($request);
            if (!$validator) {
                return response()->json(['message' => 'Se encontró un error, porfavor vuelva a cargar el archivo.']);
            }
            $data = [
                'number_socket' => $request->get('number_socket') ?? null
            ];
            $import = new ChangeStateUserMassive($data);
            $import->identificator = 'document';
            $import->state_user_massive = 0;
            Excel::import(new FirstPageImport($import), $request->file('file'));

            // === guardar archivo log ===
            $codes = [  'code_section' => 'massive-upload',
                        'code_type' => 'upload' ];
            SectionUpload::storeRequestLog($request, $codes);
            // === guardar archivo log ===

            return $this->success([
                'message' => 'Usuarios inactivados correctamente.',
                'headers' => $import->getStaticHeader(false, true),
                'datos_procesados' => $import->q_change_status,
                'errores' => $import->errors
            ]);
        } catch (\Throwable $exception) {
            Error::storeAndNotificateException($exception, $request);
            $errorMessage = $exception->getMessage();
            $message = strpos($errorMessage, 'cantidad máxima') !== false ? $errorMessage : 'Ha ocurrido un problema. Contáctate con el equipo de soporte.';
            return response()->json([
                'message'=>$message
            ],500);
        }
    }

    private function validateFile($request)
    {
        $input = $request->all();
        $rules = array(
            'file' => 'required',
        );
        $validator = \Validator::make($input, $rules);
        if ($validator->fails()) {
            return false;
        }
        if (!$request->hasFile("file")) {
            return false;
        }
        return true;
    }
    // public function index(Request $request){
    //     $data = [];
    //     $err_usu = DB::table('err_masivos')->where('type_masivo','usuarios')->count();
    //     $err_cambio = DB::table('err_masivos')->where('type_masivo','ciclos_carreras')->count();
    //     $err_activ_usu = DB::table('err_masivos')->where('type_masivo','cesados')->count();
    //     $err_desct_usu = DB::table('err_masivos')->where('type_masivo','recuperar_cesados')->count();
    //     $err_cur_tem_eva = DB::table('err_masivos')->where(function ($q) {
    //         $q->where('type_masivo', 'cursos')->orWhere('type_masivo', 'temas')->orWhere('type_masivo', 'preguntas');
    //     })->count();
    //     $info_error = compact([
    //         'err_usu',
    //         'err_cambio',
    //         'err_activ_usu',
    //         'err_desct_usu',
    //         'err_cur_tem_eva'
    //     ]);
    //     return view('masivo.index')->with(compact('data','info_error'));
    // }

    public function getDefaultDBName()
    {
        $db = \DB::connection()->getDatabaseName();
        return $db;
    }

    public function getHistorialDBName()
    {
        $db = \DB::connection('mysql2')->getDatabaseName();
        return $db;
    }

    // ACTUALIZAR CICLO DE USUARIOS QUE YA EXISTEN EN BD
    public function actualizar_ciclo(Request $request)
    {
        $input = $request->all();
        $rules = array(
            'migra_usuario' => 'required',
        );
        $validator = \Validator::make($input, $rules);
        if ($validator->fails()) {
            return response()->json(compact('validator'));
        }
        if ($request->hasFile("migra_usuario")) {
            $import = new CambiosMasivos();
            // $import->onlySheets(CambiosMasivosImport::SHEET_TO_IMPORT);
            Excel::import($import, $request->file('migra_usuario'));
            $info = '';
            $info = $info . $import->get_q_cambio_ciclo() . ' usuarios(s) con ciclo actualizado.<br>';
            $info = $info . $import->get_q_cambio_carrera() . ' usuarios(s) con carreras actualizado(s).<br>';
            $info = $info . $import->get_q_cambio_modulo() . ' usuarios(s) con módulo actualizado(s).<br>';
            $info = $info . $import->get_q_actualizaciones() . ' usuarios(s) con datos actualizado(s).<br>';
            $info = $info . $import->get_q_errors() . ' error(es) detectado(s)';
            $error = ($import->get_q_errors() > 0) ? true : false;
            $q_error = $import->get_q_errors();
            return response()->json(compact('info', 'error', 'q_error'));
        }
        return response()->json(compact('error'));
    }

    // SUBIR USUARIOS DE FORMA MASIVA CON ARCHIVO EXCEL IMPORTADO
    public function migrar_usuarios(Request $request)
    {
        $input = $request->all();
        $rules = array(
            'dnis_file' => 'required',
        );
        $validator = \Validator::make($input, $rules);
        if ($validator->fails()) {
            return \Redirect::back()->withInput()->withErrors($validator);
        }
        if ($request->hasFile("dnis_file")) {
            // PROCESAR CADA DNI
            $import = new MigracionPerfilImport;
            Excel::import($import, $request->file('dnis_file'));
            foreach ($import->data as $fila) {
                $dni = $fila[0];
                $modulo = $fila[1];
                $carrera = $fila[7];
                $ciclo = $fila[8];
            }
            return \Redirect::back()->with('info', 'Migración exitosa');
        }

        return \Redirect::back()->with('error', 'Error.');
    }

    // CESAR USUARIOS DE FORMA MASIVA CON ARCHIVO EXCEL IMPORTADO
    public function cesar_usuarios(Request $request)
    {
        $input = $request->all();
        $rules = array(
            'file_dsc_usu' => 'required',
        );
        $validator = \Validator::make($input, $rules);
        if ($validator->fails()) {
            return response()->json(['info' => 'Archivo requerido.']);
        }
        if ($request->hasFile("file_dsc_usu")) {
            $model = new UsuariosDesactivos();
            Excel::import($model, $request->file('file_dsc_usu'));
            $info = '';
            $info = $info . $model->get_q_cesados() . ' usuarios(s) desactivado(s).<br>';
            $info = $info . $model->get_q_errors() . ' error(es) detectado(s)';
            $error = ($model->get_q_errors() > 0) ? true : false;
            $q_error = $model->get_q_errors();
            return response()->json(compact('info', 'error', 'q_error'));
        }
        return response()->json(['info' => 'Error']);
    }

    public function migrar_farma_historial(Request $request)
    {
        $input = $request->all();
        $rules = array(
            'file' => 'required',
        );
        $validator = \Validator::make($input, $rules);
        if ($validator->fails()) {
            return \Redirect::back()->withInput()->withErrors($validator);
        }
        if ($request->hasFile("file")) {
            try {
                $import = new UsuariosFarmaHistorialImport();
                $import->onlySheets(UsuariosFarmaHistorialImport::SHEET_TO_IMPORT);
                Excel::import($import, $request->file('file'));
                $info = [
                    'info_cesados' => $import->sheet->get_q_cesados(),
                    'info_errors' => $import->sheet->get_q_errors(),
                ];
                return back()->with($info);
            } catch (\Exception $th) {
                //throw $th;
                $error_page = 'El nombre de la página debe ser: "Insertar  - Nuevos"';
                return back()->with(["error_page" => $error_page]);
            }
        }
        return \Redirect::back()->with('error', 'Error.');
    }
    // MIGRAR AVANCE DE UN CURSO A OTRO
    // Columnas: curso_origen_id, curso_destino_id
    public function migrar_avance_x_curso(Request $request)
    {
        $input = $request->all();
        $rules = array(
            'file' => 'required',
        );

        $validator = \Validator::make($input, $rules);
        if ($validator->fails()) {
            return \Redirect::back()->withInput()->withErrors($validator);
        }

        if ($request->hasFile("file")) {
            $import = new MigracionPerfilImport;
            Excel::import($import, $request->file('file'));

            foreach ($import->data as $fila) {

                $exl_curso_origen_id = $fila[0];
                $exl_tema_origen_id = $fila[1];
                $exl_curso_destino_id = $fila[2];
                $exl_tema_destino_id = $fila[3];

                $curso_destino = Curso::select('categoria_id')->where('id', $exl_curso_destino_id)->first();
                $categoria_id = ($curso_destino) ? $curso_destino->categoria_id : 0;

                // $usuarios_visitas = Visita::select('usuario_id')->where('post_id', $exl_tema_origen_id)->where('usuario_id', 7432)->get();
                $usuarios = Visita::select('usuario_id')->where('post_id', $exl_tema_origen_id)->get();

                if ($usuarios->count() == 0) {
                    $usuarios_pruebas = Prueba::select('usuario_id')->where('posteo_id', $exl_tema_origen_id)->get();
                    $usuarios = $usuarios_pruebas;
                }
                foreach ($usuarios as $uv) {
                    // ----> Se valida para todos que no exista el registro destino. Solo si no existe se procede a trasladar la información.
                    // Trasladar visitas
                    $existe_destino = DB::table('visitas')->where('usuario_id', $uv->usuario_id)->where('post_id', $exl_tema_destino_id)->first();
                    if (!$existe_destino) {
                        // Actualiza registro con el dato (curso o tema) destino
                        DB::table('visitas')->where('usuario_id', $uv->usuario_id)->where('post_id', $exl_tema_origen_id)->update(
                            [
                                'post_id' => $exl_tema_destino_id,
                                'curso_id' => $exl_curso_destino_id
                            ]
                        );
                    } else {
                        // Elimina el registro origen
                        DB::table('visitas')->where('usuario_id', $uv->usuario_id)->where('post_id', $exl_tema_origen_id)->delete();
                    }

                    // Trasladar pruebas
                    $existe_destino = DB::table('pruebas')->where('usuario_id', $uv->usuario_id)->where('posteo_id', $exl_tema_destino_id)->first();
                    if (!$existe_destino) {
                        // Actualiza registro con el dato (curso o tema) destino
                        DB::table('pruebas')->where('usuario_id', $uv->usuario_id)->where('posteo_id', $exl_tema_origen_id)->update(
                            [
                                'posteo_id' => $exl_tema_destino_id,
                                'curso_id' => $exl_curso_destino_id,
                                'categoria_id' => $categoria_id
                            ]
                        );

                    } else {
                        // Elimina el registro origen
                        DB::table('pruebas')->where('usuario_id', $uv->usuario_id)->where('posteo_id', $exl_tema_origen_id)->delete();
                    }

                    // Trasladar ev_abiertas
                    $existe_destino = DB::table('ev_abiertas')->where('usuario_id', $uv->usuario_id)->where('posteo_id', $exl_tema_destino_id)->first();
                    if (!$existe_destino) {
                        // Actualiza registro con el dato (curso o tema) destino
                        DB::table('ev_abiertas')->where('usuario_id', $uv->usuario_id)->where('posteo_id', $exl_tema_origen_id)->update(
                            [
                                'posteo_id' => $exl_tema_destino_id,
                                'curso_id' => $exl_curso_destino_id,
                                'categoria_id' => $categoria_id
                            ]
                        );
                    } else {
                        // Elimina el registro origen
                        DB::table('ev_abiertas')->where('usuario_id', $uv->usuario_id)->where('posteo_id', $exl_tema_origen_id)->delete();
                    }

                    // Trasladar encuestas
                    $existe_destino = DB::table('encuestas_respuestas')->where('usuario_id', $uv->usuario_id)->where('curso_id', $exl_curso_destino_id)->first();
                    if (!$existe_destino) {
                        // Actualiza registro con el dato (curso o tema) destino
                        DB::table('encuestas_respuestas')->where('usuario_id', $uv->usuario_id)->where('curso_id', $exl_curso_origen_id)->update(
                            ['curso_id' => $exl_curso_destino_id]
                        );
                    } else {
                        // Elimina el registro origen
                        DB::table('encuestas_respuestas')->where('usuario_id', $uv->usuario_id)->where('curso_id', $exl_curso_destino_id)->delete();
                    }

                    // Trasladar resumen_x_curso
                    $existe_destino = DB::table('resumen_x_curso')->where('usuario_id', $uv->usuario_id)->where('curso_id', $exl_curso_destino_id)->first();
                    if (!$existe_destino) {
                        // Actualiza registro con el dato (curso o tema) destino
                        DB::table('resumen_x_curso')->where('usuario_id', $uv->usuario_id)->where('curso_id', $exl_curso_origen_id)->update(
                            [
                                'curso_id' => $exl_curso_destino_id,
                                'categoria_id' => $categoria_id
                            ]
                        );
                    } else {
                        // Elimina el registro origen
                        DB::table('resumen_x_curso')->where('usuario_id', $uv->usuario_id)->where('curso_id', $exl_curso_destino_id)->delete();
                    }
                }
            }

            return \Redirect::back()
                ->with('info', 'El proceso se ha completado exitosamente.');

        } else {
            return \Redirect::back()->with('error', 'Error.');
        }
    }

    // -- Columnas: DNI
    public function recuperar_data_cesados(Request $request)
    {
        $input = $request->all();
        $rules = array(
            'file_act_usu' => 'required',
        );
        $validator = \Validator::make($input, $rules);
        if ($validator->fails()) {
            return response()->json(['info' => 'Archivo requerido.']);
        }
        if ($request->hasFile("file_act_usu")) {
            $model = new UsuariosActivos();
            Excel::import($model, $request->file('file_act_usu'));
            $info = '';
            $info = $info . $model->get_q_activos() . ' usuarios(s) activado(s).<br>';
            $info = $info . $model->get_q_errors() . ' error(es) detectado(s)';
            $error = ($model->get_q_errors() > 0) ? true : false;
            $q_error = $model->get_q_errors();
            return response()->json(compact('info', 'error', 'q_error'));
        }
        return response()->json(['info' => 'Error']);
    }

    /*************************************** SUBFUNCIONES (LLAMADAS EN FUNCIONES PRINCIPALES)  **********************************************/

    // Limpiar tablas de producción, que tienen data de usuarios cesados, cursos eliminados, etc.
    public function migrar_data_cesados_a_historial()
    {
        $db_name = $this->getDefaultDBName();
        $hi_db_name = $this->getHistorialDBName();

        echo "<h2> INICIA PROCESO DE TRASLADO:</h2>";

        // diplomas
        DB::statement("
            INSERT INTO $hi_db_name.hi_diplomas (usuario_id, curso_id, posteo_id, fecha_emision)
            SELECT usuario_id, curso_id, posteo_id, fecha_emision FROM $db_name.diplomas
            WHERE $db_name.diplomas.usuario_id NOT IN (SELECT id FROM $db_name.usuarios)
            ;");

        echo "<p> -> Diplomas insertadas en historial</p>";

        DB::statement("
            DELETE FROM diplomas
            WHERE usuario_id NOT IN (SELECT id FROM usuarios)
            ;");

        echo "<p> -> Diplomas depuradas de producción</p>";
        echo "<hr>";

        // encuestas_respuestas
        DB::statement("
            INSERT INTO $hi_db_name.hi_encuestas_respuestas (encuesta_id, curso_id, post_id, pregunta_id, usuario_id, respuestas, tipo_pregunta, created_at, updated_at)
            SELECT encuesta_id, curso_id, post_id, pregunta_id, usuario_id, respuestas, tipo_pregunta, created_at, updated_at FROM $db_name.encuestas_respuestas
            WHERE $db_name.encuestas_respuestas.usuario_id NOT IN (SELECT id FROM $db_name.usuarios)
            ;");

        echo "<p> -> Encuestas(respuestas) insertadas en historial</p>";

        DB::statement("
            DELETE FROM encuestas_respuestas
            WHERE usuario_id NOT IN (SELECT id FROM usuarios)
            ;");

        echo "<p> -> Encuestas(respuestas) depuradas de producción</p>";
        echo "<hr>";

        // evaluaciones abiertas
        DB::statement("
            INSERT INTO $hi_db_name.hi_ev_abiertas (categoria_id, curso_id, posteo_id, usuario_id, usu_rptas, fuente, created_at, updated_at)
            SELECT categoria_id, curso_id, posteo_id, usuario_id, usu_rptas, fuente, created_at, updated_at FROM $db_name.ev_abiertas
            WHERE $db_name.ev_abiertas.usuario_id NOT IN (SELECT id FROM $db_name.usuarios)
            ;");

        echo "<p> -> EV_abiertas insertadas en historial</p>";

        DB::statement("
            DELETE FROM ev_abiertas
            WHERE usuario_id NOT IN (SELECT id FROM usuarios)
            ;");

        echo "<p> -> Ev_abiertas depuradas de producción</p>";
        echo "<hr>";

        // matriculas
        DB::statement("
            INSERT INTO $hi_db_name.hi_matricula (usuario_id, carrera_id, ciclo_id, secuencia_ciclo, estado, created_at, updated_at)
            SELECT usuario_id, carrera_id, ciclo_id, secuencia_ciclo, estado, created_at, updated_at FROM $db_name.matricula
            WHERE $db_name.matricula.usuario_id NOT IN (SELECT id FROM $db_name.usuarios)
            ;");

        echo "<p> -> Matriculas insertadas en historial</p>";

        DB::statement("
            DELETE FROM matricula
            WHERE usuario_id NOT IN (SELECT id FROM usuarios)
            ;");

        echo "<p> -> Matriculas depuradas de producción</p>";
        echo "<hr>";

        // pruebas
        DB::statement("
            INSERT INTO $hi_db_name.hi_pruebas (categoria_id, curso_id, posteo_id, usuario_id, intentos, rptas_ok, rptas_fail, nota, resultado, usu_rptas, created_at, updated_at)
            SELECT categoria_id, curso_id, posteo_id, usuario_id, intentos, rptas_ok, rptas_fail, nota, resultado, usu_rptas, created_at, updated_at FROM $db_name.pruebas
            WHERE $db_name.pruebas.usuario_id NOT IN (SELECT id FROM $db_name.usuarios)
            ;");

        echo "<p> -> Pruebas insertadas en historial</p>";

        DB::statement("
            DELETE FROM pruebas
            WHERE usuario_id NOT IN (SELECT id FROM usuarios)
            ;");

        echo "<p> -> Pruebas depuradas de producción</p>";
        echo "<hr>";

        // reinicios
        DB::statement("
            INSERT INTO $hi_db_name.hi_reinicios (usuario_id, curso_id, posteo_id, admin_id, tipo, acumulado, created_at, updated_at)
            SELECT usuario_id, curso_id, posteo_id, admin_id, tipo, acumulado, created_at, updated_at FROM $db_name.reinicios
            WHERE $db_name.reinicios.usuario_id NOT IN (SELECT id FROM $db_name.usuarios)
            ;");

        echo "<p> -> Reinicios insertadas en historial</p>";

        DB::statement("
            DELETE FROM reinicios
            WHERE usuario_id NOT IN (SELECT id FROM usuarios)
            ;");

        echo "<p> -> Reinicios depuradas de producción</p>";
        echo "<hr>";

        // resumen_general
        DB::statement("
            INSERT INTO $hi_db_name.hi_resumen_general (usuario_id, cur_asignados, tot_completados, nota_prom, intentos, rank, porcentaje, created_at, updated_at )
            SELECT usuario_id, cur_asignados, tot_completados, nota_prom, intentos, rank, porcentaje, created_at, updated_at FROM $db_name.resumen_general
            WHERE $db_name.resumen_general.usuario_id NOT IN (SELECT id FROM $db_name.usuarios)
            ;");

        echo "<p> -> resumen_general insertadas en historial</p>";

        DB::statement("
            DELETE FROM resumen_general
            WHERE usuario_id NOT IN (SELECT id FROM usuarios)
            ;");

        echo "<p> -> resumen_general depuradas de producción</p>";
        echo "<hr>";

        // resumen_x_curso
        DB::statement("
            INSERT INTO $hi_db_name.hi_resumen_x_curso (usuario_id, curso_id, categoria_id, asignados, aprobados, realizados, revisados, desaprobados, nota_prom, intentos, visitas, estado, porcentaje, created_at, updated_at)
            SELECT usuario_id, curso_id, categoria_id, asignados, aprobados, realizados, revisados, desaprobados, nota_prom, intentos, visitas, estado, porcentaje, created_at, updated_at FROM $db_name.resumen_x_curso
            WHERE $db_name.resumen_x_curso.usuario_id NOT IN (SELECT id FROM $db_name.usuarios)
            ;");

        echo "<p> -> resumen_x_curso insertadas en historial</p>";

        DB::statement("
            DELETE FROM resumen_x_curso
            WHERE usuario_id NOT IN (SELECT id FROM usuarios)
            ;");

        echo "<p> -> resumen_x_curso depuradas de producción</p>";
        echo "<hr>";

        // usuario_uploads
        DB::statement("
            INSERT INTO $hi_db_name.hi_usuario_uploads (usuario_id, link, description, created_at, updated_at)
            SELECT usuario_id, link, description, created_at, updated_at FROM $db_name.usuario_uploads
            WHERE $db_name.usuario_uploads.usuario_id NOT IN (SELECT id FROM $db_name.usuarios)
            ;");

        echo "<p> -> usuario_uploads insertadas en historial</p>";

        DB::statement("
            DELETE FROM usuario_uploads
            WHERE usuario_id NOT IN (SELECT id FROM usuarios)
            ;");

        echo "<p> -> usuario_uploads depuradas de producción</p>";
        echo "<hr>";

        // usuario_versiones
        DB::statement("
            INSERT INTO $hi_db_name.hi_usuario_versiones (usuario_id, v_android, v_ios, created_at, updated_at)
            SELECT usuario_id, v_android, v_ios, created_at, updated_at FROM $db_name.usuario_versiones
            WHERE $db_name.usuario_versiones.usuario_id NOT IN (SELECT id FROM $db_name.usuarios)
            ;");

        echo "<p> -> usuario_versiones insertadas en historial</p>";

        DB::statement("
            DELETE FROM usuario_versiones
            WHERE usuario_id NOT IN (SELECT id FROM usuarios)
            ;");

        echo "<p> -> usuario_versiones depuradas de producción</p>";
        echo "<hr>";

        // usuario_vigencia
        DB::statement("
            INSERT INTO $hi_db_name.hi_usuario_vigencia (usuario_id, fecha_inicio, fecha_fin)
            SELECT usuario_id, fecha_inicio, fecha_fin FROM $db_name.usuario_vigencia
            WHERE $db_name.usuario_vigencia.usuario_id NOT IN (SELECT id FROM $db_name.usuarios)
            ;");

        echo "<p> -> usuario_vigencia insertadas en historial</p>";

        DB::statement("
            DELETE FROM usuario_vigencia
            WHERE usuario_id NOT IN (SELECT id FROM usuarios)
            ;");

        echo "<p> -> usuario_vigencia depuradas de producción</p>";
        echo "<hr>";

        // visitas
        DB::statement("
            INSERT INTO $hi_db_name.hi_visitas (curso_id, post_id, usuario_id, sumatoria, descargas, created_at, updated_at)
            SELECT curso_id, post_id, usuario_id, sumatoria, descargas, created_at, updated_at FROM $db_name.visitas
            WHERE $db_name.visitas.usuario_id NOT IN (SELECT id FROM $db_name.usuarios)
            ;");

        echo "<p> -> visitas insertadas en historial</p>";

        DB::statement("
            DELETE FROM visitas
            WHERE usuario_id NOT IN (SELECT id FROM usuarios)
            ;");

        echo "<p> -> visitas depuradas de producción</p>";
        echo "<hr>";

        // // ranking (// aun no tiene registros)
        // // SELECT * FROM `ranking` WHERE usuario_id NOT IN (SELECT id FROM `usuarios`)

        // // usuario antecedentes (aun no tiene registros)
        // // SELECT * FROM `usuario_antecedentes` WHERE usuario_id NOT IN (SELECT id FROM `usuarios`)
    }

    // Depurar (limpiar data) de tablas que se quedaron con datos en el aire (Ej. pruebas con posteo_id que ya no existen, Ej2. Cuando se eliminan cursos, pero no sus temas se quedaron en el aire)
    public function depurar_tablas()
    {

        // visitas
        DB::statement("
            DELETE FROM visitas
            WHERE post_id NOT IN (SELECT id FROM posteos)
            ;");
        DB::statement("
            DELETE FROM visitas
            WHERE curso_id NOT IN (SELECT id FROM cursos)
            ;");

        echo "<p> -> visitas depuradas de producción</p>";
        echo "<hr>";

        // pruebas
        DB::statement("
            DELETE FROM pruebas
            WHERE posteo_id NOT IN (SELECT id FROM posteos)
            ;");
        DB::statement("
            DELETE FROM pruebas
            WHERE curso_id NOT IN (SELECT id FROM cursos)
            ;");
        DB::statement("
            DELETE FROM pruebas
            WHERE categoria_id NOT IN (SELECT id FROM categorias)
            ;");

        echo "<p> -> Pruebas depuradas de producción</p>";
        echo "<hr>";

        // ev_abiertas
        DB::statement("
            DELETE FROM ev_abiertas
            WHERE posteo_id NOT IN (SELECT id FROM posteos)
            ;");
        DB::statement("
            DELETE FROM ev_abiertas
            WHERE curso_id NOT IN (SELECT id FROM cursos)
            ;");
        DB::statement("
            DELETE FROM ev_abiertas
            WHERE categoria_id NOT IN (SELECT id FROM categorias)
            ;");

        echo "<p> -> ev_abiertas depuradas de producción</p>";
        echo "<hr>";

        // encuestas_respuestas
        DB::statement("
            DELETE FROM encuestas_respuestas
            WHERE encuestas_respuestas.curso_id NOT IN (SELECT id FROM cursos)
            ;");

        echo "<p> -> Encuestas(respuestas) depuradas de producción</p>";
        echo "<hr>";

        // diplomas
        DB::statement("
            DELETE FROM diplomas
            WHERE posteo_id NOT IN (SELECT id FROM posteos)
            ;");
        DB::statement("
            DELETE FROM diplomas
            WHERE curso_id NOT IN (SELECT id FROM cursos)
            ;");

        echo "<p> -> diplomas depuradas de producción</p>";
        echo "<hr>";

        // resumen_x_curso
        DB::statement("
            DELETE FROM resumen_x_curso
            WHERE curso_id NOT IN (SELECT id FROM cursos)
            ;");
        DB::statement("
            DELETE FROM resumen_x_curso
            WHERE categoria_id NOT IN (SELECT id FROM categorias)
            ;");

        echo "<p> -> resumen_x_curso depuradas de producción</p>";
        echo "<hr>";

        // reinicios
        DB::statement("
            DELETE FROM reinicios
            WHERE posteo_id NOT IN (SELECT id FROM posteos)
            ;");
        DB::statement("
            DELETE FROM reinicios
            WHERE curso_id NOT IN (SELECT id FROM cursos)
            ;");

        echo "<p> -> reinicios depuradas de producción</p>";
        echo "<hr>";

        // preguntas (EV)
        DB::statement("
            DELETE FROM preguntas
            WHERE post_id NOT IN (SELECT id FROM posteos)
            ;");

        echo "<p> -> preguntas (EV) depuradas de producción</p>";
        echo "<hr>";

        // posteos
        DB::statement("
            DELETE FROM posteos
            WHERE curso_id NOT IN (SELECT id FROM cursos)
            ;");
        DB::statement("
            DELETE FROM posteos
            WHERE categoria_id NOT IN (SELECT id FROM categorias)
            ;");

        echo "<p> -> posteos depuradas de producción</p>";
        echo "<hr>";

        // cursos
        DB::statement("
            DELETE FROM cursos
            WHERE categoria_id NOT IN (SELECT id FROM categorias)
            ;");

        echo "<p> -> cursos depuradas de producción</p>";
        echo "<hr>";

        // curso_encuesta
        DB::statement("
            DELETE FROM curso_encuesta
            WHERE curso_id NOT IN (SELECT id FROM cursos)
            ;");

        echo "<p> -> curso_encuesta depuradas de producción</p>";
        echo "<hr>";

    }

    // Funcion para recuperar data de historial


    //INSERTAR/ACTUALIZAR MASIVAMENTE NUEVOS USUARIOS
    public function subir_usuarios(Request $request)
    {
        $input = $request->all();
        $rules = array(
            'file_usuarios' => 'required',
        );

        $validator = \Validator::make($input, $rules);

        if ($validator->fails()) {
            return response()->json(['info' => 'Archivo requerido.']);
        }
        if ($request->hasFile("file_usuarios")) {
            // try {
            $import = new UsuariosMasivos();
            // $import->onlySheets(UsuariosMasivosImport::SHEET_TO_IMPORT);
            Excel::import($import, $request->file('file_usuarios'));
            $info = '';
            $info = $info . $import->get_qinsert() . ' nuevo(s) usuarios(s) <br>';
            $info = $info . $import->get_q_updates() . ' usuario(s) actualizados <br>';
            $info = $info . $import->get_q_errors() . ' error(es) detectado(s) <br>';
            $error = ($import->get_q_errors() > 0) ? true : false;
            $q_error = $import->get_q_errors();
            return response()->json(compact('info', 'error', 'q_error'));
        }
        return response()->json(['info' => 'Error']);
    }

    public function subir_cursos(Request $request)
    {
        $input = $request->all();
        $rules = array(
            'file_cursos' => 'required',
        );

        $validator = \Validator::make($input, $rules);
        if ($validator->fails()) {
            return \Redirect::back()->withInput()->withErrors($validator);
        }
        if ($request->hasFile("file_cursos")) {
            $tipos = explode(',', $request->get('tipos'));
            $Excel_datos = file_get_contents($request->file('file_cursos'));
            $ext_rand = rand(1, 100);
            Storage::disk('public')->put('temporal_' . $ext_rand . '.xlsx', $Excel_datos);
            $import = new CursosSubirImport();
            // ELEGIR QUE HOJAS SE IMPORTARAN
            $send = [];
            foreach ($tipos as $value) {
                switch ($value) {
                    case 'cursos':
                        $send[] = 'Carga de Cursos';
                        break;
                    case 'temas':
                        $send[] = 'Carga de Temas';
                        break;
                    case 'evaluaciones':
                        $send[] = 'Carga Evaluaciones';
                        break;
                }
            }
            if (count($send) != count($tipos)) {
                $msg_error = 'Las hojas no estan bien nombradas';
                return response()->json(compact('msg_error'), 500);
            }
            $import->onlySheets($send);
            //IMPORTAR
            // try {
            Excel::import($import, 'temporal_' . $ext_rand . '.xlsx', 'public');
            // ENVIAR MENSAJES
            $info = '';
            $error = false;
            $q_error = 0;
            if (in_array('cursos', $tipos)) {
                $info = $info . $import->cls_curso->get_q_inserts() . ' nuevo(s) cursos y ' . $import->cls_curso->get_q_errors() . ' error(es) <br>';
                if ($import->cls_curso->get_q_errors()) {
                    $q_error += $import->cls_curso->get_q_errors();
                    $error = true;
                }
            }
            if (in_array('temas', $tipos)) {
                $info = $info . $import->cls_tema->get_q_inserts() . ' nuevo(s) temas y ' . $import->cls_tema->get_q_errors() . ' error(es) <br>';
                if ($import->cls_tema->get_q_errors()) {
                    $q_error += $import->cls_tema->get_q_errors();
                    $error = true;
                }
            }
            if (in_array('evaluaciones', $tipos)) {
                $info = $info . $import->cls_evalucion->get_q_inserts() . ' nueva(s) preguntas(s) con ' . $import->cls_evalucion->get_q_errors() . ' error(es) <br>';
                if ($import->cls_evalucion->get_q_errors()) {
                    $q_error += $import->cls_evalucion->get_q_errors();
                    $error = true;
                }
            }
            Storage::disk('public')->delete('temporal_' . $ext_rand . '.xlsx');
            return response()->json(compact('info', 'error', 'q_error'));
            // } catch (\Throwable $th) {
            //     return response()->json(['info'=>'Error; revise el nombre de las hojas o verifique que esta subiendo el excel correcto.']);
            // }
        }
    }

    public function restaurar_bd2019(Request $request)
    {
        $data = $request->all();
        $dni = $data['dni'];
        $user = DB::connection('mysql3')->table('usuarios')->where('dni', $dni)->first();
        //BUSCAR SU GRUPO PARA LA BD FARMA_UNIVERSIDAD
        $grupo = Criterio::where('valor', $user->grupo)->first(['id']);
        //BUSCAR SU CARRERA
        $perf = DB::connection('mysql3')->table('perfiles')
            ->where('id', $user->perfil_id)
            ->first(['nombre', 'id']);
        $carrera = Carrera::where('nombre', $perf->nombre)->where('config_id', $user->config_id)->first(['id']);
        //CREAR USUARIO O BUSCAR USUARIO
        $user_b = Usuario::where('dni', 42991214)->first();
        //CURSOS
        $post_old = DB::connection('mysql3')
            ->table('posteo_perfil as pp')
            ->join('posteos as pos', 'pos.id', 'pp.posteo_id')
            ->join('cursos as cur', 'cur.id', 'pos.curso_id')
            ->select('pos.nombre', 'pos.id as posteo_id', 'pos.curso_id', 'pos.evaluable', 'cur.nombre as curso_nombre')
            ->where('pp.perfile_id', $perf->id)
            ->where('cur.config_id', $user->config_id)
            ->get();
        // $helper = new HelperController();
        $curso_ids = $this->help_cursos_x_matricula($user_b->id);
        $temas_corres = Posteo::with(['curso' => function ($q) {
            $q->select('cursos.nombre', 'cursos.id');
        }])->whereIn('curso_id', $curso_ids)->get(['nombre', 'curso_id', 'id', 'categoria_id'])->toArray();
        $plk_temas = collect($temas_corres)->pluck('nombre')->toArray();
        //VARIABLES PARA EL REPORTE
        //MIGRAR DE LOS POSTEOS CON NOMBRE IGUAL
        foreach ($post_old as $post) {
            if (in_array($post->nombre, $plk_temas)) {
                //MIGRAR PRUEBAS
                $color = "#" . $this->random_color();

                $prueba = DB::connection('mysql3')
                    ->table('pruebas')
                    ->where('posteo_id', $post->posteo_id)
                    ->where('usuario_id', $user->id)
                    ->first();
                if ($prueba) {
                    $idx = array_search($post->nombre, $plk_temas);
                    $post_new = $temas_corres[$idx];
                    $temas_corres[$idx]['color'] = $color;
                    $post->color = $color;
                    Prueba::updateOrInsert(
                        ['posteo_id' => $post_new['id'], 'usuario_id' => $user_b->id],
                        [
                            'categoria_id' => $post_new['categoria_id'],
                            'curso_id' => $post_new['curso_id'],
                            'posteo_id' => $post_new['id'],
                            'usuario_id' => $user_b->id,
                            'intentos' => $prueba->intentos,
                            'rptas_ok' => $prueba->rptas_ok,
                            'rptas_fail' => $prueba->rptas_fail,
                            'nota' => $prueba->nota,
                            'resultado' => $prueba->resultado,
                        ]);
                    //MIGRAR ENCUESTA_RESPUESTA
                    // $enc_rpts = DB::connection('mysql3')->table('encuestas_respuestas')->where('usuario_id', $user->id)->where('curso_id',$post->curso_id)->get();
                    // foreach ($enc_rpts as $enc_rpta) {
                    // $enc = DB::connection('mysql3')->table('encuestas')->where('id',$enc_rpta->encuesta_id)->first(['titulo']);
                    // $enc_new = Encuesta::where('titulo',$enc->titulo)->first(['id']);
                    // //PREGUNTA
                    // $preg = DB::connection('mysql3')->table('encuestas_preguntas')->where('id',$enc_rpta->pregunta_id)->first(['titulo']);
                    // if($enc_new){
                    //     $preg_new = Encuesta::where('encuesta_id',$enc_new->id)->where('titulo',$preg->titulo)->first(['id']);
                    //     if($preg_new){
                    //         dd($preg_new);
                    //         Encuestas_respuesta::updateOrInsert([
                    //             'post_id' => $post_new['id'],
                    //             'usuario_id' => $user_b->id,
                    //             ],[
                    //             'encuesta_id' => $enc_new->id,
                    //             'curso_id' => $post->curso_id,
                    //             // 'post_id' =>
                    //             'pregunta_id' => $reg_new->id,
                    //             'usuario_id' => $user_b->id,
                    //             'respuestas'=> $enc->respuestas,
                    //             'tipo_pregunta'=> $enc->tipo_pregunta,
                    //         ]);
                    //     }
                    // }
                    // }
                }
                //MIGRAR VISITAS (de donde sacar tipo_tema, estado_tema)
                $visita = DB::connection('mysql3')
                    ->table('visitas')
                    ->where('post_id', $post->posteo_id)
                    ->where('usuario_id', $user->id)
                    ->first();
                if ($visita) {
                    $idx = array_search($post->nombre, $plk_temas);
                    $post_new = $temas_corres[$idx];
                    $evaluable = ($post->evaluable == 'si') ? '' : 'no-evaluable';
                    Visita::updateOrInsert([
                        'post_id' => $post_new['id'],
                        'usuario_id' => $user_b->id,
                    ],
                        [
                            'post_id' => $post_new['id'],
                            'usuario_id' => $user_b->id,
                            'sumatoria' => $visita->sumatoria,
                            'descargas' => $visita->descargas,
                            'tipo_tema' => $evaluable,
                        ]);
                }
                //MIGRAR DIPLOMAS
                $dip = DB::connection('mysql3')
                    ->table('diplomas')
                    ->where('posteo_id', $post->posteo_id)
                    ->where('usuario_id', $user->id)
                    ->first();
                if ($dip) {
                    $idx = array_search($post->nombre, $plk_temas);
                    $post_new = $temas_corres[$idx];
                    Diploma::updateOrInsert([
                        'posteo_id' => $post_new['id'],
                        'usuario_id' => $user_b->id,
                    ],
                        [
                            'posteo_id' => $post_new['id'],
                            'usuario_id' => $user_b->id,
                            'curso_id' => $post_new['curso_id'],
                            'fecha_emision' => $dip->fecha_emision,
                        ]);
                }
                //MIGRAR REINICIOS
                $rei = DB::connection('mysql3')
                    ->table('reinicios')
                    ->where('posteo_id', $post->posteo_id)
                    ->where('usuario_id', $user->id)
                    ->first();
                if ($rei) {
                    $idx = array_search($post->nombre, $plk_temas);
                    $post_new = $temas_corres[$idx];
                    Reinicio::updateOrInsert([
                        'posteo_id' => $post_new['id'],
                        'usuario_id' => $user_b->id,
                    ],
                        [
                            'posteo_id' => $post_new['id'],
                            'usuario_id' => $user_b->id,
                            'curso_id' => $post_new['curso_id'],
                            'posteo_id' => $post_new['id'],
                            // 'admin_id' =>,
                            'tipo' => $rei->tipo,
                            'acumulado' => $rei->acumulado
                        ]);
                }
            }
        }
        //LIMPIAR TABLAS RESUMENES
        Resumen_x_curso::where('usuario_id', $user_b->id)->delete();
        Resumen_general::where('usuario_id', $user_b->id)->delete();
        //ACTUALIZAR TABLAS RESUMENES
        $rest_avance = new RestAvanceController();
        $ab_config = Abconfig::where('id', $user_b->config_id)->first(['mod_evaluaciones']);
        $mod_eval = json_decode($ab_config->mod_evaluaciones, true);
        //UPDATE
        $help_matri = new HelperController;
        $curso_ids_matriculado = $help_matri->help_cursos_x_matricula($user_b->id);
        foreach ($curso_ids_matriculado as $cur_id) {
            // ACTUALIZAR RESUMENES
            $rest_avance->actualizar_resumen_x_curso($user_b->id, $cur_id, $mod_eval['nro_intentos']);
        }
        $rest_avance->actualizar_resumen_general($user_b->id);
        $obj = new ExportReporteBD2019();
        $obj->temas_n_r = $temas_corres;
        $obj->temas_o_r = $post_old;
        $obj->view();
        ob_end_clean();
        ob_start();
        return Excel::download($obj, 'reporte.xlsx');
    }

    private function random_color_part()
    {
        return str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT);
    }

    private function random_color()
    {
        return $this->random_color_part() . $this->random_color_part() . $this->random_color_part();
    }

    private function help_cursos_x_matricula($usuario_id)
    {
        $usuario = Usuario_rest::where('id', $usuario_id)->first(['id', 'grupo']);
        $result = collect();
        $matriculas = DB::table('matricula AS m')
            ->where('m.usuario_id', $usuario->id)
            ->get(['ciclo_id', 'id', 'carrera_id']);
        foreach ($matriculas as $matricula) {
            $matriculas_criterio = Matricula_criterio::select('criterio_id')->where('matricula_id', $matricula->id)->first();
            $criterio_id = $matriculas_criterio->criterio_id;
            $curriculas_criterios = Curricula_criterio::select('curricula_id')->where('criterio_id', $criterio_id)->get();
            foreach ($curriculas_criterios as $curricula_criterio) {
                $curricula = Curricula::join('cursos', 'cursos.id', 'curricula.curso_id')
                    ->select('ciclo_id', 'curso_id')
                    ->where('cursos.estado', 1)
                    ->where('curricula.id', $curricula_criterio->curricula_id)->first();
                if (isset($curricula) && $curricula->ciclo_id == $matricula->ciclo_id) {
                    $result->push($curricula->curso_id);
                }
            }
        }
        return $result->unique()->values()->all();
    }
}
