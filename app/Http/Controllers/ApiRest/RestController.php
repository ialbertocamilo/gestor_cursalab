<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\ApiRest\HelperController;
use App\Http\Controllers\Controller;
use App\Mail\RecuperarPass;
use App\Models\Abconfig;
use App\Models\Announcement;
use App\Models\AyudaApp;
use App\Models\Carrera;
use App\Models\Categoria;
use App\Models\Ciclo;
use App\Models\Curricula;
use App\Models\Curso;
use App\Models\Curso_encuesta;
use App\Models\Matricula;
use App\Models\Notification;
use App\Models\Poll;
use App\Models\Posteo;
use App\Models\Pregunta;
use App\Models\Prueba;
use App\Models\Resumen_general;
use App\Models\Resumen_x_curso;
use App\Models\Usuario;
use App\Models\UsuarioAyuda;
use App\Models\Usuario_rest;
use App\Models\Usuario_upload;
use App\Models\Visita;
use Carbon\Carbon;
use Config;
use DB;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use JWTAuth;
use Storage;
use Tymon\JWTAuth\Exceptions\JWTException;


class RestController extends Controller
{
  /* public function __construct()
    {
        // header('Access-Control-Allow-Origin: *');
        // header('Access-Control-Allow-Methods:  GET,PUT,POST,DELETE,PATCH,OPTIONS');
        // // header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        // // header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, X-Token-Auth, Authorization, x-xsrf-token");
        // header("Access-Control-Allow-Headers: Origin, Accept, X-Requested-With, Content-Type, X-Token-Auth, X-Auth-Token, Authorization, x-xsrf-token");

        // Config::set('jwt.user', Usuario_rest::class);
        // Config::set('auth.providers.users.model', Usuario_rest::class);
        $this->middleware('auth.jwt', ['except' => ['appVersions', 'download_file']]);
        return auth()->shouldUse('api');
    }*/

    public function index()
    {
        return "...";
    }

    /**
     *   Verificar versiones actuales subidas a Play Store y App Store
     *   Error => 0: se encontraron datos
     *            1: No se encontraron datos
     */
    public function appVersions()
    {
        $result = DB::table('app_versions')->where('id', 1)->first();

        if ($result) {
            $data = array('error' => 0, 'data' => $result);
        } else {
            $data = array('error' => 1);
        }

        return $data;
    }

    // public function pruebaGet(Request $request) {
    //     return $request;
    // }

    // public function pruebaPost(Request $request) {
    //     return $request;
    // }

    /**
     *
     * @param Request $request
     */
    public function terminar_token(Request $request)
    {
        $this->validate($request, ['token' => 'required']);
        try {
            JWTAuth::invalidate($request->input('token'));
            return response()->json(['success' => true, 'message' => "Has terminado tu sesión satisfactoriamente."]);
        } catch (JWTException $e) {
            return response()->json(['success' => false, 'error' => 'Error al cerrar sesión, por favor intente de nuevo.'], 500);
        }
    }

    // lOGIN
    public function usuariosPorDniPass($userinput = null, $pass = null)
    {
        $user_id = null;
        if (is_null($userinput) || is_null($pass)) {
            $data = array('error' => 2, 'data' => null);
        } else {
            $usuario = DB::table('usuarios')->select('id', 'password', 'dni', 'config_id')->whereRaw("dni = '" . $userinput . "'")->where('estado', 1)->first();
            if (isset($usuario)) {
                // Validar usuario
                if (password_verify($pass, $usuario->password)) {
                    //actualiza ultima_sesion
                    date_default_timezone_set('America/Lima');
                    DB::table('usuarios')->where('id', $user_id)->update(['ultima_sesion' => date('Y-m-d H:i:s')]);

                    $config_data = DB::table('ab_config')->select('id', 'color', 'color2', 'duracion_dias', 'logo', 'isotipo', 'mod_agrupacion', 'mod_cronometro', 'mod_encuestas', 'mod_evaluaciones', 'mod_mainmenu', 'mod_sidemenu', 'mod_tipovalidacion', 'thumb_diploma', 'mod_push', 'push_code', 'plantilla_diploma')->where('id', $usuario->config_id)->where('estado', 1)->first();

                    $datos = ['user_id' => $usuario->id, 'config_data' => $config_data];
                    $user_id = $usuario->id;

                    $data = array('error' => 0, 'data' => $datos);
                } else {
                    $data = array('error' => 3, 'data' => null);
                }
            } else {
                $data = array('error' => 1, 'data' => null);
            }
        }
        $credentials = array('id' => $user_id, 'password' => $pass);
        $token = JWTAuth::attempt($credentials);
        $data['token'] = $token;
        return $data;

        // $dd = password_hash('prueba',PASSWORD_BCRYPT);
        // $de = password_verify('prueba', $ss);
    }

    // lOGIN v2
    public function login_dni_pass_v2($username = null, $pass = null, $version, $os)
    {
        $user_id = null;
        if (is_null($username) || is_null($pass)) {
            $data = array('error' => 2, 'data' => null);
        } else {
            $usuario = DB::table('usuarios')->select('id', 'password', 'dni', 'config_id')->whereRaw("dni = '" . $username . "'")->where('estado', 1)->first();

            if (isset($usuario)) {
                // Actualizar tabla usuario_versiones
                $usu_ver = DB::table('usuario_versiones')->where("usuario_id", $usuario->id)->first();
                $tipo_os = ($os == "android") ? "v_android" : "v_ios";

                if ($usu_ver) {
                    $query = DB::table('usuario_versiones')->where('usuario_id', $usuario->id)->update(array($tipo_os => $version));
                } else {
                    $query = DB::table('usuario_versiones')->insert(array('usuario_id' => $usuario->id, $tipo_os => $version));
                }

                // Validar login
                if (password_verify($pass, $usuario->password)) {
                    //actualiza ultima_sesion
                    date_default_timezone_set('America/Lima');
                    DB::table('usuarios')->where('id', $user_id)->update(['ultima_sesion' => date('Y-m-d H:i:s')]);
                    //
                    $config_data = DB::table('ab_config')->select('id', 'color', 'color2', 'duracion_dias', 'logo', 'isotipo', 'mod_agrupacion', 'mod_cronometro', 'mod_encuestas', 'mod_evaluaciones', 'mod_mainmenu', 'mod_sidemenu', 'mod_tipovalidacion', 'thumb_diploma', 'mod_push', 'push_code', 'plantilla_diploma')->where('id', $usuario->config_id)->where('estado', 1)->first();

                    $datos = ['user_id' => $usuario->id, 'config_data' => $config_data];
                    $user_id = $usuario->id;

                    $data = array('error' => 0, 'data' => $datos);
                } else {
                    $data = array('error' => 3, 'data' => null);
                }
            } else {
                $data = array('error' => 1, 'data' => null);
            }
        }
        $credentials = array('id' => $user_id, 'password' => $pass);
        $token = JWTAuth::attempt($credentials);
        $data['token'] = $token;
        return $data;

        // $dd = password_hash('prueba',PASSWORD_BCRYPT);
        // $de = password_verify('prueba', $ss);
    }


    /**
     *   Función para el login
     *   Error => 0: se encontraron datos
     *            1: No se encontraron datos
     *            2: Es nulo u otro error
     */
    public function usuariosDatos($id = null)
    {
        if (is_null($id)) {
            $data = array('error' => 2, 'data' => null);
        } else {
            $usuario = Usuario::select('botica', 'cargo', 'config_id', 'dni', 'grupo_id', 'id', 'nombre', 'sexo', 'perfil_id', 'grupo', 'token_firebase')
                ->where('id', $id)
                ->where('estado', 1)
                ->first();

            if ($usuario) {
                // $perfil = DB::table('perfiles')->where('id', $usuario->perfil_id)->first();
                $novedades = DB::table('anuncios')->where('estado', 1)->orderBy('orden', 'DESC')->get();
                // $data = array('error' => 0, 'usuario' => $usuario, 'perfil' => $perfil, 'novedades' => $novedades, 'ingreso'=>$usuario->ingreso, 'matricula'=>$usuario->matricula);
                $carrera = ($usuario->matricula->last()) ? $usuario->matricula->last()->carrera : "";
                // $data = array('error' => 0, 'usuario' => $usuario, 'perfil' => $perfil, 'novedades' => $novedades, 'matricula'=>$usuario->matricula, 'carrera'=>$carrera);
                $data = array('error' => 0, 'usuario' => $usuario, 'novedades' => $novedades, 'matricula' => $usuario->matricula, 'carrera' => $carrera);
            } else {
                $data = array('error' => 1, 'usuario' => null, 'novedades' => null);
            }
        }
        return $data;
    }

    public function usuario_data($id = null)
    {
        if (is_null($id)) {
            $result = array('error' => 2, 'data' => null);
        } else {
            $usuario = Usuario::select('nombre', 'grupo_id', 'botica', 'cargo', 'sexo', 'grupo')
                ->where('id', $id)
                ->where('estado', 1)
                ->first();

            if ($usuario) {
                $matricula_actual = Matricula::select('carrera_id', 'ciclo_id')->where('usuario_id', $id)->where('estado', 1)->where('presente', 1)->orderBy('id', 'DESC')->first();
                $carrera = Carrera::select('id', 'nombre')->where('id', $matricula_actual->carrera_id)->first();
                $ciclo = Ciclo::select('id', 'nombre')->where('id', $matricula_actual->ciclo_id)->first();

                $data = array(
                    'nombre' => $usuario->nombre,
                    'grupo_id' => $usuario->grupo_id,
                    'botica' => $usuario->botica,
                    'cargo' => $usuario->cargo,
                    'sexo' => $usuario->sexo,
                    'grupo' => $usuario->grupo,
                    'carrera' => $carrera,
                    'ciclo' => $ciclo
                );
                $result = array('error' => 0, 'data' => $data);
            } else {
                $result = array('error' => 1, 'data' => null);
            }
        }
        return $result;
    }

    // Anuncios
    public function anuncios($config_id = null)
    {
        if (is_null($config_id)) {
            $data = array('error' => 2, 'data' => null);
        } else {
             $anuncios = Announcement::getPublisheds($config_id);
            // $anuncios = DB::table('anuncios')->select(DB::raw("nombre, contenido, imagen, destino, link, archivo, DATE_FORMAT(publish_date,'%d/%m/%Y') AS publish_date"))->where('config_id', 'like', '%"' . $config_id . '"%')->where('estado', 1)->orderBy('orden', 'DESC')->get();
            $data = array('error' => 0, 'data' => $anuncios);
        }
        return $data;
    }

    public function cambiarPassword($dni = null, $pass_actual = null, $pass_nuevo = null)
    {
        if (is_null($dni) || is_null($pass_actual) || is_null($pass_nuevo)) {
            $data = array('error' => 2, 'data' => null);
        } else {
            $usuarios = DB::table('usuarios')->select('id', 'password', 'dni')->where('dni', $dni)->where('estado', 1)->limit(1)->get();
            if (count($usuarios) > 0) {

                if (password_verify($pass_actual, $usuarios[0]->password)) {
                    $passhash = password_hash(trim($pass_nuevo), PASSWORD_BCRYPT);
                    $update = DB::table('usuarios')->where('dni', $dni)->update(['password' => $passhash]);
                    if ($update)
                        $data = array('error' => 0, 'data' => $usuarios[0]->id);
                    else
                        $data = array('error' => 4, 'data' => null);
                } else {
                    $data = array('error' => 3, 'data' => null);
                }
            } else {
                $data = array('error' => 1, 'data' => null);
            }
        }
        return $data;
    }


    /**
     *   Crea por primera vez, y actualiza para los siguiente INTENTOS del usuario ante una prueba
     */
    // public function preguntasIntentos( $post_id = null, $id_user = null)
    // {
    //     date_default_timezone_set("America/Lima");
    //     if (is_null($post_id) && is_null($id_user))
    //     {
    //         $response = array('error' => 2, 'data'=>null);
    //     }
    //     else
    //     {

    //         $user = DB::table('usuarios')->select('config_id')->where('id', $id_user)->first();
    //         $config = DB::table('ab_config')->select('mod_evaluaciones')->where('id', $user->config_id)->first();
    //         $mod_eval = json_decode($config->mod_evaluaciones, true);
    //         $nro_max__intentos = $mod_eval['nro_intentos'];
    //         //
    //         $existe = Prueba::where([['posteo_id', $post_id],['usuario_id', $id_user]])->first();
    //         $correcto = [];

    //         if ($existe){

    //             $intentos = $existe->intentos;

    //             if ( intval($intentos) < intval($nro_max__intentos) ) {
    //                 $new_intentos = intval($intentos) + 1;

    //                 $update = Prueba::where('id', $existe->id)
    //                                     ->update(array('intentos' => $new_intentos));
    //                 $correcto = $update;
    //             }
    //         }else{
    //             $posteo = Posteo::select('categoria_id', 'curso_id')->find($post_id);

    //             $intentos = 1;
    //             $insertar = Prueba::insert(array(
    //                                         'categoria_id' => $posteo->categoria_id,
    //                                         'curso_id' => $posteo->curso_id,
    //                                         'posteo_id' => $post_id,
    //                                         'usuario_id' => $id_user,
    //                                         'created_at'=>date('Y-m-d H:i:s'),
    //                                         'intentos' => $intentos,
    //                                         'fuente'=>'old'
    //                                     ));
    //             $correcto = $insertar;
    //         }

    //         if (isset($correcto))
    //             $response = array('error' => 0, 'data'=>$intentos);
    //         else
    //             $response = array('error' => 1, 'data'=>null);
    //     }
    //     return $response;
    // }

    // public function preguntasIntentos($post_id = null, $id_user = null)
    // {
    //     if (is_null($post_id) && is_null($id_user)) {
    //         $response = array('error' => 2, 'data' => null);
    //     } else {
    //         $user = DB::table('usuarios')->select('config_id')->where('id', $id_user)->first();
    //         $config = DB::table('ab_config')->select('mod_evaluaciones')->where('id', $user->config_id)->first();
    //         $mod_eval = json_decode($config->mod_evaluaciones, true);
    //         //
    //         $ev = Prueba::select('id', 'intentos', 'categoria_id', 'curso_id')->where('posteo_id', $post_id)->where('usuario_id', $id_user)->first();
    //         if ($ev) {
    //             if (intval($ev->intentos) < intval($mod_eval['nro_intentos'])) {
    //                 $intentos = intval($ev->intentos) + 1;
    //                 Prueba::where('id', $ev->id)->update(array('intentos' => $intentos));
    //             }
    //         } else {
    //             $posteo = Posteo::find($post_id);
    //             $intentos = 1;
    //             $new_prueba_id = Prueba::insertGetId(array(
    //                 'categoria_id' => $posteo->categoria_id,
    //                 'curso_id' => $posteo->curso_id,
    //                 'posteo_id' => $post_id,
    //                 'usuario_id' => $id_user,
    //                 'intentos' => $intentos,
    //                 'fuente' => 'old'
    //             ));

    //             $ev = Prueba::find($new_prueba_id);
    //         }

    //         // INSERTA / ACTUALIZA -> RESUMEN_X_CURSO
    //         $res_x_curso = DB::table('resumen_x_curso')->select('id', 'intentos')->where('usuario_id', $id_user)->where('curso_id', $ev->curso_id)->first();
    //         if ($res_x_curso) { // Actualiza
    //             $suma_intentos = $res_x_curso->intentos + 1;

    //             DB::table('resumen_x_curso')->where('id', $res_x_curso->id)->where('curso_id', $ev->curso_id)->update(array('intentos' => $suma_intentos));
    //         } else { // Inserta
    //             $this->new_res_x_curso($id_user, $ev->curso_id, $ev->categoria_id);
    //         }

    //         // INSERTA / ACTUALIZA -> RESUMEN_GENERAL
    //         $res_general = DB::table('resumen_general')->select('id', 'intentos')->where('usuario_id', $id_user)->first();
    //         if ($res_general) { // Actualiza
    //             $suma_intentos = $res_general->intentos + 1;

    //             DB::table('resumen_general')->where('id', $res_general->id)->update(array('intentos' => $suma_intentos));
    //         } else { // Inserta
    //             $this->new_res_general($id_user);
    //         }

    //         if ($ev)
    //             $response = array('error' => 0, 'data' => $intentos);
    //         else
    //             $response = array('error' => 1, 'data' => null);
    //     }
    //     return $response;
    // }


    /**
     *   Crea por primera vez, y actualiza para los siguiente INTENTOS del usuario ante una prueba
     */
    // public function preguntasIntentos_v7($post_id = null, $id_user = null, $fuente)
    // {
    //     if (is_null($post_id) && is_null($id_user)) {
    //         $response = array('error' => 2, 'data' => null);
    //     } else {
    //         $user = DB::table('usuarios')->select('config_id')->where('id', $id_user)->first();
    //         $config = DB::table('ab_config')->select('mod_evaluaciones')->where('id', $user->config_id)->first();
    //         $mod_eval = json_decode($config->mod_evaluaciones, true);
    //         //
    //         $ev = Prueba::select('id', 'intentos', 'categoria_id', 'curso_id')->where('posteo_id', $post_id)->where('usuario_id', $id_user)->first();
    //         if ($ev) {
    //             if (intval($ev->intentos) < intval($mod_eval['nro_intentos'])) {
    //                 $intentos = intval($ev->intentos) + 1;
    //                 Prueba::where('id', $ev->id)->update(array('intentos' => $intentos));
    //             }
    //         } else {
    //             $posteo = Posteo::find($post_id);
    //             $intentos = 1;
    //             $new_prueba_id = Prueba::insertGetId(array(
    //                 'categoria_id' => $posteo->categoria_id,
    //                 'curso_id' => $posteo->curso_id,
    //                 'posteo_id' => $post_id,
    //                 'usuario_id' => $id_user,
    //                 'intentos' => $intentos,
    //                 'fuente' => $fuente
    //             ));

    //             $ev = Prueba::find($new_prueba_id);
    //         }

    //         // INSERTA / ACTUALIZA -> RESUMEN_X_CURSO
    //         $res_x_curso = DB::table('resumen_x_curso')->select('id', 'intentos')->where('usuario_id', $id_user)->where('curso_id', $ev->curso_id)->first();
    //         if ($res_x_curso) { // Actualiza
    //             $suma_intentos = $res_x_curso->intentos + 1;

    //             DB::table('resumen_x_curso')->where('id', $res_x_curso->id)->where('curso_id', $ev->curso_id)->update(array('intentos' => $suma_intentos));
    //         } else { // Inserta
    //             $this->new_res_x_curso($id_user, $ev->curso_id, $ev->categoria_id);
    //         }

    //         // INSERTA / ACTUALIZA -> RESUMEN_GENERAL
    //         $res_general = DB::table('resumen_general')->select('id', 'intentos')->where('usuario_id', $id_user)->first();
    //         if ($res_general) { // Actualiza
    //             $suma_intentos = $res_general->intentos + 1;

    //             DB::table('resumen_general')->where('id', $res_general->id)->update(array('intentos' => $suma_intentos));
    //         } else { // Inserta
    //             $this->new_res_general($id_user);
    //         }

    //         if ($ev)
    //             $response = array('error' => 0, 'data' => $intentos);
    //         else
    //             $response = array('error' => 1, 'data' => null);
    //     }
    //     return $response;
    // }

    // public function new_res_x_curso($id_user, $curso_id, $categoria_id)
    // {
    //     $asignados = Posteo::where('curso_id', $curso_id)->where('estado', 1)->count();

    //     $data = Resumen_x_curso::create([
    //         'usuario_id' => $id_user,
    //         'curso_id' => $curso_id,
    //         'categoria_id' => $categoria_id,
    //         'asignados' => $asignados,
    //         'intentos' => 1,
    //         'estado' => 'desarrollo'
    //     ]);

    //     return $data;
    // }

    public function new_res_general($id_user)
    {
        $asignados = 1;

        $matricula = Matricula::select('carrera_id')->where('usuario_id', $id_user)->where('estado', 1)->get()->last();
        if ($matricula) {
            $asignados = Curricula::where('carrera_id', $matricula->carrera_id)->where('estado', 1)->count();
        }

        $intent_elo = DB::table('resumen_x_curso')
            ->select(DB::raw('COUNT(nota_prom) AS sum_intent'))
            ->where('usuario_id', $id_user)
            ->first();

        $intentos = ($intent_elo) ? $intent_elo->sum_intent : 1;

        $rank_default = Usuario::count();

        $data = Resumen_general::create([
            'usuario_id' => $id_user,
            'cur_asignados' => $asignados,
            'intentos' => $intentos,
            'rank' => $rank_default
        ]);

        return $data;
    }

    /**
     *
     *   Preguntas
     *   Verificar si existen preguntas
     *
     */

    public function existenPreguntas($id_post = null)
    {
        if (is_null($id_post)) {
            $response = array('error' => 2, 'data' => null);
        } else {
            $preguntas = Pregunta::where('post_id', $id_post)
                ->where('estado', 1)
                ->inRandomOrder()
                // ->limit(2)
                ->get();

            if (count($preguntas) > 0)
                $response = array('error' => 0, 'data' => $preguntas);
            else
                $response = array('error' => 1, 'data' => null);
        }
        return $response;
    }
    /**
     *
     *   Preguntas
     *   Cargar preguntas
     *
     */

    public function cargarPreguntas($id_post = null, $max_cant = null)
    {
        if (is_null($id_post)) {
            $response = array('error' => 2, 'data' => null);
        } else {
            $posteo = Posteo::select('tipo_ev')->find($id_post);

            if ($posteo->tipo_ev == 'calificada') {
                # code...
                $preguntas = Pregunta::where('post_id', $id_post)
                    ->where('estado', 1)
                    ->where('tipo_pregunta','selecciona')
                    ->inRandomOrder()
                    ->limit($max_cant)
                    ->get();
            } else {
                $preguntas = Pregunta::where('post_id', $id_post)
                    ->where('estado', 1)
                    ->where('tipo_pregunta','texto')
                    // ->limit($max_cant)
                    ->get();
            }

            if (count($preguntas) > 0)
                $response = array('error' => 0, 'data' => $preguntas);
            else
                $response = array('error' => 1, 'data' => null);
        }
        return $response;
    }

    // Consultar preguntas de post, segun rptas de usuario -> para mostrar respuestas correctas/incorrectas que hizo

    public function preguntasRptasUsuario($id_post = null, $id_user = null)
    {
        if (is_null($id_post)) {
            $response = array('error' => 2, 'data' => null);
        } else {
            $select = DB::table('pruebas')
                ->select('usu_rptas')
                ->where('posteo_id', $id_post)
                ->where('usuario_id', $id_user)
                ->first();

            $pgtas_id = [];
            if (isset($select) && $select->usu_rptas != '') {
                $array = json_decode($select->usu_rptas);
                foreach ($array as $key => $value) {
                    $pgtas_id[] = $value->preg_id;
                }
            }

            $preguntas = Pregunta::where('post_id', $id_post)
                ->whereIn('id', $pgtas_id)
                ->where('estado', 1)
                // ->inRandomOrder()
                ->get();

            if (count($preguntas) > 0)
                $response = array('error' => 0, 'data' => $preguntas);
            else
                $response = array('error' => 1, 'data' => null);
        }
        return $response;
    }

    // saber cuantos intentos me quedan por posteo
    public function verificarAprobado($user_id = null, $post_id = null)
    {
        if (is_null($post_id) || is_null($user_id)) {
            $response = array('error' => 2, 'data' => null);
        } else {
            $select = DB::table('pruebas')
                ->select('resultado', 'nota')
                ->where('posteo_id', $post_id)
                ->where('usuario_id', $user_id)
                ->first();

            $result = ($select) ? $select : null;
            $response = array('error' => 0, 'data' => $result);
        }
        return $response;
    }

    //
    public function verificar_aprobado_v2($user_id = null, $post_id = null)
    {
        if (is_null($post_id) || is_null($user_id)) {
            $response = array('error' => 2, 'data' => null);
        } else {
            $select = DB::table('pruebas')
                ->select('resultado', 'nota')
                ->where('posteo_id', $post_id)
                ->where('usuario_id', $user_id)
                ->first();

            $result = ($select) ? $select : null;
            $response = array('error' => 0, 'data' => $result);
        }
        return $response;
    }

    public function listaEncLibres($config_id, $user_id)
    {
        $encs = Poll::where('tipo', 'libre')->where('estado', 1)->get();

        $data = [];
        foreach ($encs as $enc) {
            $er = DB::table('encuestas_respuestas')
                ->where('encuesta_id', $enc->id)
                ->where('usuario_id', $user_id)
                ->first();
            if ($er) {
                $data[] = ["id" => $enc->id, "tipo" => $enc->tipo, "titulo" => $enc->titulo, "imagen" => $enc->imagen, "vigencia" => $enc->vigencia, "estado" => $enc->estado, "user_realizado" => $er->id];
            } else {
                $data[] = ["id" => $enc->id, "tipo" => $enc->tipo, "titulo" => $enc->titulo, "imagen" => $enc->imagen, "vigencia" => $enc->vigencia, "estado" => $enc->estado, "user_realizado" => ""];
            }
        }


        // $response = array('error' => 0, 'data' => $data);
        return $data;
        // return $encs;
    }


    public function usuarioRespuestasEval($user_id = null, $post_id = null)
    {

        if (is_null($post_id) || is_null($user_id)) {
            $response = array('error' => 2, 'data' => null);
        } else {
            $select = DB::table('pruebas')
                ->select('usu_rptas')
                ->where('usuario_id', $user_id)
                ->where('posteo_id', $post_id)
                ->first();

            if ($select) {
                $response = array('error' => 0, 'data' => $select->usu_rptas);
            }
        }
        return $response;
    }

    // Cargar TODAS las categorias dentro de un MODULO
    public function cargarCategorias($config_id = null)
    {
        $categorias = Categoria::select('id', 'config_id', 'modalidad', 'nombre', 'descripcion', 'imagen', 'color', 'en_menu_sec')
            ->where('estado', 1)
            ->where('config_id', $config_id)
            ->orderBy('orden', 'asc')
            ->get();
        if (count($categorias) > 0)
            $data = array('error' => 0, 'data' => $categorias);
        else
            $data = array('error' => 1, 'data' => null);
        return $data;
    }


    // Cargar categorias dentro de un MODULO y un PERFIL
    // -> Deprecado desde el 17/04
    // public function escuela_x_perfil($config_id = null, $perfil_id)
    // {

    //     $cates_x_perfil = DB::table('categoria_perfil')
    //             ->select('categoria_id')
    //             ->where('perfil_id', $perfil_id)
    //              ->pluck('categoria_id');

    //     $categorias = Categoria::select('id','config_id', 'modalidad', 'nombre', 'descripcion','imagen', 'color', 'en_menu_sec')
    //                             ->where('estado', 1)
    //                             ->where('config_id', $config_id)
    //                             ->whereIn('id', $cates_x_perfil)
    //                             ->orderBy('orden','asc')
    //                             ->get();
    //     if (count($categorias) > 0)
    //         $data = array('error' => 0, 'data' => $categorias);
    //     else
    //         $data = array('error' => 1, 'data' => null);
    //     return $data;
    // }

    // -> Deprecado desde el 17/04
    // public function cargarCursos($config_id = null, $perfil_id = null){
    //     $cursos = DB::table('cursos AS c')
    //                     ->select('c.id','c.categoria_id', 'c.nombre', 'c.descripcion','c.imagen','c.requisito_id', 'c_evaluable')
    //                     ->join('curso_perfil AS cp','c.id','=','cp.curso_id')
    //                     ->where('cp.perfile_id', $perfil_id)
    //                      ->where('c.config_id', $config_id)
    //                      ->where('c.estado', 1)
    //                      ->orderBy('c.orden','ASC')
    //                      ->get();

    //     if (count($cursos) > 0)
    //         $data = array('error' => 0, 'data' => $cursos);
    //     else
    //         $data = array('error' => 1, 'data' => null);
    //     return $data;
    // }


    // Cargar temas v2
    public function cargarTemas2($perfil_id = null, $config_id = null)
    {
        if (is_null($config_id)) {
            $data = array('error' => 2, 'data' => null);
        } else {
            $cursos_ids = DB::table('cursos')
                ->select('id')
                ->where('config_id', $config_id)
                ->where('estado', 1)
                ->orderBy('orden', 'ASC')
                ->pluck('id');

            // $videos = DB::table('posteos AS p')
            //                 ->select('p.id', 'p.curso_id', 'p.requisito_id', 'p.nombre', 'p.resumen', 'p.contenido', 'p.cod_video', 'p.imagen', 'p.archivo', 'p.video', 'p.evaluable','ca.modalidad AS cate_modali', 'p.tipo_ev')
            //                 ->join('posteo_perfil AS pp','p.id','=','pp.posteo_id')
            //                 ->join('categorias AS ca','p.categoria_id','=','ca.id')
            //                 ->where('pp.perfile_id', $perfil_id)
            //                  ->where('p.estado', 1)
            //                  ->whereIn('curso_id', $cursos_ids)
            //                  ->orderBy('p.orden','ASC')
            //                  ->get();

            $videos = DB::table('posteos AS p')
                ->select('p.id', 'p.curso_id', 'p.requisito_id', 'p.nombre', 'p.resumen', 'p.contenido', 'p.cod_video', 'p.imagen', 'p.archivo', 'p.video', 'p.evaluable', 'ca.modalidad AS cate_modali', 'p.tipo_ev')
                ->join('categorias AS ca', 'p.categoria_id', '=', 'ca.id')
                ->where('p.estado', 1)
                ->whereIn('p.curso_id', $cursos_ids)
                ->orderBy('p.orden', 'ASC')
                ->get();

            if (count($videos) > 0)
                $data = array('error' => 0, 'data' => $videos);
            else
                $data = array('error' => 1, 'data'  => null);
        }
        return $data;
    }

    // Cargar temas v3
    public function cargarTemas_v3($carrera_id = null)
    {
        if (is_null($carrera_id)) {
            $data = array('error' => 2, 'data' => null);
        } else {
            $curso_ids = Curricula::where('carrera_id', $carrera_id)->where('estado', 1)->pluck('curso_id');

            $temas_query = DB::table('posteos AS p')
                ->select(\DB::raw('p.id, p.curso_id, p.requisito_id, p.nombre, p.resumen, p.contenido, p.cod_video, p.imagen, p.archivo, p.video, p.evaluable, ca.modalidad AS cate_modali, p.tipo_ev, p.media'))
                ->join('categorias AS ca', 'p.categoria_id', '=', 'ca.id')
                ->where('p.estado', 1)
                ->whereIn('p.curso_id', $curso_ids)
                ->orderBy('p.orden', 'ASC');
            $temas = $temas_query->get();

            $tags = DB::table('tag_relationships AS p')
                ->select('p.element_id', 't.nombre', 't.color')
                ->join('tags AS t', 'p.tag_id', '=', 't.id')
                ->where('p.element_type', 'tema')
                ->whereIn('p.element_id', $temas_query->pluck('id'))
                ->get();

            $tags_arr = [];
            foreach ($tags as $tag) {
                $tags_arr[$tag->element_id] = ['nombre' => $tag->nombre, 'color' => $tag->color];
            }

            if (count($temas) > 0)
                $data = array('error' => 0, 'temas' => $temas, 'tags' => $tags_arr);
            else
                $data = array('error' => 1, 'data'  => null);
        }
        return $data;
    }

    // Cargar temas v4
    public function cargar_temas_v4($carrera_id = null, $usuario_id)
    {
        if (is_null($carrera_id)) {
            $data = array('error' => 2, 'data' => null);
        } else {
            $curso_ids = Curricula::where('carrera_id', $carrera_id)->where('estado', 1)->pluck('curso_id');

            $temas_query = DB::table('posteos AS p')
                ->select(\DB::raw('p.id, p.curso_id, p.requisito_id, p.nombre, p.resumen, p.contenido, p.cod_video, p.imagen, p.archivo, p.video, p.evaluable, ca.modalidad AS cate_modali, p.tipo_ev, p.media'))
                ->join('categorias AS ca', 'p.categoria_id', '=', 'ca.id')
                ->where('p.estado', 1)
                ->whereIn('p.curso_id', $curso_ids)
                ->orderBy('p.orden', 'ASC');

            $temas = $temas_query->get();

            $tags = DB::table('tag_relationships AS p')
                ->select('p.element_id', 't.nombre', 't.color')
                ->join('tags AS t', 'p.tag_id', '=', 't.id')
                ->where('p.element_type', 'tema')
                ->whereIn('p.element_id', $temas_query->pluck('id'))
                ->get();

            $actividad = DB::table('visitas AS p')
                ->select('p.post_id', 'p.estado_tema')
                ->where('p.usuario_id', $usuario_id)
                ->where('p.estado_tema', '!=', '')
                ->whereIn('p.post_id', $temas_query->pluck('id'))
                ->get();

            $tags_arr = [];
            foreach ($tags as $tag) {
                $tags_arr[$tag->element_id] = ['nombre' => $tag->nombre, 'color' => $tag->color];
            }

            if (count($temas) > 0)
                $data = array('error' => 0, 'temas' => $temas, 'tags' => $tags_arr, 'actividad' => $actividad);
            else
                $data = array('error' => 1, 'data'  => null);
        }
        return $data;
    }

    // Cargar temas v5
    public function cargar_temas_v5($carrera_id = null, $usuario_id)
    {
        if (is_null($carrera_id)) {
            $data = array('error' => 2, 'data' => null);
        } else {
            $curso_ids = Curricula::where('carrera_id', $carrera_id)->where('estado', 1)->pluck('curso_id');

            $temas_query = DB::table('posteos AS p')
                ->select(\DB::raw('p.id, p.curso_id, p.requisito_id, p.nombre, p.resumen, p.contenido, p.cod_video, p.imagen, p.archivo, p.video, p.evaluable, ca.modalidad AS cate_modali, p.tipo_ev, p.media'))
                ->join('categorias AS ca', 'p.categoria_id', '=', 'ca.id')
                ->where('p.estado', 1)
                ->whereIn('p.curso_id', $curso_ids)
                ->orderBy('p.orden', 'ASC');

            $temas = $temas_query->get();
            $temas_arr = [];

            foreach ($temas as $tema) {

                $tags = DB::table('tag_relationships AS p')
                    ->select('p.element_id', 't.nombre', 't.color')
                    ->leftJoin('tags AS t', 'p.tag_id', '=', 't.id')
                    ->where('p.element_type', 'tema')
                    ->where('p.element_id', $tema->id)
                    ->get();

                $actividad = DB::table('visitas AS p')
                    ->select('p.post_id', 'p.estado_tema')
                    ->where('p.usuario_id', $usuario_id)
                    ->where('p.estado_tema', '!=', '')
                    ->where('p.post_id', $tema->id)
                    ->get();

                $tema->tags = $tags;
                $tema->actividad = $actividad;
            }

            if (count($temas) > 0)
                $data = array('error' => 0, 'temas' => $temas);
            else
                $data = array('error' => 1, 'data'  => null);
        }
        return $data;
    }

    /**
     *   Lista todos los temas libres
     */
    public function temasLibres($config_id)
    {
        if (is_null($config_id)) {
            $data = array('error' => 2, 'data' => null);
        } else {
            $temas = DB::table('post_electivos')->where('config_id', $config_id)->orderBy('nombre', 'ASC')->where('estado', 1)->get();
            if (count($temas) > 0)
                $data = array('error' => 0, 'data' => $temas);
            else
                $data = array('error' => 1, 'data'  => null);
        }
        return $data;
    }

    // Calcular posicion de ranking
    public function calcula_rank($usuario_id, $rank_user, $tot_completed_gen, $nota_prom_gen, $intentos)
    {
        //
        // SELECT    usuario_id, tot_completados, nota_prom, intentos, porcentaje, created_at, updated_at, rank,
        // @curRank := @curRank + 1 AS posrank
        // FROM      resumen_general AS t1, (SELECT @curRank := 0) AS t2
        // ORDER BY  tot_completados DESC, nota_prom DESC, intentos ASC, updated_at ASC;
        //

        $ranking = \DB::select("
                SELECT usuario_id, posrank FROM (
                    SELECT usuario_id, @curRank := @curRank + 1 AS posrank
                    FROM resumen_general AS t1, (SELECT @curRank := 0) AS t2
                    ORDER BY tot_completados DESC, nota_prom DESC, intentos ASC, updated_at ASC
                ) AS t3
                WHERE usuario_id = " . $usuario_id . "
        ");

        $col_ranking = collect($ranking)->first();
        // print_r($col_ranking);
        // \Log::info(json_encode($col_ranking));

        if ($col_ranking) {
            $rank_user = $col_ranking->posrank;
        }

        // \Log::info("U = ".$usuario_id." -> Nuevo rank = ".$rank_user);

        return $rank_user;
    }

    // Verifica si ya realizó o no la EVALUACION ABIERTA PARA ESTE CURSO
    public function verificaEvaluacionAbierta($user_id = null, $post_id = null)
    {

        if (is_null($post_id) && is_null($user_id)) {
            $response = array('error' => 2);
        } else {
            $existe = DB::table('ev_abiertas')
                ->where('posteo_id', $post_id)
                ->where('usuario_id', $user_id)
                ->first();
            if ($existe) {
                $response = array('error' => 0, 'status' => 'realizado', 'msg' => 'EV ABIERTA realizada');
            } else {
                $response = array('error' => 0, 'status' => 'pendiente', 'msg' => 'EV ABIERTA pendiente');
            }
        }
        return $response;
    }


    /**
     *   Cargar las pruebas que el usuario realizó
     */
    public function cargarPruebas($id_user = null)
    {
        if (is_null($id_user)) {
            $response = array('error' => 2, 'data' => null);
        } else {
            $select = Prueba::where('usuario_id', $id_user)->get();

            if (count($select) > 0)
                $response = array('error' => 0, 'data' => $select);
            else
                $response = array('error' => 1, 'data' => null);
        }
        return $response;
    }

    /**
     *   Cargar Ranking
     */
    public function cargarRankingGeneral($user_id)
    {

        $ranking = [];
        $ranking_usuario = [];

        $usuario = Usuario::select("config_id", "nombre")->find($user_id);

        // $ranking = \DB::select( \DB::raw("
        //                     SELECT u.id, u.nombre
        //                     FROM resumen_general p
        //                     INNER JOIN usuarios u ON p.usuario_id = u.id
        //                     WHERE u.rol = 'default'
        //                     AND u.config_id = '".$usuario->config_id."'
        //                     ORDER BY p.rank ASC, p.updated_at DESC
        //                     LIMIT 10
        //                 "));

        // $ranking_usuario = \DB::select( \DB::raw("
        //                     SELECT u.id, u.nombre, p.rank
        //                     FROM resumen_general p
        //                     INNER JOIN usuarios u ON p.usuario_id = u.id
        //                     WHERE p.usuario_id = ".$user_id
        //                 ));

        $ranking = \DB::select(\DB::raw("
                                SELECT u.id, u.nombre, p.rank
                                FROM usuarios u
                                INNER JOIN resumen_general p ON u.id = p.usuario_id
                                WHERE u.rol = 'default'
                                AND u.config_id = '" . $usuario->config_id . "'
                                ORDER BY p.tot_completados DESC, p.nota_prom DESC, p.intentos ASC, p.updated_at ASC
                                LIMIT 10
                    "));

        $ranking_usuario = \DB::select(\DB::raw("
                                SELECT usuario_id, nombre,  rank
                                FROM (
                                    SELECT usuario_id, nombre,  @rank := @rank + 1 AS rank
                                    FROM (
                                        SELECT p.usuario_id, u.nombre
                                        FROM usuarios u
                                        INNER JOIN resumen_general p ON u.id = p.usuario_id
                                        WHERE u.rol = 'default'
                                        AND u.config_id = '" . $usuario->config_id . "'
                                        ORDER BY p.tot_completados DESC, p.nota_prom DESC, p.intentos ASC, p.updated_at ASC
                                        ) AS t1, (SELECT @rank := 0) AS t2
                                    ) AS t3
                                WHERE usuario_id = " . $user_id));


        // $rank_obj = collect($rank_res)->first();
        // $ranking_usuario[] = array('nombre'=>$usuario->nombre, 'rank'=>$rank_obj->rank);

        if (count($ranking) > 0)
            $data = array('error' => 0, 'data' => $ranking, 'data_usuario' => $ranking_usuario);
        else
            $data = array('error' => 1, 'data' => [], 'data_usuario' => []);

        return $data;
    }

    /**
     *   Cargar Ranking Zona
     */
    public function cargarRankingZona($user_id = null, $grupo)
    {
        // $ranking = \DB::select( \DB::raw("
        //     SELECT u.id, u.nombre
        //     FROM resumen_general p
        //     INNER JOIN usuarios u ON p.usuario_id = u.id
        //     WHERE u.rol = 'default'
        //     AND u.grupo = '".$grupo."'
        //     ORDER BY p.rank ASC, p.updated_at DESC
        //     LIMIT 10
        // "));

        // $ranking_usuario = \DB::select( \DB::raw("
        //     SELECT u.id, u.nombre, p.rank
        //     FROM resumen_general p
        //     INNER JOIN usuarios u ON p.usuario_id = u.id
        //     WHERE p.usuario_id = ".$user_id."
        //     AND u.grupo = '".$grupo."'"
        // ));

        $ranking = \DB::select(\DB::raw("
                                SELECT u.id, u.nombre, p.rank
                                FROM usuarios u
                                INNER JOIN resumen_general p ON u.id = p.usuario_id
                                WHERE u.rol = 'default'
                                AND u.grupo = '" . $grupo . "'
                                ORDER BY p.tot_completados DESC, p.nota_prom DESC, p.intentos ASC, p.updated_at ASC
                                LIMIT 10
                    "));

        $ranking_usuario = \DB::select(\DB::raw("
                                SELECT usuario_id, nombre,  rank
                                FROM (
                                    SELECT usuario_id, nombre,  @rank := @rank + 1 AS rank
                                    FROM (
                                        SELECT p.usuario_id, u.nombre
                                        FROM usuarios u
                                        INNER JOIN resumen_general p ON u.id = p.usuario_id
                                        WHERE u.rol = 'default'
                                        AND u.grupo = '" . $grupo . "'
                                        ORDER BY p.tot_completados DESC, p.nota_prom DESC, p.intentos ASC, p.updated_at ASC
                                        ) AS t1, (SELECT @rank := 0) AS t2
                                    ) AS t3
                                WHERE usuario_id = " . $user_id));


        if (count($ranking) > 0)
            $data = array('error' => 0, 'data' => $ranking, 'data_usuario' => $ranking_usuario);
        else
            $data = array('error' => 1, 'data' => [], 'data_usuario' => []);

        return $data;
    }

    /**
     *   Cargar Ranking Zona
     */
    public function cargarRankingBotica($user_id = null, $botica)
    {
        // $ranking = \DB::select( \DB::raw("
        //     SELECT u.id, u.nombre
        //     FROM resumen_general p
        //     INNER JOIN usuarios u ON p.usuario_id = u.id
        //     WHERE u.rol = 'default'
        //     AND u.botica = '".$botica."'
        //     ORDER BY p.rank ASC, p.updated_at DESC
        //     LIMIT 10
        // "));

        // $ranking_usuario = \DB::select( \DB::raw("
        //     SELECT u.id, u.nombre, p.rank
        //     FROM resumen_general p
        //     INNER JOIN usuarios u ON p.usuario_id = u.id
        //     WHERE p.usuario_id = ".$user_id."
        //     AND u.botica = '".$botica."'"
        // ));

        $ranking = \DB::select(\DB::raw("
                                SELECT u.id, u.nombre, p.rank
                                FROM usuarios u
                                INNER JOIN resumen_general p ON u.id = p.usuario_id
                                WHERE u.rol = 'default'
                                AND u.botica = '" . $botica . "'
                                ORDER BY p.tot_completados DESC, p.nota_prom DESC, p.intentos ASC, p.updated_at ASC
                                LIMIT 10
                    "));

        $ranking_usuario = \DB::select(\DB::raw("
                                SELECT usuario_id, nombre,  rank
                                FROM (
                                    SELECT usuario_id, nombre,  @rank := @rank + 1 AS rank
                                    FROM (
                                        SELECT p.usuario_id, u.nombre
                                        FROM usuarios u
                                        INNER JOIN resumen_general p ON u.id = p.usuario_id
                                        WHERE u.rol = 'default'
                                        AND u.botica = '" . $botica . "'
                                        ORDER BY p.tot_completados DESC, p.nota_prom DESC, p.intentos ASC, p.updated_at ASC
                                        ) AS t1, (SELECT @rank := 0) AS t2
                                    ) AS t3
                                WHERE usuario_id = " . $user_id));

        if (count($ranking) > 0)
            $data = array('error' => 0, 'data' => $ranking, 'data_usuario' => $ranking_usuario);
        else
            $data = array('error' => 1, 'data' => [], 'data_usuario' => []);

        return $data;
    }

    // lo uso
    public function obtenerArchivo($id = null)
    {
        if (is_null($id)) {
            $data = array('error' => 2, 'data' => null);
        } else {
            $archivo = DB::table('posteos')->select('archivo')->where('id', $id)->where('estado', 1)->limit(1)->get();
            if (count($archivo) > 0) {
                $file = $archivo[0]->archivo;
                $file = json_decode($file);
                $return = null;
                foreach ($file as $fil) {
                    $return = $fil->download_link;
                }
                $data = array('error' => 0, 'data' => $return);
            } else {
                $data = array('error' => 1, 'data' => null);
            }
        }
        return $data;
    }

    /* Listar cursos requeridos del usuario */
    /* LISTA LOS CURSOS QUE SON REQUISITOS QUE YA FUERON COMPLETADOS, POR ESO SIRVEN PARA DESCARTAR A LOS REQUISITOS POR DEFECTO (requisito_id) */
    // public function cursoRequerido( $user_id = null, $cate_id = null )
    // {
    //     if (is_null($user_id))
    //     {
    //         $data = array('error' => 2, 'data' => null );
    //     }
    //     else
    //     {
    //         $usuario = Usuario::find($user_id);

    //         $result_asignados = \DB::select( \DB::raw("
    //                         SELECT c.id, COUNT(curso_id) txc, c.nombre, c.requisito_id
    //                         FROM cursos AS c
    //                         INNER JOIN posteos AS p
    //                         ON c.id = p.curso_id
    //                         WHERE c.categoria_id = ".$cate_id."
    //                         AND p.evaluable = 'si'
    //                         AND c.estado = 1 AND p.estado = 1
    //                         AND c.id IN (SELECT curso_id FROM curso_perfil WHERE perfile_id = ".$usuario->perfil_id.")
    //                         GROUP BY p.curso_id
    //                         ORDER BY c.orden
    //                     "));


    //         $cant_aprobados = 0;
    //         //
    //         $arr_aprobados = array();

    //         foreach($result_asignados as $curso) {
    //             // aprob
    //             $aprobados = DB::table('pruebas AS u')
    //                             ->join('posteos AS po', 'po.id', '=', 'u.posteo_id')
    //                             ->select(DB::raw('COUNT(u.id) AS tot_aprobados, AVG(u.nota) AS nota_prom'))
    //                             ->where('u.resultado', 1)
    //                             ->where('po.estado', 1)
    //                             ->where('u.usuario_id', $user_id)
    //                             ->where('po.curso_id', $curso->id)
    //                             ->get();

    //             if ($aprobados->count() > 0) {
    //                 if ($curso->txc == intval($aprobados[0]->tot_aprobados) ) {

    //                     // Verifica si ha realizado Encuesta
    //                     $realiza_enc = DB::table('encuestas_respuestas')
    //                                                 ->select('curso_id')
    //                                                 ->where('usuario_id', $user_id)
    //                                                 ->where('curso_id', $curso->id)
    //                                                 ->get();

    //                     if (count($realiza_enc) > 0) {
    //                         array_push($arr_aprobados, $curso->id);
    //                     }

    //                 }
    //             }
    //         }
    //         //
    //         $requeridos = DB::table('cursos AS cu')
    //                         ->select('cu.id AS curso_id', 'cu.nombre', 'cu.requisito_id')
    //                         ->whereIn('cu.id', $arr_aprobados )
    //                         // ->whereNotNull('cu.requisito_id')
    //                         ->where('cu.estado', 1)
    //                         ->get();

    //         if ($requeridos){
    //             $data = array('error' => 0, 'data' => $requeridos);
    //         }
    //         else{
    //             $data = array('error' => 1, 'data' => null );
    //         }
    //     }
    //     return $data;
    // }

    /* Listar cursos requeridos del usuario */
    /* LISTA LOS CURSOS QUE SON REQUISITOS QUE YA FUERON COMPLETADOS, POR ESO SIRVEN PARA DESCARTAR A LOS REQUISITOS POR DEFECTO (requisito_id) */
    // INCLUYE EV ABIERTAS

    public function cursoRequerido_v6($user_id = null, $cate_id = null)
    {
        if (is_null($user_id)) {
            $data = array('error' => 2, 'data' => null);
        } else {
            $usuario = Usuario::find($user_id);

            // $result_asignados = \DB::select( \DB::raw("
            //                 SELECT c.id, COUNT(curso_id) txc, c.nombre, c.requisito_id
            //                 FROM cursos AS c
            //                 INNER JOIN posteos AS p
            //                 ON c.id = p.curso_id
            //                 WHERE c.categoria_id = ".$cate_id."
            //                 AND p.evaluable = 'si'
            //                 AND c.estado = 1 AND p.estado = 1
            //                 AND c.id IN (SELECT curso_id FROM curso_perfil WHERE perfile_id = ".$usuario->perfil_id.")
            //                 GROUP BY p.curso_id
            //                 ORDER BY c.orden
            //             "));
            $helper = new HelperController();
            $cursos_id = $helper->help_cursos_x_matricula_con_cursos_libre($user_id);
            // $cursos_id_str = implode(",", json_decode($cursos_id));
            $cursos_id_str = (isset($cursos_id)) ? implode(",", $cursos_id) : 0;

            // $result_asignados = \DB::select( \DB::raw("
            //         SELECT c.id, COUNT(curso_id) txc, c.nombre, c.requisito_id
            //         FROM cursos AS c
            //         INNER JOIN posteos AS p
            //         ON c.id = p.curso_id
            //         WHERE c.categoria_id = ".$cate_id."
            //         AND p.evaluable = 'si'
            //         AND c.estado = 1 AND p.estado = 1
            //         AND c.id IN (".$cursos_id_str.")
            //         GROUP BY p.curso_id
            //         ORDER BY c.orden
            //     "));

            $result_asignados = \DB::select(\DB::raw("
                SELECT c.id, COUNT(curso_id) txc, c.nombre, c.requisito_id
                FROM cursos AS c
                INNER JOIN posteos AS p
                ON c.id = p.curso_id
                WHERE c.categoria_id = " . $cate_id . "
                AND c.estado = 1 AND p.estado = 1
                AND c.id IN (" . $cursos_id_str . ")
                GROUP BY p.curso_id
                ORDER BY c.orden
            "));

            $cant_aprobados = 0;
            //
            $arr_aprobados = array();

            foreach ($result_asignados as $curso) {

                $cant_temas = intval($curso->txc);

                // APROBADOS CALIFICADOS
                $curso_aprob = DB::table('pruebas as pru')
                    ->select(DB::raw('COUNT(pru.id) AS tot_aprobados'))
                    ->join('posteos AS po', 'po.id', '=', 'pru.posteo_id')
                    ->where('pru.resultado', 1)
                    ->where('pru.usuario_id', $user_id)
                    ->where('pru.curso_id', $curso->id)
                    ->where('po.estado',1)
                    ->where('po.tipo_ev','calificada')
                    ->first();

                // APROBADOS ABIERTOS
                $aprobados_ab = DB::table('ev_abiertas as evas')
                    ->join('posteos AS po', 'po.id', '=', 'evas.posteo_id')
                    ->select(DB::raw('COUNT(evas.id) AS tot_aprobados'))
                    ->where('evas.usuario_id', $user_id)
                    ->where('evas.curso_id', $curso->id)
                    ->where('po.estado',1)
                    ->where('po.tipo_ev','abierta')
                    ->first();

                // REVISADOS (SIN EV)
                $revisados = DB::table('visitas as vi')
                    ->select(DB::raw('COUNT(vi.id) AS cant'))
                    ->join('posteos AS po', 'po.id', '=', 'vi.post_id')
                    ->where('vi.usuario_id', $user_id)
                    ->where('vi.curso_id', $curso->id)
                    ->where('vi.estado_tema', 'revisado')
                    ->where('po.estado',1)
                    ->where('po.evaluable','no')
                    ->first();

                $cant_aprobados1 = ($curso_aprob) ? intval($curso_aprob->tot_aprobados) : 0;
                $cant_aprobados2 = ($aprobados_ab) ? intval($aprobados_ab->tot_aprobados) : 0;
                $cant_revisados = ($revisados) ? intval($revisados->cant) : 0;

                $cant_aprobados = $cant_aprobados1 + $cant_aprobados2 + $cant_revisados;

                if ($cant_aprobados >= $cant_temas) {

                    // Verifica si ha realizado Encuesta
                    $realiza_enc = DB::table('encuestas_respuestas')
                        ->select('curso_id')
                        ->where('usuario_id', $user_id)
                        ->where('curso_id', $curso->id)
                        ->get();

                    if (count($realiza_enc) > 0) {
                        array_push($arr_aprobados, $curso->id);
                    }
                }
            }
            //
            $requeridos = DB::table('cursos')
                ->select('id AS curso_id', 'nombre', 'requisito_id')
                ->whereIn('id', $arr_aprobados)
                // ->whereNotNull('requisito_id')
                ->where('estado', 1)
                ->get();

            if ($requeridos) {
                $data = array('error' => 0, 'data' => $requeridos);
            } else {
                $data = array('error' => 1, 'data' => null);
            }
        }
        return $data;
    }

    /* Listar posteos requeridos del usuario */
    // public function posteoRequerido( $user_id = null, $curso_id = null )
    // {
    //     if (is_null($user_id))
    //     {
    //         $data = array('error' => 2, 'data' => null );
    //     }
    //     else
    //     {
    //         $requeridos = DB::table('posteos')
    //                         ->select('id AS posteo_id','requisito_id')
    //                         ->where('curso_id',$curso_id)
    //                         ->whereNotNull('requisito_id')
    //                         ->whereIn('requisito_id', DB::table('posteos AS pos')
    //                                                         ->join('pruebas AS pru', 'pos.id', '=', 'pru.posteo_id')
    //                                                         ->select('pos.id AS posteo_id')
    //                                                         ->where('pru.usuario_id', $user_id)
    //                                                         ->where('pru.resultado', 1)
    //                                                         ->where('pos.estado', 1)
    //                                 )
    //                         ->where('estado', 1)
    //                         ->get();

    //         if (count($requeridos) > 0)
    //         {
    //             $data = array('error' => 0, 'data' => $requeridos);
    //         }
    //         else
    //         {
    //             $data = array('error' => 1, 'data' => null );
    //         }
    //     }
    //     return $data;
    // }

    // public function posteoRequerido2( $user_id = null, $curso_id = null )
    // {
    //     if (is_null($user_id))
    //     {
    //         $data = array('error' => 2, 'data' => null );
    //     }
    //     else
    //     {
    //         $requeridos = DB::table('posteos')
    //                         ->select('id AS posteo_id','requisito_id')
    //                         ->where('curso_id',$curso_id)
    //                         // ->whereNotNull('requisito_id')
    //                         ->whereIn('id', DB::table('posteos AS pos')
    //                                                         ->join('pruebas AS pru', 'pos.id', '=', 'pru.posteo_id')
    //                                                         ->select('pos.id AS posteo_id')
    //                                                         ->where('pru.usuario_id', $user_id)
    //                                                         ->where('pru.resultado', 1)
    //                                                         ->where('pos.estado', 1)
    //                                 )
    //                         ->where('estado', 1)
    //                         ->get();

    //         if (count($requeridos) > 0)
    //         {
    //             $data = array('error' => 0, 'data' => $requeridos);
    //         }
    //         else
    //         {
    //             $data = array('error' => 1, 'data' => null );
    //         }
    //     }
    //     return $data;
    // }

    // EV ABIERTAS - lista los que ya fueron aprobados
    public function posteos_req_aprobados_v3($user_id = null, $curso_id = null)
    {
        if (is_null($user_id)) {
            $data = array('error' => 2, 'data' => null);
        } else {
            $requeridos = DB::table('posteos')
                ->select('id AS posteo_id', 'requisito_id')
                ->where('curso_id', $curso_id)
                ->whereIn('id', DB::table('ev_abiertas as evas')
                    ->join('posteos AS pos', 'pos.id', '=', 'evas.posteo_id')
                    ->select('evas.posteo_id')
                    ->where('evas.usuario_id', $user_id)
                    ->where('evas.curso_id', $curso_id)
                    ->where('pos.tipo_ev', 'abierta')->where('pos.estado',1))
                ->orwhereIn('id', DB::table('posteos AS pos')
                    ->join('pruebas AS pru', 'pos.id', '=', 'pru.posteo_id')
                    ->select('pos.id AS posteo_id')
                    ->where('pru.usuario_id', $user_id)
                    ->where('pru.resultado', 1)
                    ->where('pos.tipo_ev', 'calificada')->where('pos.estado',1))
                ->where('estado', 1)
                ->get();

            $revisados = DB::table('visitas as vis')
            ->join('posteos AS pos', 'pos.id', '=', 'vis.post_id')
            ->select(DB::raw('vis.post_id as posteo_id'), 'pos.requisito_id')
                ->where('vis.usuario_id', $user_id)
                ->where('vis.curso_id', $curso_id)
                ->where('vis.estado_tema', 'revisado')
                ->where('pos.estado', 1)->where('pos.evaluable', 'no')
                ->get();

            $temas_aprob = $requeridos->merge($revisados);

            if (count($temas_aprob) > 0) {
                $data = array('error' => 0, 'data' => $temas_aprob);
            } else {
                $data = array('error' => 1, 'data' => null);
            }
        }
        return $data;
    }



    // Obtiene la evaluacion pendiente
    // public function evalPendientes( $user_id = null, $curso_id = null )
    // {
    //     if (is_null($user_id))
    //     {
    //         $data = array('error' => 2, 'data' => null );
    //     }
    //     else
    //     {
    //         $temas = DB::table('posteos')
    //                         ->select('id', 'curso_id', 'requisito_id', 'nombre', 'resumen', 'contenido', 'cod_video', 'imagen', 'archivo', 'video', 'evaluable', 'tipo_ev')
    //                         ->where('curso_id', $curso_id)
    //                         ->whereNotIn('id', DB::table('posteos AS pos')
    //                                                         ->join('pruebas AS pru', 'pos.id', '=', 'pru.posteo_id')
    //                                                         ->select('pos.id AS posteo_id')
    //                                                         ->where('pru.usuario_id', $user_id)
    //                                                         ->where('pru.resultado', 1)
    //                                                         ->where('pos.estado', 1)
    //                                 )
    //                         ->where('evaluable', 'si')
    //                         ->where('estado', 1)
    //                         ->orderBy('orden')
    //                         ->first();

    //         // solo evaluaré con que ese post esté aprobado, si tiene una evaluación obviamente habrá realziado la encuesta.

    //         if ($temas){
    //             $data = array('error' => 0, 'data' => $temas);
    //         }
    //         else{
    //             $data = array('error' => 1, 'data' => null );
    //         }
    //     }
    //     return $data;
    // }

    // Obtiene la evaluacion pendiente, CON EV ABIERTAS
    public function evalPendientes2($user_id = null, $curso_id = null)
    {
        if (is_null($user_id)) {
            $data = array('error' => 2, 'data' => null);
        } else {
            // $temas = DB::table('posteos')
            //                 ->select('id', 'curso_id', 'requisito_id', 'nombre', 'resumen', 'contenido', 'cod_video', 'imagen', 'archivo', 'video', 'evaluable', 'tipo_ev')
            //                 ->where('curso_id', $curso_id)
            //                 ->whereNotIn('id', DB::table('ev_abiertas')
            //                                                 ->select('posteo_id')
            //                                                 ->where('usuario_id', $user_id)
            //                                                 ->where('curso_id', $curso_id)
            //                         )
            //                 ->whereNotIn('id', DB::table('posteos AS pos')
            //                                                 ->join('pruebas AS pru', 'pos.id', '=', 'pru.posteo_id')
            //                                                 ->select('pos.id AS posteo_id')
            //                                                 ->where('pru.usuario_id', $user_id)
            //                                                 ->where('pru.resultado', 1)
            //                                                 ->where('pos.estado', 1)
            //                         )
            //                 ->where('evaluable', 'si')
            //                 ->where('estado', 1)
            //                 ->orderBy('orden')
            //                 ->first();

            $temas = DB::table('posteos')
                ->select('id', 'curso_id', 'requisito_id', 'nombre', 'resumen', 'contenido', 'cod_video', 'imagen', 'archivo', 'video', 'evaluable', 'tipo_ev')
                ->where('curso_id', $curso_id)
                ->whereNotIn('id', DB::table('ev_abiertas as evas')
                    ->join('posteos AS pos', 'pos.id', '=', 'evas.posteo_id')
                    ->select('evas.posteo_id')
                    ->where('evas.usuario_id', $user_id)
                    ->where('evas.curso_id', $curso_id)
                    ->where('pos.tipo_ev', 'abierta')->where('pos.estado',1))
                ->whereNotIn('id', DB::table('posteos AS pos')
                    ->join('pruebas AS pru', 'pos.id', '=', 'pru.posteo_id')
                    ->select('pos.id AS posteo_id')
                    ->where('pru.usuario_id', $user_id)
                    ->where('pru.resultado', 1)
                    ->where('pos.tipo_ev', 'calificada')
                    ->where('pos.estado', 1)->where('pos.estado',1))
                ->whereNotIn('id', DB::table('posteos AS pos')
                    ->join('visitas AS v', 'pos.id', '=', 'v.post_id')
                    ->select('pos.id AS posteo_id')
                    ->where('v.usuario_id', $user_id)
                    ->where('estado_tema', 'revisado')
                    ->where('pos.estado', 1)
                    ->where('pos.evaluable', 'no')
                    )
                ->where('estado', 1)
                ->orderBy('orden')
                ->first();

            if ($temas) {
                $data = array('error' => 0, 'data' => $temas);
            } else {

                $data = array('error' => 1, 'data' => null);
            }
        }
        return $data;
    }

    // Obtiene la evaluacion pendiente
    public function encPendientes($user_id = null, $curso_id = null)
    {
        if (is_null($curso_id)) {
            $response = array('error' => 2, 'data' => null);
        } else {
            $select = DB::table('encuestas AS e')
                ->join('encuestas_respuestas AS x', 'e.id', '=', 'x.encuesta_id')
                ->select('e.id', 'e.titulo', 'e.anonima')
                ->where('x.curso_id', $curso_id)
                ->where('x.usuario_id', $user_id)
                ->where('e.estado', 1)
                ->groupBy('x.encuesta_id')
                ->get();

            if (count($select) > 0)
                $response = array('error' => 0, 'data' => $select);
            else
                $response = array('error' => 1, 'data' => null);
        }
        return $response;
    }

    // carga las encuestas que realizó un usuario
    public function usuarioEncuesta($user_id = null)
    {
        if (is_null($user_id)) {
            $response = array('error' => 2, 'data' => null);
        } else {
            $select = DB::table('encuestas AS e')
                ->join('encuestas_preguntas AS ep', 'e.id', '=', 'ep.encuesta_id')
                ->join('encuestas_respuestas AS er', 'ep.id', '=', 'er.pregunta_id')
                ->select('e.post_id')
                ->where('er.usuario_id', $user_id)
                ->where('e.estado', 1)
                ->groupBy('e.post_id')
                ->get();

            if (count($select) > 0)
                $response = array('error' => 0, 'data' => $select);
            else
                $response = array('error' => 1, 'data' => null);
        }
        return $response;
    }

    // cargar encuesta
    public function cargarEncuestaCurso($curso_id = null)
    {
        if (is_null($curso_id)) {
            $response = array('error' => 2, 'data' => null);
        } else {
            $select = DB::table('encuestas AS e')
                ->join('encuestas_preguntas AS ep', 'e.id', '=', 'ep.encuesta_id')
                ->select('e.id AS enc_id', 'ep.id', 'e.titulo', 'e.vigencia', 'ep.titulo AS titulo_pregunta', 'ep.tipo_pregunta', 'ep.opciones', 'e.anonima')
                ->whereIn('e.id', DB::table('curso_encuesta')
                    ->select('encuesta_id')
                    ->where('curso_id', $curso_id))
                ->where('e.estado', 1)
                ->where('ep.estado', 1)
                ->get();

            if (count($select) > 0)
                $response = array('error' => 0, 'data' => $select);
            else
                $response = array('error' => 1, 'data' => null);
        }
        return $response;
    }

    // cargar encuesta libre
    public function preguntasEncuestaLibre($enc_id = null)
    {
        if (is_null($enc_id)) {
            $response = array('error' => 2, 'data' => null);
        } else {
            $select = DB::table('encuestas AS e')
                ->join('encuestas_preguntas AS ep', 'e.id', '=', 'ep.encuesta_id')
                ->select('e.id AS enc_id', 'ep.id', 'e.titulo', 'e.vigencia', 'ep.titulo AS titulo_pregunta', 'ep.tipo_pregunta', 'ep.opciones', 'e.anonima')
                ->where('e.id', $enc_id)
                ->where('e.estado', 1)
                ->where('ep.estado', 1)
                ->get();

            if (count($select) > 0)
                $response = array('error' => 0, 'data' => $select);
            else
                $response = array('error' => 1, 'data' => null);
        }
        return $response;
    }

    // saber cuantos intentos me quedan por posteo
    public function saberIntentos($user_id = null, $post_id = null)
    {
        if (is_null($post_id) || is_null($user_id)) {
            $response = array('error' => 2, 'data' => null);
        } else {
            $select = DB::table('pruebas')
                ->select('intentos')
                ->where('posteo_id', $post_id)
                ->where('usuario_id', $user_id)
                ->first();

            // if (count($select) > 0){
            // $intentos = (count($select) > 0) ? 3 - intval($select->intentos) : 3;
            $intentos = ($select) ? $select->intentos : 0;
            $response = array('error' => 0, 'data' => $intentos);

            // }else{
            //     $response = array('error' => 1, 'data' => null);
            // }
        }
        return $response;
    }

    // vigencia del usuario
    public function vigencia($user_id = null)
    {
        if (is_null($user_id)) {
            $response = array('error' => 2, 'data' => null);
        } else {
            $vigencia = DB::table('usuarios AS u')
                ->join('usuario_vigencia AS uv', 'u.id', '=', 'uv.usuario_id')
                ->select('uv.*')
                ->where('u.id', $user_id)
                ->where('u.estado', 1)
                ->first();

            if ($vigencia) {
                date_default_timezone_set('America/Lima');

                $fecha_actual = new DateTime();
                $fecha_inicio = new DateTime($vigencia->fecha_inicio);
                $fecha_fin = new DateTime($vigencia->fecha_fin);
                $fecha_inicio_re = new DateTime($vigencia->fecha_inicio_reinsertado);
                $fecha_migrado = new DateTime($vigencia->fecha_migrado);

                if ($fecha_actual <= $fecha_fin && $fecha_actual >= $fecha_inicio) {
                    $quedan = $fecha_actual->diff($fecha_fin);
                    $quedan = $quedan->format('%d');
                } else {
                    $quedan = 0;
                }

                $response = array('error' => 0, 'data' => (object)array(
                    'quedan' => $quedan
                ));
            } else {
                $response = array('error' => 1, 'data' => null);
            }
        }
        return $response;
    }

    // PROGRESO RESUMEN
    // public function progresoResumen( $config_id = null, $user_id = null, $id_perfil = null ){
    //     if (is_null($id_perfil) || is_null($user_id)){
    //         $data = array('error' => 2, 'data' => null);
    //     }
    //     else{
    //         $config = DB::table('ab_config')->where('id', $config_id)->first();
    //         $mod_eval = json_decode($config->mod_evaluaciones, true);

    //         $result_asignados = $this->consultaAsignadosxCurso( $config_id, $user_id );

    //         $asignados = ( count($result_asignados['cursos']) > 0) ? count($result_asignados['cursos']) : 0;

    //         $cant_aprobados = 0;
    //         $cant_desa = 0;

    //         $temas_apro_curso = 0;
    //         $temas_desa_curso = 0;
    //         foreach ($result_asignados['cursos'] as $curso) {
    //             // aprob
    //             $aprobados = DB::table('pruebas')
    //                             ->select(DB::raw('COUNT(id) AS tot_aprobados'))
    //                             ->where('resultado', 1)
    //                             ->where('usuario_id', $user_id)
    //                             ->where('curso_id', $curso->id)
    //                             ->first();

    //             // APROBADOS ABIERTOS
    //             $aprobados_ab = DB::table('ev_abiertas')
    //                             ->select(DB::raw('COUNT(id) AS tot_aprobados'))
    //                             ->where('usuario_id', $user_id)
    //                             ->where('curso_id', $curso->id)
    //                             ->first();

    //             $cant_aprobados1 = ($aprobados) ? intval($aprobados->tot_aprobados) : 0;
    //             $cant_aprobados2 = ($aprobados_ab) ? intval($aprobados_ab->tot_aprobados) : 0;

    //             $cant_aprobados = $cant_aprobados1 + $cant_aprobados2;

    //             // $cant_aprobados = ($aprobados) ? intval($aprobados->tot_aprobados) : 0;

    //             if ($cant_aprobados >= $curso->txc) {
    //                 // Verifica si ha realizado Encuesta
    //                 $realiza_enc = DB::table('encuestas_respuestas')
    //                                             ->select('curso_id')
    //                                             ->where('usuario_id', $user_id)
    //                                             ->where('curso_id', $curso->id)
    //                                             ->get();

    //                 if (count($realiza_enc) > 0) {
    //                     $temas_apro_curso += 1;
    //                 }
    //             }

    //             //desaprob
    //             $desa = DB::table('pruebas AS u')
    //                             ->join('posteos AS po', 'po.id', '=', 'u.posteo_id')
    //                             ->select(DB::raw('COUNT(po.curso_id) AS tot_desaprobados'))
    //                             ->where('u.resultado', 0)
    //                             ->where('intentos', '>=', $mod_eval['nro_intentos'])
    //                             ->where('u.usuario_id', $user_id)
    //                             ->where('po.curso_id', $curso->id)
    //                             ->groupBy('po.curso_id')
    //                             ->first();

    //             $cant_desa = ($desa) ? intval($desa->tot_desaprobados) : 0;

    //             if ($curso->txc == $cant_desa) {
    //                 $temas_desa_curso += 1;
    //             }
    //         }

    //         if ($asignados > 0){
    //             $data = array('error' => 0, 'data' => array('asignados' => $asignados,
    //                                                         'aprobados' => $temas_apro_curso,
    //                                                         'desaprobados' => $temas_desa_curso)
    //                             );
    //         }
    //         else{
    //             $data = array('error' => 1, 'data' => null);
    //         }

    //     }
    //         return $data;
    // }

    // PROGRESO RESUMEN
    public function progresoResumen_v6($config_id = null, $user_id = null, $id_perfil = null)
    {
        if (is_null($config_id) || is_null($user_id)) {
            $data = array('error' => 2, 'data' => null);
        } else {
            $config = DB::table('ab_config')->where('id', $config_id)->first();
            $mod_eval = json_decode($config->mod_evaluaciones, true);

            $result_asignados = $this->consultaAsignadosxCurso($config_id, $user_id);

            $asignados = (count($result_asignados['cursos']) > 0) ? count($result_asignados['cursos']) : 0;

            $cant_aprobados = 0;
            $cant_desa = 0;

            $temas_apro_curso = 0;
            $temas_desa_curso = 0;
            foreach ($result_asignados['cursos'] as $curso) {
                // aprob
                $aprobados = DB::table('pruebas')
                    ->select(DB::raw('COUNT(id) AS tot_aprobados'))
                    ->where('resultado', 1)
                    ->where('usuario_id', $user_id)
                    ->where('curso_id', $curso->id)
                    ->first();

                // APROBADOS ABIERTOS
                $aprobados_ab = DB::table('ev_abiertas')
                    ->select(DB::raw('COUNT(id) AS tot_aprobados'))
                    ->where('usuario_id', $user_id)
                    ->where('curso_id', $curso->id)
                    ->first();

                // REVISADOS (SIN EV)
                $revisados = DB::table('visitas')
                    ->select(DB::raw('COUNT(id) AS cant'))
                    ->where('usuario_id', $user_id)
                    ->where('curso_id', $curso->id)
                    ->where('estado_tema', 'revisado')
                    ->first();

                $cant_aprobados1 = ($aprobados) ? intval($aprobados->tot_aprobados) : 0;
                $cant_aprobados2 = ($aprobados_ab) ? intval($aprobados_ab->tot_aprobados) : 0;
                $cant_revisados = ($revisados) ? intval($revisados->cant) : 0;

                $cant_aprobados = $cant_aprobados1 + $cant_aprobados2 + $cant_revisados;

                // $cant_aprobados = ($aprobados) ? intval($aprobados->tot_aprobados) : 0;

                if ($cant_aprobados >= $curso->txc) {
                    // Verifica si ha realizado Encuesta
                    $realiza_enc = DB::table('encuestas_respuestas')
                        ->select('curso_id')
                        ->where('usuario_id', $user_id)
                        ->where('curso_id', $curso->id)
                        ->get();

                    if (count($realiza_enc) > 0) {
                        $temas_apro_curso += 1;
                    }
                }

                //desaprob
                $desa = DB::table('pruebas AS u')
                    ->join('posteos AS po', 'po.id', '=', 'u.posteo_id')
                    ->select(DB::raw('COUNT(po.curso_id) AS tot_desaprobados'))
                    ->where('u.resultado', 0)
                    ->where('intentos', '>=', $mod_eval['nro_intentos'])
                    ->where('u.usuario_id', $user_id)
                    ->where('po.curso_id', $curso->id)
                    ->groupBy('po.curso_id')
                    ->first();

                $cant_desa = ($desa) ? intval($desa->tot_desaprobados) : 0;

                if ($curso->txc == $cant_desa) {
                    $temas_desa_curso += 1;
                }
            }

            if ($asignados > 0) {
                $data = array('error' => 0, 'data' => array(
                    'asignados' => $asignados,
                    'aprobados' => $temas_apro_curso,
                    'desaprobados' => $temas_desa_curso
                ));
            } else {
                $data = array('error' => 1, 'data' => null);
            }
        }
        return $data;
    }

    // PROGRESO RESUMEN
    public function progresoResumen_v7($config_id = null, $user_id = null)
    {
        if (is_null($config_id) || is_null($user_id)) {
            $data = array('error' => 2, 'data' => null);
        } else {
            $config = DB::table('ab_config')->where('id', $config_id)->first();
            $mod_eval = json_decode($config->mod_evaluaciones, true);

            $result_asignados = $this->consultaAsignadosxCurso($config_id, $user_id);

            $asignados = (count($result_asignados['cursos']) > 0) ? count($result_asignados['cursos']) : 0;

            $cant_aprobados = 0;
            $cant_desa = 0;

            $temas_apro_curso = 0;
            $temas_desa_curso = 0;
            foreach ($result_asignados['cursos'] as $curso) {
                // aprob
                $aprobados = DB::table('pruebas')
                    ->select(DB::raw('COUNT(id) AS tot_aprobados'))
                    ->where('resultado', 1)
                    ->where('usuario_id', $user_id)
                    ->where('curso_id', $curso->id)
                    ->first();

                // APROBADOS ABIERTOS
                $aprobados_ab = DB::table('ev_abiertas')
                    ->select(DB::raw('COUNT(id) AS tot_aprobados'))
                    ->where('usuario_id', $user_id)
                    ->where('curso_id', $curso->id)
                    ->first();

                // REVISADOS (SIN EV)
                $revisados = DB::table('visitas')
                    ->select(DB::raw('COUNT(id) AS cant'))
                    ->where('usuario_id', $user_id)
                    ->where('curso_id', $curso->id)
                    ->where('estado_tema', 'revisado')
                    ->first();

                $cant_aprobados1 = ($aprobados) ? intval($aprobados->tot_aprobados) : 0;
                $cant_aprobados2 = ($aprobados_ab) ? intval($aprobados_ab->tot_aprobados) : 0;
                $cant_revisados = ($revisados) ? intval($revisados->cant) : 0;

                $cant_aprobados = $cant_aprobados1 + $cant_aprobados2 + $cant_revisados;

                // $cant_aprobados = ($aprobados) ? intval($aprobados->tot_aprobados) : 0;

                if ($cant_aprobados >= $curso->txc) {
                    // Verifica si ha realizado Encuesta
                    $realiza_enc = DB::table('encuestas_respuestas')
                        ->select('curso_id')
                        ->where('usuario_id', $user_id)
                        ->where('curso_id', $curso->id)
                        ->get();

                    if (count($realiza_enc) > 0) {
                        $temas_apro_curso += 1;
                    }
                }

                //desaprob
                $desa = DB::table('pruebas AS u')
                    ->join('posteos AS po', 'po.id', '=', 'u.posteo_id')
                    ->select(DB::raw('COUNT(po.curso_id) AS tot_desaprobados'))
                    ->where('u.resultado', 0)
                    ->where('intentos', '>=', $mod_eval['nro_intentos'])
                    ->where('u.usuario_id', $user_id)
                    ->where('po.curso_id', $curso->id)
                    ->groupBy('po.curso_id')
                    ->first();

                $cant_desa = ($desa) ? intval($desa->tot_desaprobados) : 0;

                if ($curso->txc == $cant_desa) {
                    $temas_desa_curso += 1;
                }
            }

            if ($asignados > 0) {
                $data = array('error' => 0, 'data' => array(
                    'asignados' => $asignados,
                    'aprobados' => $temas_apro_curso,
                    'desaprobados' => $temas_desa_curso
                ));
            } else {
                $data = array('error' => 1, 'data' => null);
            }
        }
        return $data;
    }

    // Todos los asignados sin restriccion, (EVALUABLES CALIFICADAS, EVALUABLES ABIERTAS, NO EVALUABLES)
    public function consultaAsignadosxCurso($config_id = null, $user_id)
    {
        $helper = new HelperController();
        $cursos_id = $helper->help_cursos_x_matricula_con_cursos_libre($user_id);
        // $cursos_id_str = implode(",", json_decode($cursos_id));
        $cursos_id_str = (isset($cursos_id)) ? implode(",", $cursos_id) : 0;

        // $result_cursos = \DB::select( \DB::raw("
        //                     SELECT c.id, COUNT(curso_id) txc, c.nombre, c.requisito_id, c.categoria_id
        //                     FROM cursos AS c
        //                     INNER JOIN posteos AS p
        //                     ON c.id = p.curso_id
        //                     WHERE c.config_id = ".$config_id."
        //                     AND p.evaluable = 'si'
        //                     AND c.estado = 1 AND p.estado = 1
        //                     AND c.id IN (".$cursos_id_str.")
        //                     GROUP BY p.curso_id
        //                     ORDER BY c.categoria_id
        //                 "));

        $result_cursos = \DB::select(\DB::raw("
                        SELECT c.id, COUNT(curso_id) txc, c.nombre, c.requisito_id, c.categoria_id
                        FROM cursos AS c
                        INNER JOIN posteos AS p
                        ON c.id = p.curso_id
                        WHERE c.config_id = " . $config_id . "
                        AND c.estado = 1 AND p.estado = 1
                        AND c.id IN (" . $cursos_id_str . ")
                        GROUP BY p.curso_id
                        ORDER BY c.categoria_id
                    "));
        //AND p.id IN (SELECT posteo_id FROM posteo_perfil WHERE perfile_id = ".$id_perfil.")

        $result_temas = array();
        // $result_temas = Posteo::select('id')
        //             ->whereIn('curso_id', collect($result_cursos)->pluck('id') )
        //             ->whereIn('id', DB::table('posteo_perfil AS pp')
        //                             ->select('pp.posteo_id')
        //                             ->where('pp.perfile_id', $id_perfil)
        //                     )
        //             ->where('evaluable','=','si')
        //             ->where('estado', 1)
        //             ->orderBy('orden','ASC')
        //             ->get();

        return array('cursos' => $result_cursos, 'temas' => $result_temas);
    }

    public function consultaTemasNotas($curso_id, $user_id)
    {

        $temas = DB::table('pruebas AS u')
            ->join('posteos AS po', 'po.id', '=', 'u.posteo_id')
            ->select(DB::raw('po.nombre, u.nota'))
            ->where('u.usuario_id', $user_id)
            ->where('u.curso_id', $curso_id)
            ->get();

        $temas_ab = DB::table('ev_abiertas AS u')
            ->join('posteos AS po', 'po.id', '=', 'u.posteo_id')
            ->select(DB::raw('po.nombre'))
            ->where('u.usuario_id', $user_id)
            ->where('u.curso_id', $curso_id)
            ->get();
    }

    public function consultaTemasNotas_v6($curso_id, $user_id)
    {

        $temas = DB::table('pruebas AS u')
            ->join('posteos AS po', 'po.id', '=', 'u.posteo_id')
            ->select(DB::raw('po.nombre, u.nota'))
            ->where('u.usuario_id', $user_id)
            ->where('u.curso_id', $curso_id)
            ->get();

        $temas_ab = DB::table('ev_abiertas AS u')
            ->join('posteos AS po', 'po.id', '=', 'u.posteo_id')
            ->select(DB::raw('po.nombre'))
            ->where('u.usuario_id', $user_id)
            ->where('u.curso_id', $curso_id)
            ->get();
    }

    public function consultaTemasNotas_v7($curso_id, $user_id)
    {

        $temas = DB::table('pruebas AS u')
            ->join('posteos AS po', 'po.id', '=', 'u.posteo_id')
            ->select(DB::raw('po.nombre, u.nota'))
            ->where('u.usuario_id', $user_id)
            ->where('u.curso_id', $curso_id)
            ->get();

        $temas_ab = DB::table('ev_abiertas AS u')
            ->join('posteos AS po', 'po.id', '=', 'u.posteo_id')
            ->select(DB::raw('po.nombre'))
            ->where('u.usuario_id', $user_id)
            ->where('u.curso_id', $curso_id)
            ->get();

        // $data = $temas->merge($temas_ab);
        return array('calificadas' => $temas, 'abiertas' => $temas_ab);
    }

    // Temas asignados *(ya no intervienen los perfiles)
    public function consultaTemasAsignados($curso_id, $user_id)
    {

        $temas = DB::table('posteos AS po')
            ->leftJoin('pruebas AS u', function ($join) use ($user_id) {
                $join->on('po.id', '=', 'u.posteo_id')
                    ->where('u.usuario_id', "=", $user_id);
            })
            ->select(DB::raw('po.id, po.nombre, po.tipo_ev, u.nota'))
            ->where('po.curso_id', $curso_id)
            ->where('po.evaluable', '=', 'si')
            ->where('po.tipo_ev', '=', 'calificada')
            ->where('po.estado', 1)
            ->orderBy('po.orden')
            ->get();

        $temas_ab = DB::table('posteos AS po')
            ->leftJoin('ev_abiertas AS u', function ($join) use ($user_id) {
                $join->on('po.id', '=', 'u.posteo_id')
                    ->where('u.usuario_id', "=", $user_id);
            })
            ->select(DB::raw('po.id, po.nombre, po.tipo_ev, u.id existe_evab'))
            ->where('po.curso_id', $curso_id)
            ->where('po.evaluable', '=', 'si')
            ->where('po.tipo_ev', '=', 'abierta')
            ->where('po.estado', 1)
            ->orderBy('po.orden')
            ->get();

        $data = $temas->merge($temas_ab);
        return $data;
    }

    // temas asignados incluyendo EV ABIERTAS
    public function consultaTemasAsignados_v6($curso_id, $user_id)
    {

        $temas = DB::table('posteos AS po')
            ->leftJoin('pruebas AS u', function ($join) use ($user_id) {
                $join->on('po.id', '=', 'u.posteo_id')
                    ->where('u.usuario_id', "=", $user_id);
            })
            ->select(DB::raw('po.id, po.nombre, po.tipo_ev, u.nota'))
            ->where('po.curso_id', $curso_id)
            ->where('po.evaluable', '=', 'si')
            ->where('po.tipo_ev', '=', 'calificada')
            ->where('po.estado', 1)
            ->orderBy('po.orden')
            ->get();

        $temas_ab = DB::table('posteos AS po')
            ->leftJoin('ev_abiertas AS u', function ($join) use ($user_id) {
                $join->on('po.id', '=', 'u.posteo_id')
                    ->where('u.usuario_id', "=", $user_id);
            })
            ->select(DB::raw('po.id, po.nombre, po.tipo_ev, u.id existe_evab'))
            ->where('po.curso_id', $curso_id)
            ->where('po.evaluable', '=', 'si')
            ->where('po.tipo_ev', '=', 'abierta')
            ->where('po.estado', 1)
            ->orderBy('po.orden')
            ->get();

        $data = $temas->merge($temas_ab);
        return $data;
    }


    // progreso - cursos realizados
    // public function progresoCursosRealizados( $max_intentos = null, $user_id = null, $id_perfil = null, $config_id = null )
    // {
    //     if (is_null($user_id) || is_null($max_intentos))
    //     {
    //         $response = array('error' => 2, 'data' => null);
    //     }
    //     else
    //     {
    //         $config = DB::table('ab_config')->where('id', $config_id)->first();
    //         $mod_eval = json_decode($config->mod_evaluaciones, true);

    //         $result_asignados = $this->consultaAsignadosxCurso( $config_id, $user_id );

    //         $cant_aprobados = 0;
    //         $cant_desa = 0;
    //         //
    //         $arr_aprobados = array();
    //         $arr_intentar = array();
    //         $arr_desaprobados = array();

    //         foreach ($result_asignados['cursos'] as $curso) {

    //             $cate_obj = Categoria::select('color')->find($curso->categoria_id);
    //             $color = $cate_obj->color;

    //             $cant_temas_x_curso = $curso->txc;
    //             // APROBADOS CALIFICADOS
    //             $aprobados = DB::table('pruebas')
    //                             ->select(DB::raw('COUNT(id) AS tot_aprobados, AVG(nota) AS nota_prom'))
    //                             ->where('resultado', 1)
    //                             ->where('usuario_id', $user_id)
    //                             ->where('curso_id', $curso->id)
    //                             ->get();
    //             // APROBADOS ABIERTOS
    //             $aprobados_ab = DB::table('ev_abiertas')
    //                             ->select(DB::raw('COUNT(id) AS tot_aprobados'))
    //                             ->where('usuario_id', $user_id)
    //                             ->where('curso_id', $curso->id)
    //                             ->get();

    //             $cant_aprobados1 = $cant_aprobados2 = 0;

    //             if ($aprobados->count() > 0) {
    //                 $cant_aprobados1 =  intval($aprobados[0]->tot_aprobados);
    //             }
    //             if ($aprobados_ab->count() > 0) {
    //                 $cant_aprobados2 =  intval($aprobados_ab[0]->tot_aprobados);
    //             }
    //             $cant_aprobados = $cant_aprobados1 + $cant_aprobados2;

    //             if ($cant_aprobados >= $cant_temas_x_curso) {
    //                 // Verifica si ha realizado Encuesta
    //                 $realiza_enc = DB::table('encuestas_respuestas')
    //                                             ->select('curso_id')
    //                                             ->where('usuario_id', $user_id)
    //                                             ->where('curso_id', $curso->id)
    //                                             ->get();

    //                 if (count($realiza_enc) > 0) {
    //                     $temas = $this->consultaTemasNotas_v7($curso->id, $user_id);
    //                     $nota_prom = ($aprobados->count() > 0) ? $aprobados[0]->nota_prom : 0;
    //                     // $nota_prom = ($nota_prom > 0) ? $nota_prom : 0;
    //                     $arr = ['id'=>$curso->id, 'nombre'=>$curso->nombre, 'nota_prom'=>$nota_prom, 'requisito_id'=>$curso->requisito_id, 'color'=>$color, 'temas'=>$temas];

    //                     array_push($arr_aprobados, $arr);
    //                 }
    //             }

    //             // desaprob
    //             $desa = DB::table('pruebas AS u')
    //                             ->join('posteos AS po', 'po.id', '=', 'u.posteo_id')
    //                             ->select(DB::raw('COUNT(u.id) AS tot_desaprobados, AVG(u.nota) AS nota_prom, po.nombre, u.nota'))
    //                             ->where('u.resultado', 0)
    //                             ->where('intentos', '>=', $mod_eval['nro_intentos'])
    //                             ->where('u.usuario_id', $user_id)
    //                             ->where('po.curso_id', $curso->id)
    //                             ->get();

    //             if ($desa->count() > 0) {

    //                 $cant_desa =  intval($desa[0]->tot_desaprobados);

    //                 if ($cant_temas_x_curso == $cant_desa) {
    //                     $arr = ['id'=>$curso->id, 'nombre'=>$curso->nombre, 'nota_prom'=>$desa[0]->nota_prom, 'requisito_id'=>$curso->requisito_id, 'color'=>$color, 'temas'=>$desa];

    //                     //array_push($arr_desaprobados, $arr);
    //                 }
    //             }

    //         }

    //         ////
    //         if ($result_asignados){
    //             $response = array('error' => 0, 'data' => array(
    //                                                         'aprobados' => $arr_aprobados,
    //                                                         'desaprobados' => $arr_desaprobados,
    //                                                         'intentar' => $arr_intentar
    //                                                     ));
    //         }
    //         else
    //         {
    //             $response = array('error' => 1, 'data' => 3);
    //         }
    //     }
    //     return $response;
    // }

    // progreso - cursos realizados INCLUYE EV ABIERTAS - VERSION 0.0.6 - 22/01/20
    // public function progresoCursosRealizados_v6( $max_intentos = null, $user_id = null, $id_perfil = null, $config_id = null )
    // {
    //     if (is_null($user_id) || is_null($max_intentos))
    //     {
    //         $response = array('error' => 2, 'data' => null);
    //     }
    //     else
    //     {
    //         $config = DB::table('ab_config')->where('id', $config_id)->first();
    //         $mod_eval = json_decode($config->mod_evaluaciones, true);

    //         $result_asignados = $this->consultaAsignadosxCurso( $config_id, $user_id );

    //         $cant_aprobados = 0;
    //         $cant_desa = 0;
    //         //
    //         $arr_aprobados = array();
    //         $arr_intentar = array();
    //         $arr_desaprobados = array();

    //         foreach ($result_asignados['cursos'] as $curso) {

    //             $cate_obj = Categoria::select('color')->find($curso->categoria_id);
    //             $color = $cate_obj->color;

    //             $cant_temas_x_curso = $curso->txc;
    //             // APROBADOS CALIFICADOS
    //             $aprobados = DB::table('pruebas')
    //                             ->select(DB::raw('COUNT(id) AS tot_aprobados, AVG(nota) AS nota_prom'))
    //                             ->where('resultado', 1)
    //                             ->where('usuario_id', $user_id)
    //                             ->where('curso_id', $curso->id)
    //                             ->get();
    //             // APROBADOS ABIERTOS
    //             $aprobados_ab = DB::table('ev_abiertas')
    //                             ->select(DB::raw('COUNT(id) AS tot_aprobados'))
    //                             ->where('usuario_id', $user_id)
    //                             ->where('curso_id', $curso->id)
    //                             ->get();

    //             $cant_aprobados1 = $cant_aprobados2 = 0;

    //             if ($aprobados->count() > 0) {
    //                 $cant_aprobados1 =  intval($aprobados[0]->tot_aprobados);
    //             }
    //             if ($aprobados_ab->count() > 0) {
    //                 $cant_aprobados2 =  intval($aprobados_ab[0]->tot_aprobados);
    //             }
    //             $cant_aprobados = $cant_aprobados1 + $cant_aprobados2;

    //             if ($cant_aprobados >= $cant_temas_x_curso) {
    //                 // Verifica si ha realizado Encuesta
    //                 $realiza_enc = DB::table('encuestas_respuestas')
    //                                             ->select('curso_id')
    //                                             ->where('usuario_id', $user_id)
    //                                             ->where('curso_id', $curso->id)
    //                                             ->get();

    //                 if (count($realiza_enc) > 0) {
    //                     $temas = $this->consultaTemasNotas_v7($curso->id, $user_id);
    //                     $nota_prom = ($aprobados->count() > 0) ? $aprobados[0]->nota_prom : 0;
    //                     // $nota_prom = ($nota_prom > 0) ? $nota_prom : 0;
    //                     $arr = ['id'=>$curso->id, 'nombre'=>$curso->nombre, 'nota_prom'=>$nota_prom, 'requisito_id'=>$curso->requisito_id, 'color'=>$color, 'temas'=>$temas];

    //                     array_push($arr_aprobados, $arr);
    //                 }
    //             }

    //             // desaprob
    //             $desa = DB::table('pruebas AS u')
    //                             ->join('posteos AS po', 'po.id', '=', 'u.posteo_id')
    //                             ->select(DB::raw('COUNT(u.id) AS tot_desaprobados, AVG(u.nota) AS nota_prom, po.nombre, u.nota'))
    //                             ->where('u.resultado', 0)
    //                             ->where('intentos', '>=', $mod_eval['nro_intentos'])
    //                             ->where('u.usuario_id', $user_id)
    //                             ->where('po.curso_id', $curso->id)
    //                             ->get();

    //             if ($desa->count() > 0) {

    //                 $cant_desa =  intval($desa[0]->tot_desaprobados);

    //                 if ($cant_temas_x_curso == $cant_desa) {
    //                     $arr = ['id'=>$curso->id, 'nombre'=>$curso->nombre, 'nota_prom'=>$desa[0]->nota_prom, 'requisito_id'=>$curso->requisito_id, 'color'=>$color, 'temas'=>$desa];

    //                     //array_push($arr_desaprobados, $arr);
    //                 }
    //             }

    //         }

    //         ////
    //         if ($result_asignados){
    //             $response = array('error' => 0, 'data' => array(
    //                                                         'aprobados' => $arr_aprobados,
    //                                                         'desaprobados' => $arr_desaprobados,
    //                                                         'intentar' => $arr_intentar
    //                                                     ));
    //         }
    //         else
    //         {
    //             $response = array('error' => 1, 'data' => 3);
    //         }
    //     }
    //     return $response;
    // }

    // progreso - cursos realizados INCLUYE EV ABIERTAS - VERSION 7 - 23/01/20
    public function progresoCursosRealizados_v7($max_intentos = null, $user_id = null, $id_perfil = null, $config_id = null)
    {
        if (is_null($user_id) || is_null($max_intentos)) {
            $response = array('error' => 2, 'data' => null);
        } else {
            $config = DB::table('ab_config')->where('id', $config_id)->first();
            $mod_eval = json_decode($config->mod_evaluaciones, true);

            $result_asignados = $this->consultaAsignadosxCurso($config_id, $user_id);

            $cant_aprobados = 0;
            $cant_desa = 0;
            //
            $arr_aprobados = array();
            $arr_intentar = array();
            $arr_desaprobados = array();

            foreach ($result_asignados['cursos'] as $curso) {

                $cate_obj = Categoria::select('color')->find($curso->categoria_id);
                $color = $cate_obj->color;

                $cant_temas_x_curso = $curso->txc;
                // APROBADOS CALIFICADOS
                $aprobados = DB::table('pruebas')
                    ->select(DB::raw('COUNT(id) AS tot_aprobados, AVG(nota) AS nota_prom'))
                    ->where('resultado', 1)
                    ->where('usuario_id', $user_id)
                    ->where('curso_id', $curso->id)
                    ->get();
                // APROBADOS ABIERTOS
                $aprobados_ab = DB::table('ev_abiertas')
                    ->select(DB::raw('COUNT(id) AS tot_aprobados'))
                    ->where('usuario_id', $user_id)
                    ->where('curso_id', $curso->id)
                    ->get();

                // REVISADOS (SIN EV)
                $revisados = DB::table('visitas')
                    ->select(DB::raw('COUNT(id) AS cant'))
                    ->where('usuario_id', $user_id)
                    ->where('curso_id', $curso->id)
                    ->where('estado_tema', 'revisado')
                    ->get();

                $cant_aprobados1 = $cant_aprobados2 = $cant_revisados = 0;

                if ($aprobados->count() > 0) {
                    $cant_aprobados1 =  intval($aprobados[0]->tot_aprobados);
                }
                if ($aprobados_ab->count() > 0) {
                    $cant_aprobados2 =  intval($aprobados_ab[0]->tot_aprobados);
                }
                if ($revisados->count() > 0) {
                    $cant_revisados =  intval($revisados[0]->cant);
                }

                $cant_aprobados = $cant_aprobados1 + $cant_aprobados2 + $cant_revisados;
                // \Log::info('CURSO: '.$curso->id.' | ASIGNADOS: '.$cant_temas_x_curso.' ---- cant_aprobados1: '.$cant_aprobados1. ' | cant_aprobados2: '.$cant_aprobados2. ' | cant_revisados: '.$cant_revisados);

                if ($cant_aprobados >= $cant_temas_x_curso) {
                    // Verifica si ha realizado Encuesta
                    $realiza_enc = DB::table('encuestas_respuestas')
                        ->select('curso_id')
                        ->where('usuario_id', $user_id)
                        ->where('curso_id', $curso->id)
                        ->get();

                    if (count($realiza_enc) > 0) {
                        $temas = $this->consultaTemasNotas_v7($curso->id, $user_id);
                        $nota_prom = ($aprobados->count() > 0) ? $aprobados[0]->nota_prom : 0;
                        // $nota_prom = ($nota_prom > 0) ? $nota_prom : 0;
                        $arr = ['id' => $curso->id, 'nombre' => $curso->nombre, 'nota_prom' => $nota_prom, 'requisito_id' => $curso->requisito_id, 'color' => $color, 'temas' => $temas];

                        array_push($arr_aprobados, $arr);
                    }
                }

                // desaprob
                $desa = DB::table('pruebas AS u')
                    ->join('posteos AS po', 'po.id', '=', 'u.posteo_id')
                    ->select(DB::raw('COUNT(u.id) AS tot_desaprobados, AVG(u.nota) AS nota_prom, po.nombre, u.nota'))
                    ->where('u.resultado', 0)
                    ->where('intentos', '>=', $mod_eval['nro_intentos'])
                    ->where('u.usuario_id', $user_id)
                    ->where('po.curso_id', $curso->id)
                    ->get();

                if ($desa->count() > 0) {

                    $cant_desa =  intval($desa[0]->tot_desaprobados);

                    if ($cant_desa >= $cant_temas_x_curso) {
                        $arr = ['id' => $curso->id, 'nombre' => $curso->nombre, 'nota_prom' => $desa[0]->nota_prom, 'requisito_id' => $curso->requisito_id, 'color' => $color, 'temas' => $desa];

                        array_push($arr_desaprobados, $arr);
                    }
                }
            }

            ////
            if ($result_asignados) {
                $response = array('error' => 0, 'data' => array(
                    'aprobados' => $arr_aprobados,
                    'desaprobados' => $arr_desaprobados,
                    'intentar' => $arr_intentar
                ));
            } else {
                $response = array('error' => 1, 'data' => 3);
            }
        }
        return $response;
    }

    // progreso - cursos realizados INCLUYE EV ABIERTAS - VERSION 8 - 24/07/20
    public function progresoCursosRealizados_v8($max_intentos = null, $user_id = null, $config_id = null)
    {
        if (is_null($user_id) || is_null($max_intentos)) {
            $response = array('error' => 2, 'data' => null);
        } else {
            $config = DB::table('ab_config')->where('id', $config_id)->first();
            $mod_eval = json_decode($config->mod_evaluaciones, true);

            $result_asignados = $this->consultaAsignadosxCurso($config_id, $user_id);

            $cant_aprobados = 0;
            $cant_desa = 0;
            //
            $arr_aprobados = array();
            $arr_intentar = array();
            $arr_desaprobados = array();

            foreach ($result_asignados['cursos'] as $curso) {

                $cate_obj = Categoria::select('color')->find($curso->categoria_id);
                $color = $cate_obj->color;

                $cant_temas_x_curso = $curso->txc;
                // APROBADOS CALIFICADOS
                $aprobados = DB::table('pruebas')
                    ->select(DB::raw('COUNT(id) AS tot_aprobados, AVG(nota) AS nota_prom'))
                    ->where('resultado', 1)
                    ->where('usuario_id', $user_id)
                    ->where('curso_id', $curso->id)
                    ->get();
                // APROBADOS ABIERTOS
                $aprobados_ab = DB::table('ev_abiertas')
                    ->select(DB::raw('COUNT(id) AS tot_aprobados'))
                    ->where('usuario_id', $user_id)
                    ->where('curso_id', $curso->id)
                    ->get();

                // REVISADOS (SIN EV)
                $revisados = DB::table('visitas')
                    ->select(DB::raw('COUNT(id) AS cant'))
                    ->where('usuario_id', $user_id)
                    ->where('curso_id', $curso->id)
                    ->where('estado_tema', 'revisado')
                    ->get();

                $cant_aprobados1 = $cant_aprobados2 = $cant_revisados = 0;

                if ($aprobados->count() > 0) {
                    $cant_aprobados1 =  intval($aprobados[0]->tot_aprobados);
                }
                if ($aprobados_ab->count() > 0) {
                    $cant_aprobados2 =  intval($aprobados_ab[0]->tot_aprobados);
                }
                if ($revisados->count() > 0) {
                    $cant_revisados =  intval($revisados[0]->cant);
                }

                $cant_aprobados = $cant_aprobados1 + $cant_aprobados2 + $cant_revisados;
                // \Log::info('CURSO: '.$curso->id.' | ASIGNADOS: '.$cant_temas_x_curso.' ---- cant_aprobados1: '.$cant_aprobados1. ' | cant_aprobados2: '.$cant_aprobados2. ' | cant_revisados: '.$cant_revisados);

                if ($cant_aprobados >= $cant_temas_x_curso) {
                    // Verifica si ha realizado Encuesta
                    $realiza_enc = DB::table('encuestas_respuestas')
                        ->select('curso_id')
                        ->where('usuario_id', $user_id)
                        ->where('curso_id', $curso->id)
                        ->get();

                    if (count($realiza_enc) > 0) {
                        $temas = $this->consultaTemasNotas_v7($curso->id, $user_id);
                        $nota_prom = ($aprobados->count() > 0) ? $aprobados[0]->nota_prom : 0;
                        // $nota_prom = ($nota_prom > 0) ? $nota_prom : 0;
                        $arr = ['id' => $curso->id, 'nombre' => $curso->nombre, 'nota_prom' => $nota_prom, 'requisito_id' => $curso->requisito_id, 'color' => $color, 'temas' => $temas];

                        array_push($arr_aprobados, $arr);
                    }
                }

                // desaprob
                $desa = DB::table('pruebas AS u')
                    ->join('posteos AS po', 'po.id', '=', 'u.posteo_id')
                    ->select(DB::raw('COUNT(u.id) AS tot_desaprobados, AVG(u.nota) AS nota_prom, po.nombre, u.nota'))
                    ->where('u.resultado', 0)
                    ->where('intentos', '>=', $mod_eval['nro_intentos'])
                    ->where('u.usuario_id', $user_id)
                    ->where('po.curso_id', $curso->id)
                    ->get();

                if ($desa->count() > 0) {

                    $cant_desa =  intval($desa[0]->tot_desaprobados);

                    if ($cant_desa >= $cant_temas_x_curso) {
                        $arr = ['id' => $curso->id, 'nombre' => $curso->nombre, 'nota_prom' => $desa[0]->nota_prom, 'requisito_id' => $curso->requisito_id, 'color' => $color, 'temas' => $desa];

                        array_push($arr_desaprobados, $arr);
                    }
                }
            }

            ////
            if ($result_asignados) {
                $response = array('error' => 0, 'data' => array(
                    'aprobados' => $arr_aprobados,
                    'desaprobados' => $arr_desaprobados,
                    'intentar' => $arr_intentar
                ));
            } else {
                $response = array('error' => 1, 'data' => 3);
            }
        }
        return $response;
    }


    // public function progresoCursosPendientes( $config_id = null, $user_id = null, $id_perfil = null )
    // {
    //     if ( is_null($user_id) || is_null($id_perfil) ){
    //         $response = array('error' => 2, 'data' => null);
    //     }
    //     else{
    //         // cursos pendientes
    //         $result_asignados = $this->consultaAsignadosxCurso( $config_id, $user_id );

    //         $query = $result_asignados['cursos'];

    //         $con_intentos = [];
    //         $sin_intentos = [];
    //         $incompletos = [];
    //         $sin_requisitos = [];
    //         $enc_pend = [];

    //         // verifica requisitos en consulta de pendientes
    //         if (count($query) > 0){
    //             foreach ($query as $curso){
    //                 // Para los requisitos

    //                 $cant_temas = intval($curso->txc);

    //                 // APROBADOS CALIFICADOS
    //                 $curso_aprob = DB::table('pruebas')
    //                                 ->select(DB::raw('COUNT(id) AS tot_aprobados'))
    //                                 ->where('resultado', 1)
    //                                 ->where('usuario_id', $user_id)
    //                                 ->where('curso_id', $curso->id)
    //                                 ->first();

    //                 // $aprobados = DB::table('pruebas')
    //                 //                 ->select(DB::raw('COUNT(id) AS tot_aprobados, AVG(nota) AS nota_prom'))
    //                 //                 ->where('resultado', 1)
    //                 //                 ->where('usuario_id', $user_id)
    //                 //                 ->where('curso_id', $curso->id)
    //                 //                 ->get();
    //                 // APROBADOS ABIERTOS
    //                 $aprobados_ab = DB::table('ev_abiertas')
    //                                 ->select(DB::raw('COUNT(id) AS tot_aprobados'))
    //                                 ->where('usuario_id', $user_id)
    //                                 ->where('curso_id', $curso->id)
    //                                 ->first();

    //                 $cant_aprobados1 = ($curso_aprob) ? intval($curso_aprob->tot_aprobados) : 0;
    //                 $cant_aprobados2 = ($aprobados_ab) ? intval($aprobados_ab->tot_aprobados) : 0;

    //                 $cant_aprobados = $cant_aprobados1 + $cant_aprobados2;

    //                 // $cant_aprobados = ($curso_aprob) ? intval($curso_aprob->tot_aprobados) : 0;

    //                 $requisito = DB::table('cursos')->select('id','nombre')->where('id', $curso->requisito_id)->first();
    //                 $cate_obj = Categoria::select('color')->find($curso->categoria_id);
    //                 $color = $cate_obj->color;

    //                 // Solo si ha aprobado todos los cursos validar si le falta Encuesta, sino guarda solo como curso que tiene intentos
    //                 if ($cant_aprobados >= $cant_temas) {
    //                     $hizo_encuesta = DB::table('encuestas_respuestas')
    //                                                 ->select('curso_id')
    //                                                 ->where('usuario_id',$user_id)
    //                                                 ->where('curso_id', $curso->id)
    //                                                 ->first();
    //                     if (empty($hizo_encuesta)) {

    //                         //temas
    //                         $temas = $this->consultaTemasAsignados_v6($curso->id, $user_id);

    //                         $arra_cur = array('nombre' => $curso->nombre, 'intentos' => null, 'requisito'=>$requisito, 'color'=>$color, 'temas'=>$temas);
    //                         $arra = (object) array_merge( (array) $curso, $arra_cur);

    //                         array_push($enc_pend, $arra);
    //                     }
    //                 }else if ($cant_aprobados > 0) {
    //                     //temas
    //                     $temas = $this->consultaTemasAsignados_v6($curso->id, $user_id);

    //                     $arra_cur = array('nombre' => $curso->nombre, 'intentos' => null, 'requisito'=>$requisito, 'color'=>$color, 'temas'=>$temas);
    //                     $arra = (object) array_merge( (array) $curso, $arra_cur);

    //                     array_push($con_intentos, $arra);
    //                 }else{

    //                     if ($curso->requisito_id != NULL) {

    //                         // consulta requisitos
    //                         $req2_posteos = DB::table('posteos')
    //                             ->select( DB::raw('COUNT(id) AS txc'))
    //                             ->where('evaluable', 'si')
    //                             ->where('estado', 1)
    //                             ->where('curso_id', $curso->requisito_id )
    //                             ->groupBy('curso_id')
    //                             ->first();

    //                         // $req2_p_abiertos = DB::table('ev_abiertas')
    //                         //     ->select(DB::raw('COUNT(id) AS tot_aprobados'))
    //                         //     ->where('curso_id', $curso->requisito_id)
    //                         //     ->first();

    //                         $req_cant1 = ($req2_posteos) ? intval($req2_posteos->txc) : 0;
    //                         // $req_cant2 = ($req2_p_abiertos) ? intval($req2_p_abiertos->tot_aprobados) : 0;

    //                         // $cant_post_req2 = $req_cant1 + $req_cant2;
    //                         $cant_post_req2 = $req_cant1;

    //                         $requi2_aprob = DB::table('pruebas')
    //                                     ->select(DB::raw('COUNT(id) AS tot_aprobados'))
    //                                     ->where('resultado', 1)
    //                                     ->where('usuario_id', $user_id)
    //                                     ->where('curso_id', $curso->requisito_id)
    //                                     ->first();

    //                         $requi2_aprob_abiertos = DB::table('ev_abiertas')
    //                                 ->select(DB::raw('COUNT(id) AS tot_aprobados'))
    //                                 ->where('usuario_id', $user_id)
    //                                 ->where('curso_id', $curso->requisito_id)
    //                                 ->first();

    //                         $req_apro_cant1 = ($requi2_aprob) ? intval($requi2_aprob->tot_aprobados) : 0;
    //                         $req_apro_cant2 = ($requi2_aprob_abiertos) ? intval($requi2_aprob_abiertos->tot_aprobados) : 0;

    //                         $r2_cant_aprob = $req_apro_cant1 + $req_apro_cant2;

    //                         $requisito = DB::table('cursos')->select('id','nombre')->where('id', $curso->requisito_id)->first();

    //                         // Consulta si aprobó el curso requisito
    //                         if ($cant_post_req2 == $r2_cant_aprob) {
    //                             $hizo_encuesta2 = DB::table('encuestas_respuestas')
    //                                                         ->select('curso_id')
    //                                                         ->where('usuario_id',$user_id)
    //                                                         ->where('curso_id', $curso->requisito_id)
    //                                                         ->first();
    //                             if (empty($hizo_encuesta2)) {
    //                                 $arra = (object)array_merge((array)$curso, array('nombre' => $curso->nombre, 'intentos' => 1000, 'requisito'=>$requisito, 'color'=>$color, 'temas'=>null));
    //                                 array_push($incompletos, $arra);
    //                             }else {
    //                                 $temas = $this->consultaTemasAsignados_v6($curso->id, $user_id);
    //                                 $arra = (object)array_merge((array)$curso, array('nombre' => $curso->nombre, 'intentos' => null, 'requisito'=>$requisito, 'color'=>$color, 'temas'=>$temas));
    //                                 array_push($sin_intentos, $arra);
    //                             }
    //                         }else if($r2_cant_aprob > 0){
    //                             $arra = (object)array_merge((array)$curso, array('nombre' => $curso->nombre, 'intentos' => null, 'requisito'=>$requisito, 'color'=>$color, 'temas'=>null, 'revision'=>$cant_post_req2."-".$r2_cant_aprob));
    //                             array_push($incompletos, $arra);
    //                         }else{
    //                             $arra = (object)array_merge((array)$curso, array('nombre' => $curso->nombre, 'intentos' => null, 'requisito'=>$requisito, 'color'=>$color, 'temas'=>null, 'revision'=>$cant_post_req2."-".$r2_cant_aprob));
    //                             array_push($incompletos, $arra);
    //                         }

    //                     }else{

    //                         //temas
    //                         $temas = $this->consultaTemasAsignados_v6($curso->id, $user_id);

    //                         $arra_cur = array('nombre' => $curso->nombre, 'intentos' => null, 'requisito'=>$requisito, 'color'=>$color, 'temas'=>$temas);
    //                         $arra = (object) array_merge( (array) $curso, $arra_cur);

    //                         array_push($sin_requisitos, $arra);
    //                     }
    //                 }

    //             }
    //         }

    //         ////
    //         if ($result_asignados){
    //             $response = array('error' => 0, 'data' => array(
    //                 'con_intentos' => $con_intentos,
    //                 'sin_intentos' => $sin_intentos,
    //                 'incompletos' => $incompletos,
    //                 'sin_requisitos' => $sin_requisitos,
    //                 'enc_pend' => $enc_pend
    //                 )
    //             );
    //         }
    //         else
    //         {
    //             $response = array('error' => 1, 'data' => 3);
    //         }



    //     }
    //     return $response;
    // }


    // public function progresoCursosPendientes2( $config_id = null, $user_id = null, $id_perfil = null )
    // {
    //     if ( is_null($user_id) || is_null($id_perfil) ){
    //         $response = array('error' => 2, 'data' => null);
    //     }
    //     else{
    //         // cursos pendientes
    //         $result_asignados = $this->consultaAsignadosxCurso( $config_id, $user_id );

    //         $query = $result_asignados['cursos'];

    //         $con_intentos = [];
    //         $sin_intentos = [];
    //         $incompletos = [];
    //         $sin_requisitos = [];
    //         $enc_pend = [];

    //         // verifica requisitos en consulta de pendientes
    //         if (count($query) > 0){
    //             foreach ($query as $curso){
    //                 // Para los requisitos

    //                 $cant_temas = intval($curso->txc);

    //                 // APROBADOS CALIFICADOS
    //                 $curso_aprob = DB::table('pruebas')
    //                                 ->select(DB::raw('COUNT(id) AS tot_aprobados'))
    //                                 ->where('resultado', 1)
    //                                 ->where('usuario_id', $user_id)
    //                                 ->where('curso_id', $curso->id)
    //                                 ->first();

    //                 // $aprobados = DB::table('pruebas')
    //                 //                 ->select(DB::raw('COUNT(id) AS tot_aprobados, AVG(nota) AS nota_prom'))
    //                 //                 ->where('resultado', 1)
    //                 //                 ->where('usuario_id', $user_id)
    //                 //                 ->where('curso_id', $curso->id)
    //                 //                 ->get();
    //                 // APROBADOS ABIERTOS
    //                 $aprobados_ab = DB::table('ev_abiertas')
    //                                 ->select(DB::raw('COUNT(id) AS tot_aprobados'))
    //                                 ->where('usuario_id', $user_id)
    //                                 ->where('curso_id', $curso->id)
    //                                 ->first();

    //                 $cant_aprobados1 = ($curso_aprob) ? intval($curso_aprob->tot_aprobados) : 0;
    //                 $cant_aprobados2 = ($aprobados_ab) ? intval($aprobados_ab->tot_aprobados) : 0;

    //                 $cant_aprobados = $cant_aprobados1 + $cant_aprobados2;

    //                 // $cant_aprobados = ($curso_aprob) ? intval($curso_aprob->tot_aprobados) : 0;

    //                 $requisito = DB::table('cursos')->select('id','nombre')->where('id', $curso->requisito_id)->first();
    //                 $cate_obj = Categoria::select('color')->find($curso->categoria_id);
    //                 $color = $cate_obj->color;

    //                 // Solo si ha aprobado todos los cursos validar si le falta Encuesta, sino guarda solo como curso que tiene intentos
    //                 if ($cant_aprobados >= $cant_temas) {
    //                     $hizo_encuesta = DB::table('encuestas_respuestas')
    //                                                 ->select('curso_id')
    //                                                 ->where('usuario_id',$user_id)
    //                                                 ->where('curso_id', $curso->id)
    //                                                 ->first();
    //                     if (empty($hizo_encuesta)) {

    //                         //temas
    //                         $temas = $this->consultaTemasAsignados_v6($curso->id, $user_id);

    //                         $arra_cur = array('nombre' => $curso->nombre, 'intentos' => null, 'requisito'=>$requisito, 'color'=>$color, 'temas'=>$temas);
    //                         $arra = (object) array_merge( (array) $curso, $arra_cur);

    //                         array_push($enc_pend, $arra);
    //                     }
    //                 }else if ($cant_aprobados > 0) {
    //                     //temas
    //                     $temas = $this->consultaTemasAsignados_v6($curso->id, $user_id);

    //                     $arra_cur = array('nombre' => $curso->nombre, 'intentos' => null, 'requisito'=>$requisito, 'color'=>$color, 'temas'=>$temas);
    //                     $arra = (object) array_merge( (array) $curso, $arra_cur);

    //                     array_push($con_intentos, $arra);
    //                 }else{

    //                     if ($curso->requisito_id != NULL) {

    //                         // consulta requisitos
    //                         $req2_posteos = DB::table('posteos')
    //                             ->select( DB::raw('COUNT(id) AS txc'))
    //                             ->where('evaluable', 'si')
    //                             ->where('estado', 1)
    //                             ->where('curso_id', $curso->requisito_id )
    //                             ->groupBy('curso_id')
    //                             ->first();

    //                         // $req2_p_abiertos = DB::table('ev_abiertas')
    //                         //     ->select(DB::raw('COUNT(id) AS tot_aprobados'))
    //                         //     ->where('curso_id', $curso->requisito_id)
    //                         //     ->first();

    //                         $req_cant1 = ($req2_posteos) ? intval($req2_posteos->txc) : 0;
    //                         // $req_cant2 = ($req2_p_abiertos) ? intval($req2_p_abiertos->tot_aprobados) : 0;

    //                         // $cant_post_req2 = $req_cant1 + $req_cant2;
    //                         $cant_post_req2 = $req_cant1;

    //                         $requi2_aprob = DB::table('pruebas')
    //                                     ->select(DB::raw('COUNT(id) AS tot_aprobados'))
    //                                     ->where('resultado', 1)
    //                                     ->where('usuario_id', $user_id)
    //                                     ->where('curso_id', $curso->requisito_id)
    //                                     ->first();

    //                         $requi2_aprob_abiertos = DB::table('ev_abiertas')
    //                                 ->select(DB::raw('COUNT(id) AS tot_aprobados'))
    //                                 ->where('usuario_id', $user_id)
    //                                 ->where('curso_id', $curso->requisito_id)
    //                                 ->first();

    //                         $req_apro_cant1 = ($requi2_aprob) ? intval($requi2_aprob->tot_aprobados) : 0;
    //                         $req_apro_cant2 = ($requi2_aprob_abiertos) ? intval($requi2_aprob_abiertos->tot_aprobados) : 0;

    //                         $r2_cant_aprob = $req_apro_cant1 + $req_apro_cant2;

    //                         $requisito = DB::table('cursos')->select('id','nombre')->where('id', $curso->requisito_id)->first();

    //                         // Consulta si aprobó el curso requisito
    //                         if ($cant_post_req2 == $r2_cant_aprob) {
    //                             $hizo_encuesta2 = DB::table('encuestas_respuestas')
    //                                                         ->select('curso_id')
    //                                                         ->where('usuario_id',$user_id)
    //                                                         ->where('curso_id', $curso->requisito_id)
    //                                                         ->first();
    //                             if (empty($hizo_encuesta2)) {
    //                                 $arra = (object)array_merge((array)$curso, array('nombre' => $curso->nombre, 'intentos' => 1000, 'requisito'=>$requisito, 'color'=>$color, 'temas'=>null));
    //                                 array_push($incompletos, $arra);
    //                             }else {
    //                                 $temas = $this->consultaTemasAsignados_v6($curso->id, $user_id);
    //                                 $arra = (object)array_merge((array)$curso, array('nombre' => $curso->nombre, 'intentos' => null, 'requisito'=>$requisito, 'color'=>$color, 'temas'=>$temas));
    //                                 array_push($sin_intentos, $arra);
    //                             }
    //                         }else if($r2_cant_aprob > 0){
    //                             $arra = (object)array_merge((array)$curso, array('nombre' => $curso->nombre, 'intentos' => null, 'requisito'=>$requisito, 'color'=>$color, 'temas'=>null, 'revision'=>$cant_post_req2."-".$r2_cant_aprob));
    //                             array_push($incompletos, $arra);
    //                         }else{
    //                             $arra = (object)array_merge((array)$curso, array('nombre' => $curso->nombre, 'intentos' => null, 'requisito'=>$requisito, 'color'=>$color, 'temas'=>null, 'revision'=>$cant_post_req2."-".$r2_cant_aprob));
    //                             array_push($incompletos, $arra);
    //                         }

    //                     }else{

    //                         //temas
    //                         $temas = $this->consultaTemasAsignados_v6($curso->id, $user_id);

    //                         $arra_cur = array('nombre' => $curso->nombre, 'intentos' => null, 'requisito'=>$requisito, 'color'=>$color, 'temas'=>$temas);
    //                         $arra = (object) array_merge( (array) $curso, $arra_cur);

    //                         array_push($sin_requisitos, $arra);
    //                     }
    //                 }

    //             }
    //         }

    //         ////
    //         if ($result_asignados){
    //             $response = array('error' => 0, 'data' => array(
    //                 'con_intentos' => $con_intentos,
    //                 'sin_intentos' => $sin_intentos,
    //                 'incompletos' => $incompletos,
    //                 'sin_requisitos' => $sin_requisitos,
    //                 'enc_pend' => $enc_pend
    //                 )
    //             );
    //         }
    //         else
    //         {
    //             $response = array('error' => 1, 'data' => 3);
    //         }



    //     }
    //     return $response;
    // }

    // app con porcentaje y progreso de colores
    // public function progresoCursosPendientes3( $config_id = null, $user_id = null, $id_perfil = null )
    // {
    //     if ( is_null($user_id) || is_null($id_perfil) ){
    //         $response = array('error' => 2, 'data' => null);
    //     }
    //     else{
    //         // cursos pendientes
    //         $result_asignados = $this->consultaAsignadosxCurso( $config_id, $user_id );

    //         $query = $result_asignados['cursos'];

    //         $con_intentos = [];
    //         $sin_intentos = [];
    //         $incompletos = [];
    //         $sin_requisitos = [];
    //         $enc_pend = [];

    //         // verifica requisitos en consulta de pendientes
    //         if (count($query) > 0){
    //             foreach ($query as $curso){
    //                 // Para los requisitos

    //                 $cant_temas = intval($curso->txc);

    //                 // APROBADOS CALIFICADOS
    //                 $curso_aprob = DB::table('pruebas')
    //                                 ->select(DB::raw('COUNT(id) AS tot_aprobados'))
    //                                 ->where('resultado', 1)
    //                                 ->where('usuario_id', $user_id)
    //                                 ->where('curso_id', $curso->id)
    //                                 ->first();

    //                 // $aprobados = DB::table('pruebas')
    //                 //                 ->select(DB::raw('COUNT(id) AS tot_aprobados, AVG(nota) AS nota_prom'))
    //                 //                 ->where('resultado', 1)
    //                 //                 ->where('usuario_id', $user_id)
    //                 //                 ->where('curso_id', $curso->id)
    //                 //                 ->get();
    //                 // APROBADOS ABIERTOS
    //                 $aprobados_ab = DB::table('ev_abiertas')
    //                                 ->select(DB::raw('COUNT(id) AS tot_aprobados'))
    //                                 ->where('usuario_id', $user_id)
    //                                 ->where('curso_id', $curso->id)
    //                                 ->first();

    //                 $cant_aprobados1 = ($curso_aprob) ? intval($curso_aprob->tot_aprobados) : 0;
    //                 $cant_aprobados2 = ($aprobados_ab) ? intval($aprobados_ab->tot_aprobados) : 0;

    //                 $cant_aprobados = $cant_aprobados1 + $cant_aprobados2;

    //                 // $cant_aprobados = ($curso_aprob) ? intval($curso_aprob->tot_aprobados) : 0;

    //                 $requisito = DB::table('cursos')->select('id','nombre')->where('id', $curso->requisito_id)->first();
    //                 $cate_obj = Categoria::select('color')->find($curso->categoria_id);
    //                 $color = $cate_obj->color;

    //                 // Solo si ha aprobado todos los cursos validar si le falta Encuesta, sino guarda solo como curso que tiene intentos
    //                 if ($cant_aprobados >= $cant_temas) {
    //                     $hizo_encuesta = DB::table('encuestas_respuestas')
    //                                                 ->select('curso_id')
    //                                                 ->where('usuario_id',$user_id)
    //                                                 ->where('curso_id', $curso->id)
    //                                                 ->first();
    //                     if (empty($hizo_encuesta)) {

    //                         //temas
    //                         $temas = $this->consultaTemasAsignados_v6($curso->id, $user_id);

    //                         $arra_cur = array('nombre' => $curso->nombre, 'intentos' => null, 'requisito'=>$requisito, 'color'=>$color, 'temas'=>$temas);
    //                         $arra = (object) array_merge( (array) $curso, $arra_cur);

    //                         array_push($enc_pend, $arra);
    //                     }
    //                 }else if ($cant_aprobados > 0) {
    //                     //temas
    //                     $temas = $this->consultaTemasAsignados_v6($curso->id, $user_id);

    //                     $arra_cur = array('nombre' => $curso->nombre, 'intentos' => null, 'requisito'=>$requisito, 'color'=>$color, 'temas'=>$temas);
    //                     $arra = (object) array_merge( (array) $curso, $arra_cur);

    //                     array_push($con_intentos, $arra);
    //                 }else{

    //                     if ($curso->requisito_id != NULL) {

    //                         // consulta requisitos
    //                         $req2_posteos = DB::table('posteos')
    //                             ->select( DB::raw('COUNT(id) AS txc'))
    //                             ->where('evaluable', 'si')
    //                             ->where('estado', 1)
    //                             ->where('curso_id', $curso->requisito_id )
    //                             ->groupBy('curso_id')
    //                             ->first();

    //                         // $req2_p_abiertos = DB::table('ev_abiertas')
    //                         //     ->select(DB::raw('COUNT(id) AS tot_aprobados'))
    //                         //     ->where('curso_id', $curso->requisito_id)
    //                         //     ->first();

    //                         $req_cant1 = ($req2_posteos) ? intval($req2_posteos->txc) : 0;
    //                         // $req_cant2 = ($req2_p_abiertos) ? intval($req2_p_abiertos->tot_aprobados) : 0;

    //                         // $cant_post_req2 = $req_cant1 + $req_cant2;
    //                         $cant_post_req2 = $req_cant1;

    //                         $requi2_aprob = DB::table('pruebas')
    //                                     ->select(DB::raw('COUNT(id) AS tot_aprobados'))
    //                                     ->where('resultado', 1)
    //                                     ->where('usuario_id', $user_id)
    //                                     ->where('curso_id', $curso->requisito_id)
    //                                     ->first();

    //                         $requi2_aprob_abiertos = DB::table('ev_abiertas')
    //                                 ->select(DB::raw('COUNT(id) AS tot_aprobados'))
    //                                 ->where('usuario_id', $user_id)
    //                                 ->where('curso_id', $curso->requisito_id)
    //                                 ->first();

    //                         $req_apro_cant1 = ($requi2_aprob) ? intval($requi2_aprob->tot_aprobados) : 0;
    //                         $req_apro_cant2 = ($requi2_aprob_abiertos) ? intval($requi2_aprob_abiertos->tot_aprobados) : 0;

    //                         $r2_cant_aprob = $req_apro_cant1 + $req_apro_cant2;

    //                         $requisito = DB::table('cursos')->select('id','nombre')->where('id', $curso->requisito_id)->first();

    //                         // Consulta si aprobó el curso requisito
    //                         if ($cant_post_req2 == $r2_cant_aprob) {
    //                             $hizo_encuesta2 = DB::table('encuestas_respuestas')
    //                                                         ->select('curso_id')
    //                                                         ->where('usuario_id',$user_id)
    //                                                         ->where('curso_id', $curso->requisito_id)
    //                                                         ->first();
    //                             if (empty($hizo_encuesta2)) {
    //                                 $arra = (object)array_merge((array)$curso, array('nombre' => $curso->nombre, 'intentos' => 1000, 'requisito'=>$requisito, 'color'=>$color, 'temas'=>null));
    //                                 array_push($incompletos, $arra);
    //                             }else {
    //                                 $temas = $this->consultaTemasAsignados_v6($curso->id, $user_id);
    //                                 $arra = (object)array_merge((array)$curso, array('nombre' => $curso->nombre, 'intentos' => null, 'requisito'=>$requisito, 'color'=>$color, 'temas'=>$temas));
    //                                 array_push($sin_intentos, $arra);
    //                             }
    //                         }else if($r2_cant_aprob > 0){
    //                             $arra = (object)array_merge((array)$curso, array('nombre' => $curso->nombre, 'intentos' => null, 'requisito'=>$requisito, 'color'=>$color, 'temas'=>null, 'revision'=>$cant_post_req2."-".$r2_cant_aprob));
    //                             array_push($incompletos, $arra);
    //                         }else{
    //                             $arra = (object)array_merge((array)$curso, array('nombre' => $curso->nombre, 'intentos' => null, 'requisito'=>$requisito, 'color'=>$color, 'temas'=>null, 'revision'=>$cant_post_req2."-".$r2_cant_aprob));
    //                             array_push($incompletos, $arra);
    //                         }

    //                     }else{

    //                         //temas
    //                         $temas = $this->consultaTemasAsignados_v6($curso->id, $user_id);

    //                         $arra_cur = array('nombre' => $curso->nombre, 'intentos' => null, 'requisito'=>$requisito, 'color'=>$color, 'temas'=>$temas);
    //                         $arra = (object) array_merge( (array) $curso, $arra_cur);

    //                         array_push($sin_requisitos, $arra);
    //                     }
    //                 }

    //             }
    //         }

    //         ////
    //         if ($result_asignados){
    //             $response = array('error' => 0, 'data' => array(
    //                 'con_intentos' => $con_intentos,
    //                 'sin_intentos' => $sin_intentos,
    //                 'incompletos' => $incompletos,
    //                 'sin_requisitos' => $sin_requisitos,
    //                 'enc_pend' => $enc_pend
    //                 )
    //             );
    //         }
    //         else
    //         {
    //             $response = array('error' => 1, 'data' => 3);
    //         }



    //     }
    //     return $response;
    // }

    // PROGRESO PENDIENTES - INCLUYE PARA EV ABIERTAS
    public function progresoCursosPendientes_v6($config_id = null, $user_id = null, $id_perfil = null)
    {
        if (is_null($config_id) || is_null($user_id)) {
            $response = array('error' => 2, 'data' => null);
        } else {
            // cursos pendientes
            $result_asignados = $this->consultaAsignadosxCurso($config_id, $user_id);

            $query = $result_asignados['cursos'];

            $con_intentos = [];
            $sin_intentos = [];
            $incompletos = [];
            $sin_requisitos = [];
            $enc_pend = [];

            // verifica requisitos en consulta de pendientes
            if (count($query) > 0) {
                foreach ($query as $curso) {
                    // Para los requisitos

                    $cant_temas = intval($curso->txc);

                    // APROBADOS CALIFICADOS
                    $curso_aprob = DB::table('pruebas')
                        ->select(DB::raw('COUNT(id) AS tot_aprobados'))
                        ->where('resultado', 1)
                        ->where('usuario_id', $user_id)
                        ->where('curso_id', $curso->id)
                        ->first();

                    // $aprobados = DB::table('pruebas')
                    //                 ->select(DB::raw('COUNT(id) AS tot_aprobados, AVG(nota) AS nota_prom'))
                    //                 ->where('resultado', 1)
                    //                 ->where('usuario_id', $user_id)
                    //                 ->where('curso_id', $curso->id)
                    //                 ->get();

                    // APROBADOS ABIERTOS
                    $aprobados_ab = DB::table('ev_abiertas')
                        ->select(DB::raw('COUNT(id) AS tot_aprobados'))
                        ->where('usuario_id', $user_id)
                        ->where('curso_id', $curso->id)
                        ->first();

                    // REVISADOS (SIN EV)
                    $revisados = DB::table('visitas')
                        ->select(DB::raw('COUNT(id) AS cant'))
                        ->where('usuario_id', $user_id)
                        ->where('curso_id', $curso->id)
                        ->where('estado_tema', 'revisado')
                        ->get();

                    $cant_aprobados1 = $cant_aprobados2 = $cant_revisados = 0;

                    $cant_aprobados1 = ($curso_aprob) ? intval($curso_aprob->tot_aprobados) : 0;
                    $cant_aprobados2 = ($aprobados_ab) ? intval($aprobados_ab->tot_aprobados) : 0;

                    if ($revisados->count() > 0) {
                        $cant_revisados =  intval($revisados[0]->cant);
                    }

                    $cant_aprobados = $cant_aprobados1 + $cant_aprobados2 + $cant_revisados;
                    // \Log::info('CURSO: '.$curso->id.' | ASIGNADOS: '.$cant_temas_x_curso.' ---- cant_aprobados1: '.$cant_aprobados1. ' | cant_aprobados2: '.$cant_aprobados2. ' | cant_revisados: '.$cant_revisados);

                    // $cant_aprobados = ($curso_aprob) ? intval($curso_aprob->tot_aprobados) : 0;

                    $requisito = DB::table('cursos')->select('id', 'nombre')->where('id', $curso->requisito_id)->first();
                    $cate_obj = Categoria::select('color')->find($curso->categoria_id);
                    $color = $cate_obj->color;

                    // Solo si ha aprobado todos los cursos validar si le falta Encuesta, sino guarda solo como curso que tiene intentos
                    if ($cant_aprobados >= $cant_temas) {
                        $hizo_encuesta = DB::table('encuestas_respuestas')
                            ->select('curso_id')
                            ->where('usuario_id', $user_id)
                            ->where('curso_id', $curso->id)
                            ->first();
                        if (empty($hizo_encuesta)) {

                            //temas
                            $temas = $this->consultaTemasAsignados_v6($curso->id, $user_id);

                            $arra_cur = array('nombre' => $curso->nombre, 'intentos' => null, 'requisito' => $requisito, 'color' => $color, 'temas' => $temas);
                            $arra = (object) array_merge((array) $curso, $arra_cur);

                            array_push($enc_pend, $arra);
                        }
                    } else if ($cant_aprobados > 0) {
                        //temas
                        $temas = $this->consultaTemasAsignados_v6($curso->id, $user_id);

                        $arra_cur = array('nombre' => $curso->nombre, 'intentos' => null, 'requisito' => $requisito, 'color' => $color, 'temas' => $temas);
                        $arra = (object) array_merge((array) $curso, $arra_cur);

                        array_push($con_intentos, $arra);
                    } else {

                        if ($curso->requisito_id != NULL) {

                            // consulta requisitos
                            $req2_posteos = DB::table('posteos')
                                ->select(DB::raw('COUNT(id) AS txc'))
                                ->where('evaluable', 'si')
                                ->where('estado', 1)
                                ->where('curso_id', $curso->requisito_id)
                                ->groupBy('curso_id')
                                ->first();

                            // $req2_p_abiertos = DB::table('ev_abiertas')
                            //     ->select(DB::raw('COUNT(id) AS tot_aprobados'))
                            //     ->where('curso_id', $curso->requisito_id)
                            //     ->first();

                            $req_cant1 = ($req2_posteos) ? intval($req2_posteos->txc) : 0;
                            // $req_cant2 = ($req2_p_abiertos) ? intval($req2_p_abiertos->tot_aprobados) : 0;

                            // $cant_post_req2 = $req_cant1 + $req_cant2;
                            $cant_post_req2 = $req_cant1;

                            $requi2_aprob = DB::table('pruebas')
                                ->select(DB::raw('COUNT(id) AS tot_aprobados'))
                                ->where('resultado', 1)
                                ->where('usuario_id', $user_id)
                                ->where('curso_id', $curso->requisito_id)
                                ->first();

                            $requi2_aprob_abiertos = DB::table('ev_abiertas')
                                ->select(DB::raw('COUNT(id) AS tot_aprobados'))
                                ->where('usuario_id', $user_id)
                                ->where('curso_id', $curso->requisito_id)
                                ->first();

                            // REVISADOS (SIN EV)
                            $revisados_requisito = DB::table('visitas')
                                ->select(DB::raw('COUNT(id) AS cant'))
                                ->where('usuario_id', $user_id)
                                ->where('curso_id', $curso->requisito_id)
                                ->where('estado_tema', 'revisado')
                                ->get();

                            $req_apro_cant1 = ($requi2_aprob) ? intval($requi2_aprob->tot_aprobados) : 0;
                            $req_apro_cant2 = ($requi2_aprob_abiertos) ? intval($requi2_aprob_abiertos->tot_aprobados) : 0;

                            if ($revisados_requisito->count() > 0) {
                                $cant_revisados =  intval($revisados_requisito[0]->cant);
                            }

                            $r2_cant_aprob = $req_apro_cant1 + $req_apro_cant2 + $cant_revisados;

                            $requisito = DB::table('cursos')->select('id', 'nombre')->where('id', $curso->requisito_id)->first();

                            // Consulta si aprobó el curso requisito
                            if ($cant_post_req2 == $r2_cant_aprob) {
                                $hizo_encuesta2 = DB::table('encuestas_respuestas')
                                    ->select('curso_id')
                                    ->where('usuario_id', $user_id)
                                    ->where('curso_id', $curso->requisito_id)
                                    ->first();
                                if (empty($hizo_encuesta2)) {
                                    $arra = (object)array_merge((array)$curso, array('nombre' => $curso->nombre, 'intentos' => 1000, 'requisito' => $requisito, 'color' => $color, 'temas' => null));
                                    array_push($incompletos, $arra);
                                } else {
                                    $temas = $this->consultaTemasAsignados_v6($curso->id, $user_id);
                                    $arra = (object)array_merge((array)$curso, array('nombre' => $curso->nombre, 'intentos' => null, 'requisito' => $requisito, 'color' => $color, 'temas' => $temas));
                                    array_push($sin_intentos, $arra);
                                }
                            } else if ($r2_cant_aprob > 0) {
                                $arra = (object)array_merge((array)$curso, array('nombre' => $curso->nombre, 'intentos' => null, 'requisito' => $requisito, 'color' => $color, 'temas' => null, 'revision' => $cant_post_req2 . "-" . $r2_cant_aprob));
                                array_push($incompletos, $arra);
                            } else {
                                $arra = (object)array_merge((array)$curso, array('nombre' => $curso->nombre, 'intentos' => null, 'requisito' => $requisito, 'color' => $color, 'temas' => null, 'revision' => $cant_post_req2 . "-" . $r2_cant_aprob));
                                array_push($incompletos, $arra);
                            }
                        } else {

                            //temas
                            $temas = $this->consultaTemasAsignados_v6($curso->id, $user_id);

                            $arra_cur = array('nombre' => $curso->nombre, 'intentos' => null, 'requisito' => $requisito, 'color' => $color, 'temas' => $temas);
                            $arra = (object) array_merge((array) $curso, $arra_cur);

                            array_push($sin_requisitos, $arra);
                        }
                    }
                }
            }

            ////
            if ($result_asignados) {
                $response = array('error' => 0, 'data' => array(
                    'con_intentos' => $con_intentos,
                    'sin_intentos' => $sin_intentos,
                    'incompletos' => $incompletos,
                    'sin_requisitos' => $sin_requisitos,
                    'enc_pend' => $enc_pend
                ));
            } else {
                $response = array('error' => 1, 'data' => 3);
            }
        }
        return $response;
    }

    // ACtualiza por CURSO
    public function actualizarEncuestaRespuestas($enc_id, $curso_id, $pregunta_id, $user_id, $tipo_pregunta, $respuestas)
    {
        $select = DB::table('encuestas_respuestas')
            ->select('id')
            ->where('encuesta_id', $enc_id)
            ->where('curso_id', $curso_id)
            ->where('pregunta_id', $pregunta_id)
            ->where('usuario_id', $user_id)
            ->limit(1)
            ->get();
        if (count($select) > 0) {
            $query = DB::table('encuestas_respuestas')
                ->where('id', $select[0]->id)
                ->update([
                    'tipo_pregunta' => $tipo_pregunta,
                    'respuestas' => $respuestas
                ]);
        } else {
            $query = DB::table('encuestas_respuestas')
                ->insert([
                    'encuesta_id' => $enc_id,
                    'curso_id' => $curso_id,
                    'pregunta_id' => $pregunta_id,
                    'usuario_id' => $user_id,
                    'tipo_pregunta' => $tipo_pregunta,
                    'respuestas' => $respuestas
                ]);
        }
        return $query;
    }

    public function cargarPreguntasFrecuentes()
    {
        $select = DB::table('preguntas_frec')->where('estado', 1)->orderBy('orden', 'ASC')->get();
        return array('error' => 0, 'data' => $select);
    }

    public function cargarAyuda()
    {
        $select = DB::table('ayudas')->where('estado', 1)->orderBy('orden', 'ASC')->get();
        return array('error' => 0, 'data' => $select);
    }


    public function realizoTodosCursos($user_id = null, $perfil_id = null)
    {
        $helper = new HelperController();
        $cursos_id = $helper->help_cursos_x_matricula_con_cursos_libre($user_id);

        // $posteos = DB::table('posteos AS p')
        //                     ->select(DB::raw('COUNT(p.id) AS tot_posteos'))
        //                     ->join('posteo_perfil AS pp','p.id','=','pp.posteo_id')
        //                     ->where('pp.perfile_id', $perfil_id)
        //                      ->where('p.estado', 1)
        //                      ->where('p.evaluable', 'si')
        //                      ->orderBy('p.orden','asc')
        //                      ->get();
        $posteos = DB::table('posteos AS p')
            ->select(DB::raw('COUNT(p.id) AS tot_posteos'))
            ->whereIn('p.curso_id', $cursos_id)
            ->where('p.estado', 1)
            ->where('p.evaluable', 'si')
            ->orderBy('p.orden', 'asc')
            ->get();


        $realizo = DB::table('pruebas AS pr')
            ->select(DB::raw('COUNT(pr.id) AS tot_pruebas'))
            ->where('pr.usuario_id', $user_id)
            ->where('pr.resultado', 1)
            ->get();
        $posteos = $posteos[0]->tot_posteos;
        $realizo = $realizo[0]->tot_pruebas;

        $certificado = ($realizo == $posteos) ? true : false;
        return array('posteos' => $posteos, 'realizo' => $realizo, 'certificado' => $certificado);
    }

    public function ultimoAcceso($user_id = 0)
    {
        date_default_timezone_set('America/Lima');
        $update = DB::table('usuarios')->where('id', $user_id)->update(['ultima_sesion' => date('Y-m-d H:i:s')]);
        return $update;
    }

    // Listar diplomas de usuario x curso
    // Listar diplomas de usuario x curso
    public function diplomasxCurso($user_id)
    {
        $help = New HelperController();
        $curs_id = $help->help_cursos_x_matricula_con_cursos_libre($user_id);

        $diploma_curso = DB::table('diplomas')
            ->select('curso_id', DB::raw('COUNT(posteo_id) AS aprob_x_curso'))
            ->where('usuario_id', $user_id)
            ->whereIn('curso_id',$curs_id)
            ->groupBy('curso_id')
            ->get();

        $data = [];

        if (count($diploma_curso) > 0) {
            foreach ($diploma_curso as $dip) {
                $arr_dip = [];
                $curso = DB::table('cursos')->select('nombre')
                ->where('id',$dip->curso_id)
                ->where('estado',1)->first();

                if($curso){
                    $arr_dip['curso_id'] = $dip->curso_id;
                    $arr_dip['nombre'] = $curso->nombre;
                    $arr_dip['ruta_ver'] = 'tools/ver_diploma/';
                    $arr_dip['ruta_descarga'] = 'tools/dnc/';
                    $data[] = $arr_dip;
                }
            }
        }
        return array('error' => 0, 'data' => $data);
    }


    /*
    * Verifica nota del usuario en el tema y inserta registro en tabla diplomas
    */
    public function actualizaDiplomas($id_user = null, $post_id = null)
    {

        // $result = DB::table('pruebas')
        //                 ->whereRaw(" usuario_id = '".$id_user."' AND resultado = 1 AND posteo_id = '".$post_id."' ")
        //                 ->first();

        // if ($result) {

        //     $curso = DB::table('posteos')
        //                 ->select('curso_id')
        //                 ->where('id', $post_id)
        //                 ->first();

        //     $curso_id = NULL;
        //     if ($curso) {
        //         $curso_id = $curso->curso_id;

        //         // Verifica si ya existe registro en diploma X EL CURSO APROBADO
        //         $reg_dip = DB::table('diplomas')->where('usuario_id', $id_user)->where('posteo_id', $post_id)->first();

        //         $emi_fecha = date('Y-m-d');
        //         if (!$reg_dip) {
        //             DB::table('diplomas')->insert(array('usuario_id'=>$id_user, 'curso_id'=> $curso_id, 'posteo_id'=>$post_id, 'fecha_emision'=>$emi_fecha));
        //         }
        //     }


        // }

        $response = array('error' => 0);
        return $response;
    }

    /******************** ENVIAR MAILS *********************/

    public function correo_recuperar_pass($dni, $email)
    {
        $usu = DB::table('usuarios')
            ->select('id', 'email')
            ->where('dni', $dni)
            ->where('email', $email)
            ->where('estado', 1)
            ->first();

        if (!$usu) {
            return array('error' => 1, 'status' => 'Usuario no existe');
        }

        // Nuevo Pass
        $random = str_random(10);
        $newpass = Hash::make($random);

        // Actualiza pass
        $guardar = DB::table('usuarios')
            ->where('id', $usu->id)
            ->update(['password' => $newpass]);

        if ($guardar) {

            Mail::to($usu->email)
                ->bcc("deyvi@lamediadl.com")
                ->send(new RecuperarPass($random));

            return array('error' => 0, 'status' => 'Correo enviado');
        } else {
            return array('error' => 2, 'status' => 'Ha ocurrido un error al cambiar la contraseña');
        }
    }


    /****************** VISTAS **************/
    public function guarda_visitas_post($idvideo = null, $idusuario = null)
    {
        $existe = DB::table('visitas')
            ->select('id', 'sumatoria')
            ->where('post_id', $idvideo)
            ->where('usuario_id', $idusuario)
            ->limit(1)
            ->get();

        $id = 0;
        $vistos = 0;

        if (count($existe) > 0) {
            foreach ($existe as $row) {
                $id = $row->id;
                $vistos = $row->sumatoria;
            }
            if ($id > 0) {
                $vistos++;
                $query = DB::table('visitas')->where('id', $id)->update(array('sumatoria' => $vistos));
            }
        } else {
            $res = DB::table('posteos')->select('curso_id')->where('id', $idvideo)->first();
            $curso_id = ($res) ? $res->curso_id : NULL;

            $vistos++;
            $query = DB::table('visitas')->where('id', $id)->insert(array('sumatoria' => $vistos, 'curso_id' => $curso_id, 'post_id' => $idvideo, 'usuario_id' => $idusuario, 'descargas' => 0));
        }
        return array('error' => 0);
    }

    //
    public function descargarPostUsuario($idvideo = null, $idusuario = null)
    {
        $existe = DB::table('visitas')
            ->select('id', 'descargas')
            ->where('post_id', $idvideo)
            ->where('usuario_id', $idusuario)
            ->limit(1)
            ->get();

        $id = 0;
        $vistos = 0;

        if (count($existe) > 0) {
            foreach ($existe as $row) {
                $id = $row->id;
                $vistos = $row->descargas;
            }
            if ($id > 0) {
                $vistos++;
                $query = DB::table('visitas')->where('id', $id)->update(array('descargas' => $vistos));
            }
        } else {
            $res = DB::table('posteos')->select('curso_id')->where('id', $idvideo)->first();
            $curso_id = ($res) ? $res->curso_id : NULL;

            $vistos++;
            $query = DB::table('visitas')->where('id', $id)->insert(array('descargas' => $vistos, 'curso_id' => $curso_id, 'post_id' => $idvideo, 'usuario_id' => $idusuario, 'sumatoria' => 0));
        }
        return $data = array('error' => 0);
    }

    // Consultar Malla
    // public function VerMalla( $perfil_id = null, $config_id = null ){
    //     $sel = DB::table('perfil_malla')
    //                     ->select('archivo')
    //                     ->where('perfil_id',$perfil_id)
    //                     ->where('config_id',$config_id)
    //                     ->first();

    //     if ($sel)
    //         $rpta = array('error' => 0, 'data' => $sel);
    //     else
    //         $rpta = array('error' => 1, 'data' => null);

    //     return $rpta;
    // }

    // Consultar Malla
    public function ver_malla_v2($usuario_id = null)
    {

        // $ingreso = Ingreso::where('usuario_id', $usuario_id)->orderBy('id','DESC')->first();
        $matricula = Matricula::where('usuario_id', $usuario_id)->orderBy('id', 'DESC')->first();

        if ($matricula) {

            $sel = DB::table('carreras')
                ->select('malla_archivo')
                ->where('id', $matricula->carrera_id)
                ->first();

            if ($sel)
                $rpta = array('error' => 0, 'data' => $sel);
            else
                $rpta = array('error' => 1, 'data' => null);
        } else {
            $rpta = array('error' => 1, 'data' => null);
        }

        return $rpta;
    }

    public function glosario_v7($config_id)
    {
        $data = DB::table('glosario')->select('id', 'palabra', 'descripcion')->where('config_id', 'like', "%" . $config_id . "%")->where('estado', 1)->orderBy('palabra', 'ASC')->get();

        return array('error' => 0, 'data' => $data);
    }

    public function glosario_v8($config_id)
    {
        $data = DB::select("SELECT g.glo_cat_id, gc.nombre, g.palabra, g.descripcion
        FROM glosario as g
        inner join glosario_categoria as gc on g.glo_cat_id=gc.id
        where config_id like '%" . $config_id . "%' and estado=1");

        $Categorias = [];
        foreach ($data as $value) {
            if (!in_array($value->glo_cat_id, $Categorias)) {
                array_push($Categorias, $value->glo_cat_id);
            }
        }

        $Glosarios = [];

        foreach ($Categorias as $cat_id) {
            $object = (object)[];
            // Cada categoria que hay
            $found = [];
            foreach ($data as $value) {
                $newValue = [];

                if ($cat_id == $value->glo_cat_id) {
                    // return $value;
                    $newValue = [
                        "palabra" => $value->palabra,
                        "descripcion" => $value->descripcion,
                    ];
                    array_push($found, $newValue);
                    $foundNombre = $value->nombre;
                    $glo_cat_id = $value->glo_cat_id;
                }
            }

            $object = [
                'glo_cat_id' => $glo_cat_id,
                'nombre' => $foundNombre,
                'data' => $found,
            ];
            array_push($Glosarios, $object);
        }
        // return $Glosarios;
        return array('error' => 0, 'data' => $Glosarios);
    }
    // Guardar usuario uploads
    public function usuario_upload_link(Request $request)
    {
        #$user = auth('api')->user();
        // return $request;
        $response = array('error' => 3, 'msg' => 'Ha ocurrido un error');

        $usuario_id = strip_tags($request->input('usuario_id'));
        $link = strip_tags($request->input('link'));
        $description = strip_tags($request->input('description'));

        if (is_null($usuario_id)) {
            $response = array('error' => 2);
        } else {
            #set subwokspaces index
            #$subworkspace_id = $user->subworkspace_id;

            $array = array(
                #'subworkspace_id' => $subworkspace_id,
                'usuario_id' => $usuario_id,
                'link' => $link,
                'description' => $description
            );

            $result = Usuario_upload::create($array);
            // $result = DB::table('usuario_uploads')->insert($array);

            if ($result)
                $response = array('error' => 0);
            else
                $response = array('error' => 1);
        }
        return $response;
    }

    // Guardar usuario uploads
    public function usuario_upload_file(Request $request)
    {
        $user = auth()->user();
        if (is_null($request)) {
            $response = array('error' => true, 'error_msg' => 'No se recibieron datos');
        } else {

            // return $request;

            $usuario_id = $user->toArray()['id'];
            // $description = strip_tags($request->input('description'));
            $description = strip_tags($request->description);

            // check if exist file
            if(!$request->hasFile('file')) {
                return array('error' => true,
                             'error_msg' => 'El archivo no fue encontrado',
                             'details' => $request->all());
            }

            $file = $request->file('file');
            $ext = $file->getClientOriginalExtension();
            $namewithextension = $file->getClientOriginalName();
            $name = Str::slug(explode('.', $namewithextension)[0]);
            $name = $name . "_" . rand(100, 300);
            $fileName = $name . '.' . $ext;

            $path = 'usuario_archivos/' . $fileName;

            if (Storage::disk('s3')->put($path, file_get_contents($file), 'public')) {

                #set subwokspaces index
                $subworkspace_id = $user->subworkspace_id;

                $array = array(
                    'subworkspace_id' => $subworkspace_id,
                    'usuario_id' => $usuario_id,
                    'file' => $path,
                    'description' => $description
                );

                $result = Usuario_upload::create($array);
                // $result = DB::table('usuario_uploads')->insert($array);
            }

            if ($result)
                $response = array('error' => false, 'msg' => 'success');
            else
                $response = array('error' => true, 'error_msg' => 'Ha ocurrido un error');
        }
        return $response;
    }

    /****************************************************************************************** ESTRUCTURA CARRERA/CICLOS **************************************************************************************************/

    // Lista los ciclos del usuario + ciclo actual
    // public function ciclos($usuario_id){
    //     // $ingreso = DB::table('ingreso')->select('carrera_id')->where('usuario_id', $usuario_id)->where('estado', 1)->orderBy('id', 'DESC')->first();
    //     $matricula = Matricula::where('usuario_id', $usuario_id)->where('estado', 1)->orderBy('id','DESC')->first();
    //     if ($matricula) {
    //         // $carrera = DB::table('carreras')->where('id', $ingreso->carrera_id)->where('estado', 1)->orderBy('id', 'DESC')->first();

    //         $ciclos = DB::table('ciclos')->where('carrera_id', $matricula->carrera_id)->where('estado', 1)->get();

    //         $ciclos_matri = Matricula::select('ciclo_id')->where('usuario_id', $usuario_id)->where('estado', 1)->pluck('ciclo_id');

    //         // $data = array('error' => 0, 'carrera' => $carrera, 'ciclos' => $ciclos, 'ciclos_matri' => $ciclos_matri);
    //         $data = array('error' => 0, 'ciclos' => $ciclos, 'ciclos_matri' => $ciclos_matri);
    //     }else{
    //         $data = array('error' => 1, 'carrera' => null, 'ciclos' => null, 'ciclos_matri' => null);
    //     }

    //     return $data;
    // }

    // Lista las escuelas *(categorias) donde está matriculado el usuario
    public function escuelas_matricula($config_id, $usuario_id)
    {

        if ($usuario_id) {
            $helper = new HelperController();
            $cursos_id = $helper->help_cursos_x_matricula($usuario_id);

            $categorias = DB::table('categorias as c')
                ->select('c.id', 'c.config_id', 'c.modalidad', 'c.nombre', 'c.descripcion', 'c.imagen', 'c.color', 'c.en_menu_sec')
                ->join('cursos AS r', 'c.id', '=', 'r.categoria_id')
                // ->where('c.config_id', $config_id)
                ->whereIn('r.id', $cursos_id)
                ->where('c.estado', 1)
                ->where('r.estado', 1)
                ->orderBy('c.orden', 'ASC')
                ->groupBy('r.categoria_id')
                ->get();

            $data = array('error' => 0, 'categorias' => $categorias);
        } else {
            $data = array('error' => 1, 'categorias' => null);
        }

        return $data;
    }

    //
    public function cursos_x_escuela($config_id, $usuario_id)
    {

        if ($usuario_id) {

            $cursos_id = [];
            // $ingreso = DB::table('ingreso')->select('carrera_id')->where('usuario_id', $usuario_id)->where('estado', 1)->orderBy('id', 'DESC')->first();
            $ciclos_activos_carrera = Matricula::where('usuario_id', $usuario_id)->where('estado', 1)->orderBy('id', 'DESC')->pluck('ciclo_id');

            if (isset($ciclos_activos_carrera)) {
                // $ciclos_activos_carrera = DB::table('matricula AS c')
                //                         ->select('a.id')
                //                         ->join('ciclos AS a','a.id','=','c.ciclo_id')
                //                         ->where('c.usuario_id', $usuario_id)
                //                         ->where('a.carrera_id', $ingreso->carrera_id)
                //                         ->where('c.estado', 1)
                //                         ->pluck('a.id');
                // ->get();

                // $cate_arr = [];
                // foreach($ciclos_activos_carrera as $cic_car){
                // $cursos_arr = array('curso_id' => $catecu->curso_id, 'nombre'=>$catecu->nombre, 'descripcion'=>$catecu->descripcion, 'imagen'=>$catecu->imagen, 'requisito_id'=>$catecu->requisito_id, 'c_evaluable'=>$catecu->c_evaluable);

                $cate_cursos = DB::table('cursos AS c')
                    ->select('c.id', 'c.categoria_id', 'c.nombre', 'c.descripcion', 'c.imagen', 'c.requisito_id', 'c.c_evaluable', 'u.ciclo_id', 'i.nombre AS ciclo_nombre')
                    ->join('curricula AS u', 'u.curso_id', '=', 'c.id')
                    ->join('ciclos AS i', 'i.id', '=', 'u.ciclo_id')
                    ->whereIn('i.id', $ciclos_activos_carrera)
                    ->where('c.estado', 1)
                    ->orderBy('i.id', 'ASC')
                    ->orderBy('c.orden', 'ASC')
                    ->get();

                // $cate_arr[] = $cate_cursos;
                // }

                $data = array('error' => 0, 'data' => $cate_cursos);
            } else {
                $data = array('error' => 1, 'data' => null);
            }
        } else {
            $data = array('error' => 1, 'data' => null);
        }

        return $data;
    }

    // Helper : buscar cursos asignados por matricula en cierto ciclo
    public function help_cursos_x_matricula($usuario_id)
    {

        $cursos_id = [];
        // $ingreso = DB::table('ingreso')->select('carrera_id')->where('usuario_id', $usuario_id)->where('estado', 1)->orderBy('id', 'DESC')->first();
        // $ciclos_matri = DB::table('matricula')->select('ciclo_id')->where('usuario_id', $usuario_id)->where('estado', 1)->orderBy('id', 'DESC')->first();

        // $ciclos_activos_carrera = Matricula::where('usuario_id', $usuario_id)->where('estado', 1)->orderBy('id','DESC')->pluck('ciclo_id');

        // if (isset($ciclos_activos_carrera)){

        $result = DB::table('matricula AS m')
            ->select('c.curso_id')
            ->join('curricula AS c', 'c.ciclo_id', '=', 'm.ciclo_id')
            ->where('m.usuario_id', $usuario_id)
            // ->where('a.carrera_id', $ingreso->carrera_id)
            ->where('m.estado', 1)
            ->pluck('c.curso_id');

        if (!empty($result)) {
            $cursos_id = $result;
        }
        // $cursos_id = Curricula::select('curso_id')->whereIn('ciclo_id', $ciclos_activos_carrera)->where('estado', 1)->pluck('curso_id');
        // }

        return $cursos_id;
    }

    // public function cursos_x_escuela_bk($config_id, $usuario_id){

    //     $ingreso = DB::table('ingreso')->select('carrera_id')->where('usuario_id', $usuario_id)->where('estado', 1)->orderBy('id', 'DESC')->first();

    //     if ($ingreso) {
    //         $ciclo_matri = DB::table('matricula')->select('ciclo_id')->where('usuario_id', $usuario_id)->where('estado', 1)->orderBy('id', 'DESC')->first();

    //         $cursos_id = DB::table('curricula')->select('curso_id')->where('ciclo_id', $ciclo_matri->ciclo_id)->where('estado', 1)->pluck('curso_id');

    //         $cate_cursos = DB::table('cursos AS c')
    //                     ->select('a.id AS cate_id','a.nombre AS nombre_cate','c.id AS curso_id', 'c.nombre', 'c.descripcion','c.imagen','c.requisito_id', 'c.c_evaluable')
    //                     ->join('categorias AS a','a.id','=','c.categoria_id')
    //                      ->where('a.config_id', $config_id)
    //                      ->whereIn('c.id', $cursos_id)
    //                      ->where('c.estado', 1)
    //                      ->orderBy('a.orden','ASC')
    //                      ->get();

    //         // return $cate_cursos;

    //         $cate_arr = [];
    //         $cate_id = "";
    //         foreach($cate_cursos as $catecu){

    //             if ($cate_id != $catecu->cate_id) {
    //                 $cate_id = $catecu->cate_id;
    //                 $cate_arr[$cate_id] = array('cate_id' => $cate_id, 'nombre_cate'=>$catecu->nombre_cate);
    //             }

    //             $cursos_arr = array('curso_id' => $catecu->curso_id, 'nombre'=>$catecu->nombre, 'descripcion'=>$catecu->descripcion, 'imagen'=>$catecu->imagen, 'requisito_id'=>$catecu->requisito_id, 'c_evaluable'=>$catecu->c_evaluable);

    //             $cate_arr[$catecu->cate_id]['cursos'][] = $cursos_arr;
    //         }

    //         $data = array('error' => 0, 'data' => $cate_arr);
    //     }else{
    //         $data = array('error' => 1, 'data' => null);
    //     }

    //     return $data;
    // }

    // listar cursos por ciclo
    public function cursos_x_ciclo($ciclo_id)
    {

        $cursos_id = DB::table('curricula')->select('curso_id')->where('ciclo_id', $ciclo_id)->where('estado', 1)->pluck('curso_id');

        $cursos = DB::table('cursos AS c')
            ->select('c.id', 'c.categoria_id', 'c.nombre', 'c.descripcion', 'c.imagen', 'c.requisito_id', 'c_evaluable')
            ->where('c.estado', 1)
            ->whereIn('c.id', $cursos_id)
            ->orderBy('c.orden', 'ASC')
            ->get();

        if (count($cursos) > 0)
            $data = array('error' => 0, 'data' => $cursos);
        else
            $data = array('error' => 1, 'data' => null);
        return $data;
    }

    // Progreso general
    public function progreso_carrera($usuario_id, $carrera_id)
    {
        if (is_null($usuario_id) || is_null($carrera_id)) {
            $data = array('error' => 2, 'data' => null);
        } else {

            $ci_asignados = $ci_aprobados = $ci_pendientes = 0;

            $ci_asignados = DB::table('ciclos')->where('carrera_id', $carrera_id)->where('estado', 1)->count();
            $ci_aprobados = DB::table('matricula')->where('usuario_id', $usuario_id)->where('estado_ciclo', 1)->count();

            $ci_pendientes = $ci_asignados - $ci_aprobados;

            if ($ci_asignados > 0) {
                $data = array('error' => 0, 'data' => array(
                    'asignados' => $ci_asignados,
                    'aprobados' => $ci_aprobados,
                    'pendientes' => $ci_pendientes
                ));
            } else {
                $data = array('error' => 1, 'data' => null);
            }
        }
        return $data;
    }

    // Progreso general
    public function progreso_escuela_ciclos_v2($config_id, $usuario_id)
    {
        if (is_null($config_id) || is_null($usuario_id)) {
            $response = array('error' => 2, 'data' => null);
        } else {
            $config = DB::table('ab_config')->select('mod_evaluaciones')->where('id', $config_id)->first();
            $mod_eval = json_decode($config->mod_evaluaciones, true);
            //
            $ciclos_arr = Ciclo::pluck('nombre', 'id');

            $categorias = Categoria::where('config_id', $config_id)->orderBy('orden')->get();
            $matriculas = Matricula::where('usuario_id', $usuario_id)->where('estado', 1)->orderBy('ciclo_id')->get();

            $res_x_escuela = [];

            $arre = [];
            $arre2 = [];

            foreach ($categorias as $cate) {
                $res_x_ciclo = [];

                $cate_ciclos = DB::table('curricula AS c')
                    ->select('c.ciclo_id')
                    ->join('cursos AS u', 'u.id', '=', 'c.curso_id')
                    ->where('u.categoria_id', $cate->id)
                    ->whereIn('c.ciclo_id', $matriculas->pluck('ciclo_id'))
                    ->where('u.estado', 1)
                    ->groupBy('c.ciclo_id')
                    ->orderBy('c.ciclo_id')
                    ->get();

                if ($cate_ciclos->isNotEmpty()) {
                    // $arre[$cate->id] = $cate_ciclos;

                    foreach ($cate_ciclos as $cateciclo) {

                        $cursos_asignados = $this->cursos_asignados_x_ciclo_escuela($cateciclo->ciclo_id, $cate->id);
                        // $arre2[$cate->id] = $cursos_asignados;

                        $cic_asign = $cic_aprob = $cic_pend = 0;
                        $cic_asign = count($cursos_asignados);
                        $detalle_ciclo = [];
                        //

                        $aprobados = [];
                        $revisados = [];
                        $desaprobados = [];
                        $enc_pend = [];
                        $pendientes = [];
                        $pendiente_requisito = [];

                        // Verifica requisitos en consulta de pendientes
                        if (count($cursos_asignados) > 0) {
                            foreach ($cursos_asignados as $curso) {

                                $cate_obj = Categoria::select('color')->find($curso->categoria_id);
                                $color = $cate_obj->color;

                                // Para los requisitos
                                $cant_temas = intval($curso->txc);

                                // APROBADOS CALIFICADOS
                                $curso_aprob = DB::table('pruebas')
                                    ->select(DB::raw('COUNT(id) AS tot_aprobados, AVG(nota) AS nota_prom'))
                                    ->where('resultado', 1)
                                    ->where('usuario_id', $usuario_id)
                                    ->where('curso_id', $curso->id)
                                    ->first();

                                // APROBADOS ABIERTOS
                                $aprobados_ab = DB::table('ev_abiertas')
                                    ->select(DB::raw('COUNT(id) AS tot_aprobados'))
                                    ->where('usuario_id', $usuario_id)
                                    ->where('curso_id', $curso->id)
                                    ->first();

                                $cant_aprobados1 = $cant_aprobados2 = 0;
                                $cant_aprobados1 = ($curso_aprob) ? intval($curso_aprob->tot_aprobados) : 0;
                                $cant_aprobados2 = ($aprobados_ab) ? intval($aprobados_ab->tot_aprobados) : 0;

                                $cant_aprobados = $cant_aprobados1 + $cant_aprobados2;

                                $requisito = DB::table('cursos')->select('id', 'nombre')->where('id', $curso->requisito_id)->first();

                                // Solo si ha aprobado todos los cursos validar si le falta Encuesta, sino guarda solo como curso que tiene intentos
                                if ($cant_aprobados >= $cant_temas) {
                                    $hizo_encuesta = DB::table('encuestas_respuestas')
                                        ->select('curso_id')
                                        ->where('usuario_id', $usuario_id)
                                        ->where('curso_id', $curso->id)
                                        ->first();

                                    $temas = $this->temas_asignados_x_curso($curso->id, $usuario_id);

                                    if (empty($hizo_encuesta)) {

                                        $arra_cur = array('id' => $curso->id, 'nombre' => $curso->nombre, 'intentos' => null, 'requisito' => $requisito, 'color' => $color, 'temas' => $temas);
                                        $arra = (object) array_merge((array) $curso, $arra_cur);

                                        array_push($enc_pend, $arra);
                                    } else {
                                        $cic_asign++;

                                        $temas = $this->consultaTemasNotas_v7($curso->id, $usuario_id);
                                        $nota_prom = ($curso_aprob) ? $curso_aprob->nota_prom : 0;

                                        $arra_cur = array('id' => $curso->id, 'nombre' => $curso->nombre, 'nota_prom' => $nota_prom, 'intentos' => null, 'color' => $color, 'temas' => $temas);
                                        $arra = (object) array_merge((array) $curso, $arra_cur);

                                        array_push($aprobados, $arra);
                                    }

                                    // }else if ($cant_aprobados < $cant_temas && $cant_aprobados > 0 ) {
                                } else {

                                    // Verificar Desaprobados
                                    $desa = DB::table('pruebas AS u')
                                        ->join('posteos AS po', 'po.id', '=', 'u.posteo_id')
                                        ->select(DB::raw('COUNT(u.id) AS tot_desaprobados, AVG(u.nota) AS nota_prom, po.nombre, u.nota'))
                                        ->where('u.resultado', 0)
                                        ->where('intentos', '>=', $mod_eval['nro_intentos'])
                                        ->where('u.usuario_id', $usuario_id)
                                        ->where('po.curso_id', $curso->id)
                                        ->get();
                                    // dd($desa);
                                    if ($desa->isNotEmpty() && intval($desa[0]->tot_desaprobados) > 0) {
                                        // \Log::info('curso_id: '.$curso->id);
                                        $cant_desa =  intval($desa[0]->tot_desaprobados);
                                        if ($cant_desa >= $cant_temas) {
                                            $arr = ['id' => $curso->id, 'nombre' => $curso->nombre, 'nota_prom' => $desa[0]->nota_prom, 'requisito_id' => $curso->requisito_id, 'color' => $color, 'temas' => $desa];
                                            $arra = (object) array_merge((array) $curso, $arr);

                                            array_push($desaprobados, $arr);
                                        }
                                    } else {

                                        if ($curso->requisito_id != NULL) {
                                            // consulta requisitos
                                            $req2_posteos = DB::table('posteos')
                                                ->select(DB::raw('COUNT(id) AS txc'))
                                                ->where('evaluable', 'si')
                                                ->where('estado', 1)
                                                ->where('curso_id', $curso->requisito_id)
                                                ->groupBy('curso_id')
                                                ->first();

                                            $cant_post_asig2 = ($req2_posteos) ? intval($req2_posteos->txc) : 0;

                                            $requi2_aprob = DB::table('pruebas')
                                                ->select(DB::raw('COUNT(id) AS tot_aprobados'))
                                                ->where('resultado', 1)
                                                ->where('usuario_id', $usuario_id)
                                                ->where('curso_id', $curso->requisito_id)
                                                ->first();

                                            $requi2_aprob_abiertos = DB::table('ev_abiertas')
                                                ->select(DB::raw('COUNT(id) AS tot_aprobados'))
                                                ->where('usuario_id', $usuario_id)
                                                ->where('curso_id', $curso->requisito_id)
                                                ->first();

                                            $req_apro_cant1 = ($requi2_aprob) ? intval($requi2_aprob->tot_aprobados) : 0;
                                            $req_apro_cant2 = ($requi2_aprob_abiertos) ? intval($requi2_aprob_abiertos->tot_aprobados) : 0;

                                            $r2_cant_aprob = $req_apro_cant1 + $req_apro_cant2;

                                            $temas = $this->temas_asignados_x_curso($curso->id, $usuario_id);

                                            // Consulta si aprobó el curso requisito
                                            if ($cant_post_asig2 > 0 && $r2_cant_aprob >= $cant_post_asig2) {
                                                $hizo_encuesta2 = DB::table('encuestas_respuestas')
                                                    ->select('curso_id')
                                                    ->where('usuario_id', $usuario_id)
                                                    ->where('curso_id', $curso->requisito_id)
                                                    ->first();

                                                if (empty($hizo_encuesta2)) {
                                                    $arra = (object)array_merge((array)$curso, array('id' => $curso->id, 'nombre' => $curso->nombre, 'intentos' => 1000, 'requisito' => $requisito, 'color' => $color, 'temas' => null));
                                                    array_push($pendiente_requisito, $arra);
                                                } else {
                                                    $arra = (object)array_merge((array)$curso, array('id' => $curso->id, 'nombre' => $curso->nombre, 'intentos' => null, 'requisito' => $requisito, 'color' => $color, 'temas' => $temas));
                                                    array_push($pendientes, $arra);
                                                }
                                            } else if ($r2_cant_aprob < $cant_post_asig2) {
                                                $arra = (object)array_merge((array)$curso, array('id' => $curso->id, 'nombre' => $curso->nombre, 'intentos' => null, 'requisito' => $requisito, 'color' => $color, 'temas' => null, 'revision' => $cant_post_asig2 . "-" . $r2_cant_aprob));

                                                array_push($pendiente_requisito, $arra);
                                            } else {
                                                $arra = (object)array_merge((array)$curso, array('id' => $curso->id, 'nombre' => $curso->nombre, 'intentos' => null, 'requisito' => $requisito, 'color' => $color, 'temas' => $temas));
                                                array_push($pendientes, $arra);
                                            }
                                        } else {
                                            // Pendientes
                                            $temas = $this->temas_asignados_x_curso($curso->id, $usuario_id);

                                            $arra_cur = array('id' => $curso->id, 'nombre' => $curso->nombre, 'intentos' => null, 'requisito' => $requisito, 'color' => $color, 'temas' => $temas);
                                            $arra = (object) array_merge((array) $curso, $arra_cur);

                                            array_push($pendientes, $arra);
                                        }
                                    }
                                }

                                // DATOS DEL CICLO
                                $detalle_ciclo = array(
                                    'aprobados' => $aprobados,
                                    'revisados' => $revisados,
                                    'desaprobados' => $desaprobados,
                                    'enc_pend' => $enc_pend,
                                    'pendientes' => $pendientes,
                                    'pendiente_requisito' => $pendiente_requisito
                                );
                            }
                        } else {
                            break;
                        }

                        // data de ciclo
                        // $cic_pend = $cic_asign - $cic_aprob;
                        // $resumen = array('asign' => $cic_asign, 'aprob' => $cic_aprob, 'pend' => $cic_pend);

                        // $res_x_ciclo[] = array('ciclo'=> $cateciclo->ciclo_id, 'nombre'=> $cateciclo->ciclo_id, 'resumen' => $resumen, 'detalle_ciclo' => $detalle_ciclo);
                        $res_x_ciclo[] = array('ciclo_id' => $cateciclo->ciclo_id, 'nombre' => $ciclos_arr[$cateciclo->ciclo_id], 'detalle_ciclo' => $detalle_ciclo);
                    }
                    $res_x_escuela[] = array('escuela_id' => $cate->id, 'escuela' => $cate->nombre, 'detalle_escuela' => $res_x_ciclo);
                }
            }

            ////
            if ($res_x_escuela) {
                $response = array('error' => 0, 'data' => $res_x_escuela);
            } else {
                $response = array('error' => 1, 'data' => 3);
            }
        }
        return $response;
    }

    // Progreso general
    public function progreso_escuela_ciclos_v3($config_id, $usuario_id)
    {
        if (is_null($config_id) || is_null($usuario_id)) {
            $response = array('error' => 2, 'data' => null);
        } else {
            $config = DB::table('ab_config')->select('mod_evaluaciones')->where('id', $config_id)->first();
            $mod_eval = json_decode($config->mod_evaluaciones, true);
            //
            $ciclos_arr = Ciclo::pluck('nombre', 'id');
            $categorias_arr = Categoria::pluck('nombre', 'id');
            $matriculas_arr = Matricula::where('usuario_id', $usuario_id)->where('estado', 1)->orderBy('secuencia_ciclo')->pluck('ciclo_id');
            $catemodalidad_arr = Categoria::pluck('modalidad', 'id');
            // Matricula actual usuario
            $matricula_actual = Matricula::select('secuencia_ciclo', 'ciclo_id')->where('usuario_id', $usuario_id)->where('estado', 1)->orderBy('secuencia_ciclo')->first();

            // DB::enableQueryLog();
            $cursos_cate_ciclo = DB::table('curricula AS c')
                ->select('u.categoria_id', 'c.ciclo_id', 'c.curso_id', 'u.nombre', 'u.requisito_id')
                ->join('cursos AS u', 'u.id', '=', 'c.curso_id')
                ->join('categorias AS a', 'a.id', '=', 'u.categoria_id')
                ->whereIn('c.ciclo_id', $matriculas_arr)
                // ->where('u.config_id', $config_id)
                ->where('u.estado', 1)
                ->orderBy('a.orden')
                ->orderBy('u.orden')
                ->get();
            // $qry = DB::getQueryLog();
            // dd($qry);

            // Hack de la vida (aumenta un elemento a la colección, para que pueda funcionar el guardado de los RESUMENES)
            $elem_sin_valores = [
                "categoria_id" => 0,
                "ciclo_id" => 0,
                "curso_id" => 0,
                "nombre" => "",
                "requisito_id" => null
            ];
            $cursos_cate_ciclo->push((object) $elem_sin_valores);
            // dd($cursos_cate_ciclo);

            $res_x_escuela = [];
            $res_extracurri = [];
            $res_x_ciclo = [];
            $detalle_ciclo = [];

            if ($cursos_cate_ciclo->isNotEmpty()) {
                //
                $aprobados = [];
                $revisados = [];
                $desaprobados = [];
                $enc_pend = [];
                $pendientes = [];
                $pendiente_requisito = [];
                //
                $cate_aux = $ciclo_aux = 0;
                $estado_curso = "ninguno";

                foreach ($cursos_cate_ciclo as $curso_caci) {

                    $cant_temas_x_curso = $this->cant_temas_x_curso($curso_caci->curso_id);
                    if (isset($cant_temas_x_curso[0])) {

                        $cant_temas = intval($cant_temas_x_curso[0]->txc);

                        // ** RESUMENES ** //
                        // Resumen por ciclo (ANTES DE GUARDAR OTRO DATO DENTRO DE CADA ARRAY-ESTADO DE CURSO)
                        if ($curso_caci->categoria_id != $cate_aux || $curso_caci->ciclo_id != $ciclo_aux) {
                            if (!empty($aprobados)) {
                                $detalle_ciclo['aprobados'] = $aprobados;
                            }
                            if (!empty($revisados)) {
                                $detalle_ciclo['revisados'] = $revisados;
                            }
                            if (!empty($desaprobados)) {
                                $detalle_ciclo['desaprobados'] = $desaprobados;
                            }
                            if (!empty($enc_pend)) {
                                $detalle_ciclo['enc_pend'] = $enc_pend;
                            }
                            if (!empty($pendientes)) {
                                $detalle_ciclo['pendientes'] = $pendientes;
                            }
                            if (!empty($pendiente_requisito)) {
                                $detalle_ciclo['pendiente_requisito'] = $pendiente_requisito;
                            }

                            // dd($detalle_ciclo);
                            // print_r($detalle_ciclo);

                            if (!empty($detalle_ciclo)) {
                                $nombre_ciclo_forzado = $ciclos_arr[$ciclo_aux];
                                if($matricula_actual && $matricula_actual->secuencia_ciclo == 0){
                                    $nombre_ciclo_forzado = $ciclos_arr[$matricula_actual->ciclo_id];
                                }
                                $res_x_ciclo[] = array('ciclo_id' => $ciclo_aux, 'nombre' => $nombre_ciclo_forzado, 'detalle_ciclo' => $detalle_ciclo);
                                // Reinicio arrays
                                $detalle_ciclo = $aprobados =  $revisados = $desaprobados = $enc_pend = $pendientes = $pendiente_requisito = [];
                            }
                            // Empieza con nuevo ID cambiado
                            $ciclo_aux = $curso_caci->ciclo_id;
                        }

                        // Resumen por escuela
                        if ($curso_caci->categoria_id != $cate_aux) {  // si la categoria es diferente al auxiliar, guarda todo el acumulado en $res_x_ciclo en $res_x_cate, y reinicia $res_x_ciclo
                            if (!empty($res_x_ciclo)) {
                                if (isset($catemodalidad_arr[$cate_aux]) && $catemodalidad_arr[$cate_aux] == "regular")
                                    $res_x_escuela[] = array('escuela_id' => $cate_aux, 'escuela' => $categorias_arr[$cate_aux], 'detalle_escuela' => $res_x_ciclo);
                                else
                                    $res_extracurri[] = array('escuela_id' => $cate_aux, 'escuela' => $categorias_arr[$cate_aux], 'detalle_escuela' => $res_x_ciclo);
                                // Reinicio arrays
                                $res_x_ciclo = [];
                            }
                            // Empieza con nuevo ID cambiado
                            $cate_aux = $curso_caci->categoria_id;
                        }
                        // ** RESUMENES FIN ** //

                        ///////////// VERIFICA ESTADO DE CADA CURSO

                        // APROBADOS CALIFICADOS
                        $curso_aprob = DB::table('pruebas')
                            ->select(DB::raw('COUNT(id) AS tot_aprobados, AVG(nota) AS nota_prom'))
                            ->where('resultado', 1)
                            ->where('usuario_id', $usuario_id)
                            ->where('curso_id', $curso_caci->curso_id)
                            ->first();

                        // APROBADOS ABIERTOS
                        $aprobados_ab = DB::table('ev_abiertas')
                            ->select(DB::raw('COUNT(id) AS tot_aprobados'))
                            ->where('usuario_id', $usuario_id)
                            ->where('curso_id', $curso_caci->curso_id)
                            ->first();

                        // REVISADOS (SIN EV)
                        $cur_revisados = DB::table('visitas')
                            ->select(DB::raw('COUNT(id) AS cant'))
                            ->where('usuario_id', $usuario_id)
                            ->where('curso_id', $curso_caci->curso_id)
                            ->where('estado_tema', 'revisado')
                            ->first();

                        $cant_aprobados1 = $cant_aprobados2 = $cant_revisados = 0;

                        $cant_aprobados1 = ($curso_aprob) ? intval($curso_aprob->tot_aprobados) : 0;
                        $cant_aprobados2 = ($aprobados_ab) ? intval($aprobados_ab->tot_aprobados) : 0;
                        $cant_revisados = ($cur_revisados) ? intval($cur_revisados->cant) : 0;

                        $cant_aprobados = $cant_aprobados1 + $cant_aprobados2 + $cant_revisados;

                        $requisito = DB::table('cursos')->select('id', 'nombre')->where('id', $curso_caci->requisito_id)->first();
                        $temas = $this->temas_asignados_x_curso_v3($curso_caci->curso_id, $usuario_id);
                        // \Log::info($curso_caci->curso_id." - ".$curso_caci->nombre.' | CANTTEMAS: '.$cant_temas.' | CANTAPRO: '.$cant_aprobados.' | '.$temas);

                        // Solo si ha aprobado todos los cursos validar si le falta Encuesta, sino guarda solo como curso que tiene intentos
                        if ($cant_aprobados >= $cant_temas) {
                            $hizo_encuesta = DB::table('encuestas_respuestas')
                                ->select('curso_id')
                                ->where('usuario_id', $usuario_id)
                                ->where('curso_id', $curso_caci->curso_id)
                                ->first();

                            if (empty($hizo_encuesta)) {
                                $arra_cur = array('categoria_id' => $curso_caci->categoria_id, 'txc' => $cant_temas, 'id' => $curso_caci->curso_id, 'nombre' => $curso_caci->nombre, 'intentos' => null, 'requisito' => $requisito, 'temas' => $temas);
                                array_push($enc_pend, $arra_cur);
                            } else {
                                $nota_prom = ($curso_aprob) ? $curso_aprob->nota_prom : 0;

                                $arra_cur = array('categoria_id' => $curso_caci->categoria_id, 'txc' => $cant_temas, 'id' => $curso_caci->curso_id, 'nombre' => $curso_caci->nombre, 'nota_prom' => $nota_prom, 'intentos' => null, 'temas' => $temas);
                                array_push($aprobados, $arra_cur);
                            }

                            // }else if ($cant_aprobados < $cant_temas && $cant_aprobados > 0 ) {
                        } else {
                            // Verificar Desaprobados
                            $desa = DB::table('pruebas AS u')
                                ->join('posteos AS po', 'po.id', '=', 'u.posteo_id')
                                ->select(DB::raw('COUNT(u.id) AS tot_desaprobados, AVG(u.nota) AS nota_prom, po.nombre, u.nota'))
                                ->where('u.resultado', 0)
                                ->where('intentos', '>=', $mod_eval['nro_intentos'])
                                ->where('u.usuario_id', $usuario_id)
                                ->where('po.curso_id', $curso_caci->curso_id)
                                ->get();

                            if ($desa->isNotEmpty() && intval($desa[0]->tot_desaprobados) > 0) {
                                // \Log::info('curso_id: '.$curso_caci->curso_id);
                                $cant_desa =  intval($desa[0]->tot_desaprobados);
                                if ($cant_desa >= $cant_temas) {
                                    $arra_cur = ['categoria_id' => $curso_caci->categoria_id, 'txc' => $cant_temas, 'id' => $curso_caci->curso_id, 'nombre' => $curso_caci->nombre, 'nota_prom' => $desa[0]->nota_prom, 'requisito' => null, 'temas' => $desa];
                                    array_push($desaprobados, $arra_cur);
                                } else { // Si tiene desaprobados pero no ha desaprobado TODOS los temas de un CURSO
                                    $arra_cur = ['categoria_id' => $curso_caci->categoria_id, 'txc' => $cant_temas, 'id' => $curso_caci->curso_id, 'nombre' => $curso_caci->nombre, 'intentos' => null, 'requisito' => $requisito,  'temas' => $temas];
                                    array_push($pendientes, $arra_cur);
                                }
                            } else {

                                if ($curso_caci->requisito_id != NULL) {
                                    // consulta requisitos
                                    $req2_posteos = DB::table('posteos')
                                        ->select(DB::raw('COUNT(id) AS txc'))
                                        // ->where('evaluable', 'si')
                                        ->where('estado', 1)
                                        ->where('curso_id', $curso_caci->requisito_id)
                                        ->first();

                                    $cant_post_asig2 = ($req2_posteos) ? intval($req2_posteos->txc) : 0;

                                    $requi2_aprob = DB::table('pruebas')
                                        ->select(DB::raw('COUNT(id) AS tot_aprobados'))
                                        ->where('resultado', 1)
                                        ->where('usuario_id', $usuario_id)
                                        ->where('curso_id', $curso_caci->requisito_id)
                                        ->first();

                                    $requi2_aprob_abiertos = DB::table('ev_abiertas')
                                        ->select(DB::raw('COUNT(id) AS tot_aprobados'))
                                        ->where('usuario_id', $usuario_id)
                                        ->where('curso_id', $curso_caci->requisito_id)
                                        ->first();

                                    // REVISADOS (SIN EV)
                                    $revisados_requisito = DB::table('visitas')
                                        ->select(DB::raw('COUNT(id) AS cant'))
                                        ->where('usuario_id', $usuario_id)
                                        ->where('curso_id', $curso_caci->requisito_id)
                                        ->where('estado_tema', 'revisado')
                                        ->first();

                                    $req_apro_cant1 = ($requi2_aprob) ? intval($requi2_aprob->tot_aprobados) : 0;
                                    $req_apro_cant2 = ($requi2_aprob_abiertos) ? intval($requi2_aprob_abiertos->tot_aprobados) : 0;
                                    $cant_revisados = ($revisados_requisito) ? intval($revisados_requisito->cant) : 0;

                                    $r2_cant_aprob = $req_apro_cant1 + $req_apro_cant2 + $cant_revisados;

                                    // Consulta si aprobó el curso requisito
                                    if ($cant_post_asig2 > 0 && $r2_cant_aprob >= $cant_post_asig2) {
                                        $hizo_encuesta2 = DB::table('encuestas_respuestas')
                                            ->select('curso_id')
                                            ->where('usuario_id', $usuario_id)
                                            ->where('curso_id', $curso_caci->requisito_id)
                                            ->first();

                                        if (empty($hizo_encuesta2)) {
                                            $arra_cur = ['categoria_id' => $curso_caci->categoria_id, 'txc' => $cant_temas, 'id' => $curso_caci->curso_id, 'nombre' => $curso_caci->nombre, 'intentos' => 1000, 'requisito' => $requisito, 'temas' => null];
                                            array_push($pendiente_requisito, $arra_cur);
                                        } else {
                                            $arra_cur = ['categoria_id' => $curso_caci->categoria_id, 'txc' => $cant_temas, 'id' => $curso_caci->curso_id, 'nombre' => $curso_caci->nombre, 'intentos' => null, 'requisito' => $requisito, 'temas' => $temas];
                                            array_push($pendientes, $arra_cur);
                                        }
                                    } else if ($r2_cant_aprob < $cant_post_asig2) {
                                        $arra_cur = ['categoria_id' => $curso_caci->categoria_id, 'txc' => $cant_temas, 'id' => $curso_caci->curso_id, 'nombre' => $curso_caci->nombre, 'intentos' => null, 'requisito' => $requisito, 'temas' => null, 'revision' => $cant_post_asig2 . "-" . $r2_cant_aprob];
                                        array_push($pendiente_requisito, $arra_cur);
                                    } else {
                                        $arra_cur = ['categoria_id' => $curso_caci->categoria_id, 'txc' => $cant_temas, 'id' => $curso_caci->curso_id, 'nombre' => $curso_caci->nombre, 'intentos' => null, 'requisito' => $requisito, 'temas' => $temas];
                                        array_push($pendientes, $arra_cur);
                                    }
                                } else {
                                    // \Log::info('PENDIENTE: '.$curso_caci->curso_id);
                                    // Pendientes
                                    $arra_cur = ['categoria_id' => $curso_caci->categoria_id, 'txc' => $cant_temas, 'id' => $curso_caci->curso_id, 'nombre' => $curso_caci->nombre, 'intentos' => null, 'requisito' => $requisito,  'temas' => $temas];
                                    array_push($pendientes, $arra_cur);
                                }
                            }
                        }
                        //
                    }
                }
            }

            ////
            if ($res_x_escuela) {
                $response = array('error' => 0, 'data' => $res_x_escuela, 'data_extra' => $res_extracurri);
            } else {
                $response = array('error' => 1, 'data' => 3);
            }
        }
        return $response;
    }

    /*** ESTADO TEMAS ***/

    // Actualizar estado de tema según botón presionado
    public function actividad_tema_revisado($posteo_id = null, $usuario_id = null)
    {
        $response = array('status' => 'error', 'msg' => 'Ha ocurrido un error al actualizar la actividad');

        $actividad = Visita::select('id')
            ->where('post_id', $posteo_id)
            ->where('usuario_id', $usuario_id)
            ->first();

        if ($actividad) {
            $actividad->tipo_tema = 'no-evaluable';
            $actividad->estado_tema = 'revisado';
            $actividad->save();
            $response = array('status' => 'exito', 'msg' => 'Actividad actualizada correctamente');
        } else {
            $response = array('status' => 'alerta', 'msg' => 'No se ha actualizado el registro');
        }

        return $response;
    }

    /**************************************************** FUNCIONES ********************************************************************************/

    // Cursos asignados por ciclo
    public function cursos_asignados_x_ciclo($ciclo_id)
    {

        $cursos_id = DB::table('curricula')->where('ciclo_id', $ciclo_id)->where('estado', 1)->pluck('curso_id');

        $cursos_id_str = (isset($cursos_id)) ? implode(",", json_decode($cursos_id)) : 0;

        $result_cursos = \DB::select(\DB::raw("
                            SELECT c.id, COUNT(curso_id) txc, c.nombre, c.requisito_id, c.categoria_id
                            FROM cursos AS c
                            INNER JOIN posteos AS p
                            ON c.id = p.curso_id
                            WHERE c.id IN (" . $cursos_id_str . ")
                            AND p.evaluable = 'si'
                            AND c.estado = 1 AND p.estado = 1
                            GROUP BY p.curso_id
                        "));

        return $result_cursos;
    }

    // Cursos asignados por ciclo y escuela
    public function cursos_asignados_x_ciclo_escuela($ciclo_id, $cate_id)
    {

        $cursos_id = DB::table('curricula')->where('ciclo_id', $ciclo_id)->where('estado', 1)->pluck('curso_id');
        // dd($cursos_id);

        $cursos_id_str = (isset($cursos_id)) ? implode(",", json_decode($cursos_id)) : 0;

        $result_cursos = \DB::select(\DB::raw("
                            SELECT c.id, COUNT(curso_id) txc, c.nombre, c.requisito_id, c.categoria_id
                            FROM cursos AS c
                            INNER JOIN posteos AS p
                            ON c.id = p.curso_id
                            WHERE c.id IN (" . $cursos_id_str . ")
                            AND c.categoria_id = " . $cate_id . "
                            AND c.c_evaluable = 'si'
                            AND p.evaluable = 'si'
                            AND c.estado = 1 AND p.estado = 1
                            GROUP BY p.curso_id
                            "));

        return $result_cursos;
    }

    // Data de curso para progreso por categoria-ciclos
    public function cant_temas_x_curso($curso_id)
    {

        $result = \DB::select(\DB::raw("
                            SELECT COUNT(curso_id) txc
                            FROM posteos
                            WHERE curso_id = " . $curso_id . "
                            AND estado = 1
                            LIMIT 1
                            "));
        // AND evaluable = 'si'

        return $result;
    }
    // temas asignados incluyendo EV ABIERTAS
    public function temas_asignados_x_curso($curso_id, $usuario_id)
    {

        $temas = DB::table('posteos AS po')
            ->leftJoin('pruebas AS u', function ($join) use ($usuario_id) {
                $join->on('po.id', '=', 'u.posteo_id')
                    ->where('u.usuario_id', "=", $usuario_id);
            })
            ->select(DB::raw('po.id, po.nombre, po.tipo_ev, u.nota'))
            ->where('po.curso_id', $curso_id)
            ->where('po.evaluable', '=', 'si')
            ->where('po.tipo_ev', '=', 'calificada')
            ->where('po.estado', 1)
            ->orderBy('po.orden')
            ->get();

        $temas_ab = DB::table('posteos AS po')
            ->leftJoin('ev_abiertas AS u', function ($join) use ($usuario_id) {
                $join->on('po.id', '=', 'u.posteo_id')
                    ->where('u.usuario_id', "=", $usuario_id);
            })
            ->select(DB::raw('po.id, po.nombre, po.tipo_ev, u.id existe_evab'))
            ->where('po.curso_id', $curso_id)
            ->where('po.evaluable', '=', 'si')
            ->where('po.tipo_ev', '=', 'abierta')
            ->where('po.estado', 1)
            ->orderBy('po.orden')
            ->get();

        $data = $temas->merge($temas_ab);
        return $data;
    }

    // temas asignados incluyendo EV ABIERTAS
    public function temas_asignados_x_curso_v3($curso_id, $usuario_id)
    {

        $temas = DB::table('posteos AS po')
            ->leftJoin('pruebas AS u', function ($join) use ($usuario_id) {
                $join->on('po.id', '=', 'u.posteo_id')
                    ->where('u.usuario_id', "=", $usuario_id);
            })
            ->select(DB::raw('po.id, po.nombre, po.evaluable, po.tipo_ev, u.nota'))
            ->where('po.curso_id', $curso_id)
            ->where('po.evaluable', '=', 'si')
            ->where('po.tipo_ev', '=', 'calificada')
            ->where('po.estado', 1)
            ->orderBy('po.orden')
            ->get();

        $temas_ab = DB::table('posteos AS po')
            ->leftJoin('ev_abiertas AS u', function ($join) use ($usuario_id) {
                $join->on('po.id', '=', 'u.posteo_id')
                    ->where('u.usuario_id', "=", $usuario_id);
            })
            ->select(DB::raw('po.id, po.nombre, po.evaluable, po.tipo_ev, u.id AS existe_evab'))
            ->where('po.curso_id', $curso_id)
            ->where('po.evaluable', '=', 'si')
            ->where('po.tipo_ev', '=', 'abierta')
            ->where('po.estado', 1)
            ->orderBy('po.orden')
            ->get();

        $temas_no_ev = DB::table('posteos AS po')
            ->leftJoin('visitas AS u', function ($join) use ($usuario_id) {
                $join->on('po.id', '=', 'u.post_id')
                    ->where('u.usuario_id', "=", $usuario_id);
            })
            ->select(DB::raw('po.id, po.nombre, po.evaluable, u.sumatoria AS visita_ok, u.estado_tema'))
            ->where('po.curso_id', $curso_id)
            ->where('po.evaluable', '=', 'no')
            ->where('po.estado', 1)
            ->orderBy('po.orden')
            ->get();

        // \Log::info($curso_id.' |: '.$temas_no_ev);

        $data = $temas_no_ev->merge($temas_ab);
        $data = $data->merge($temas);
        return $data;
    }
    //Rediseño
    public function download_file(Request $request)
    {
        $url = urldecode($request->input('ruta'));
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=" . basename($url));
        header("Content-Type: " . "application/octet-stream");
        return readfile($url);
    }
    public function download(Request $request)
    {
        $url = $request->input('url');
        $link_array = explode('/', $url);
        $nombre = end($link_array);
        return response()->streamDownload(function () use ($url) {
            echo file_get_contents($url);
        }, $nombre);
    }
    // public function cargar_preguntas($tema_id)
    // {
    //     $apiResponse = ['data' => [], 'error' => true];
    //     $appUser = auth()->user();
    //     $tema = Posteo::where('id', $tema_id)->first();
    //     if (!$tema) return response()->json($apiResponse, 200);

    //     $data = [];
    //     $config_id = $appUser->config_id;
    //     $helper = new HelperController();
    //     $config_eva = $helper->configEvaluacionxModulo($config_id);
    //     if ($tema->tipo_ev  == 'calificada') {
    //         $preguntas = Pregunta::where('post_id', $tema->id)
    //                         ->where('estado', 1)
    //                         ->where('tipo_pregunta','selecciona')
    //                         ->select('id', 'tipo_pregunta', 'pregunta', 'ubicacion', 'estado', 'rptas_json')
    //                         ->inRandomOrder()
    //                         ->limit($config_eva['preg_x_ev'])
    //                         ->get();
    //         // APLICAR SHUFFLE A ALTERNATIVAS DE CADA PREGUNTA
    //         foreach ($preguntas as $value) {
    //             $val_rpta = json_decode($value->rptas_json, true);
    //             $tempRptas = collect();
    //             foreach ($val_rpta as $key => $value2) {
    //                 $tempRpta = ['id'=> $key , 'opc' =>$value2];
    //                 $tempRptas->push($tempRpta);
    //             }
    //             // shuffle($val_rpta);
    //             $shu = $tempRptas->shuffle()->all();

    //             // info($value);
    //             $value->rptas_json = $shu;
    //             // $value->rptas_json = $val_rpta;
    //         }
    //     } else {
    //         $preguntas = Pregunta::where('post_id', $tema->id)
    //                         ->where('estado', 1)
    //                         ->where('tipo_pregunta','texto')
    //                         ->select('id', 'tipo_pregunta', 'pregunta', 'ubicacion', 'estado', 'rptas_json')
    //                         ->get();
    //     }
    //     $data = ["tipo_evaluacion"=>$tema->tipo_ev,"curso_id"=>$tema->curso_id,"posteo_id" => $tema->id, "nombre" => $tema->nombre, "preguntas" => $preguntas];
    //     $ultima_evaluacion = Carbon::now();
    //     DB::table('resumen_general')->where('usuario_id',$appUser->id)->update([
    //         'last_ev' =>$ultima_evaluacion,
    //     ]);
    //     DB::table('resumen_x_curso')->where('usuario_id',$appUser->id)->where('curso_id',$tema->curso_id)->update([
    //         'last_ev' =>$ultima_evaluacion,
    //     ]);
    //     if (count($preguntas) > 0) $apiResponse = ['error' => false, 'data' => $data];
    //     else $apiResponse = ['error' => true, 'data' => null];

    //     return response()->json($apiResponse, 200);
    // }

    public function evaluar_preguntas(Request $request)
    {
        // return $request->all();
        $apiResponse = ['error' => true, 'msg' => 'Error. Algo ha ocurrido.'];
        $appUser     = auth()->user();
        $rpta_ok     = 0;
        $rpta_fail   = 0;
        $resultado   = 0;

        $config_id   = $appUser->config_id;
        $usuario_id  = $appUser->id;
        $post_id     = $request->tema;
        $rptas       = $request->respuestas;
        $usu_rptas   = $rptas;

        if (count($usu_rptas) == 0) {
            $apiResponse = ['error' => true, 'msg' => 'Respuestas no enviadas.'];
            return response()->json($apiResponse, 200);
        }
        $ev = Prueba::select('id', 'nota', 'intentos', 'rptas_ok', 'rptas_fail', 'resultado', 'categoria_id', 'curso_id', 'posteo_id')
                    ->where('posteo_id', $post_id)
                    ->where('usuario_id', $usuario_id)
                    ->first();
        if (!$ev) {
            $apiResponse = ['error' => true, 'msg' => 'EV no existe.'];
            return response()->json($apiResponse, 200);
        }
        $preguntas = Pregunta::select('id', 'rpta_ok')
                    ->where('post_id', $post_id)
                    ->get();

        foreach ($preguntas as $preg) {
            foreach ($usu_rptas as $rpta) {
                if ($preg->id == $rpta['preg_id']) {
                    if ($preg->rpta_ok == $rpta['opc']) $rpta_ok++;
                    else $rpta_fail++;
                    continue;
                }
            }
        }

        $helper             = new HelperController();
        $config_evaluacion  = $helper->configEvaluacionxModulo($config_id);
        $nota_aprobatoria   = $config_evaluacion['nota_aprobatoria'];
        $nota_calculada     = ($rpta_ok == 0) ? 0 : ((20 / ($rpta_ok + $rpta_fail)) * $rpta_ok);
        $resultado          = ($nota_calculada >= $nota_aprobatoria) ? 1 : 0;
        $ultima_evaluacion = Carbon::now();
        $data_ev = array(
            'rptas_ok'   => $rpta_ok,
            'rptas_fail' => $rpta_fail,
            'resultado'  => $resultado,
            'usu_rptas'  => json_encode($usu_rptas),
            'nota'       => round($nota_calculada),
            'last_ev' =>$ultima_evaluacion,
        );
        // return $data_ev;
        $nota_bd = $ev->nota;
        $data_tema_siguiente = false;
        if ($nota_calculada >= $nota_bd) {
            Prueba::where('id', $ev->id)->update($data_ev);

            $actividad = Visita::select('id')
                ->where('post_id', $post_id)
                ->where('usuario_id', $usuario_id)
                ->first();
            if ($actividad) {
                $actividad->tipo_tema = 'calificada';
                if ($resultado == 1) $actividad->estado_tema = 'aprobado';
                else $actividad->estado_tema = 'desaprobado';
                $actividad->save();
                $data_ev['resultado'] = ($actividad->estado_tema=='aprobado') ? 1 : 0 ;
            }
            if($resultado){
                $tema = Posteo::where('id',$ev->posteo_id)->select('orden')->first();
                $tema_siguiente = Posteo::where('curso_id',$ev->curso_id)->whereNotIn('id',[$ev->posteo_id])->where('orden','>=',$tema->orden)->select('id')->first();
                $data_tema_siguiente = ($tema_siguiente) ? $tema_siguiente->id : false;
            }
            $data_ev['ev_updated']      = 1;
            $data_ev['ev_updated_msg']  = "(1) EV actualizada";
        } else {
            $data_ev['ev_updated']      = 0;
            $data_ev['ev_updated_msg']  = "(0) EV no actualizada (nota obtenida menor que nota existente)";
        }
        $data_ev['tema_siguiente'] = $data_tema_siguiente;
        $data_ev['curso'] = Curso::where('id',$ev->curso_id)->select('id','nombre')->first();
        $data_ev['curso_id'] = $ev->curso_id;
        $data_ev['tema_id'] = $ev->posteo_id;
        // $data_ev['curso_id'] = $ev->curso_id;
        $data_ev['intentos_realizados'] = $ev->intentos;

        $restAvanceController = new RestAvanceController();
        // ACTUALIZAR RESUMENES
        $restAvanceController->actualizar_resumen_x_curso($usuario_id, $ev->curso_id, $config_evaluacion['nro_intentos']);
        $restAvanceController->actualizar_resumen_general($usuario_id);
        DB::table('resumen_general')->where('usuario_id',$usuario_id)->update([
            'last_ev' =>$ultima_evaluacion,
        ]);
        DB::table('resumen_x_curso')->where('usuario_id',$usuario_id)->where('curso_id',$ev->curso_id)->update([
            'last_ev' =>$ultima_evaluacion,
        ]);
        $data_ev['encuesta_pendiente'] = Curso_encuesta::getEncuestaPendiente($usuario_id,$ev->curso_id);
        $apiResponse = ['error' => false, 'data' => $data_ev];

        return response()->json($apiResponse, 200);
    }
    public function contador_tema_reseteo($post_id = null){
        $user_id = Auth::id();
        $posteo = Posteo::where('id',$post_id)->with(['curso'=>function($q){
            $q->select('id','config_id','reinicios_programado');
        },'curso.config'=>function($q){
            $q->select('id','reinicios_programado','mod_evaluaciones');
        },'categoria'=>function($q){
            $q->select('id','reinicios_programado');
        }])->select('id','categoria_id','curso_id')->first();
        $mod_eval = json_decode($posteo->curso->config->mod_evaluaciones, true);
        $select = DB::table('pruebas')
        ->select('intentos','curso_id','last_ev')
        ->where('posteo_id', $post_id)
        ->where('usuario_id', $user_id)
        ->where('intentos',$mod_eval['nro_intentos'])
        ->where('resultado',0)
        ->first();
        $contador = false;
        if($select){
            $tiempos=[];
            $rp_curso = ($posteo->curso->reinicios_programado) ? $tiempos[]=json_decode($posteo->curso->reinicios_programado) : false ;
            $rp_categoria = ($posteo->categoria->reinicios_programado) ? $tiempos[]=json_decode($posteo->categoria->reinicios_programado) : false;
            $rp_config = ($posteo->curso->config->reinicios_programado) ? $tiempos[]=json_decode($posteo->curso->config->reinicios_programado) : false;
            if(count($tiempos)>0){
                $reinicio_programado = false;
                $tiempo_en_minutos = 0;
                foreach($tiempos as $tiempo){
                    if($tiempo->activado){
                        $reinicio_programado = true;
                        $tiempo_en_minutos = $tiempo->tiempo_en_minutos;
                        break;
                    }
                }
                if($reinicio_programado){
                    $finaliza = Carbon::parse($select->last_ev)->addMinutes($tiempo_en_minutos);
                    $now = Carbon::now();
                    $contador = $finaliza->diff($now)->format('%y/%m/%d %H:%i:%s');
                }
            }
        }
        return response()->json(compact('contador'));
    }
    public function registra_ayuda(Request $request)
    {
        $usuario_id = strip_tags($request->input('usuario_id'));
        $motivo = strip_tags($request->input('motivo'));
        $detalle = strip_tags($request->input('detalle'));
        $contacto = strip_tags($request->input('contacto'));
        $user = Auth::user();
        if (is_null($usuario_id) || is_null($motivo)) {
            $data = array('error' => true, 'error_msg' => 'No se recibieron datos', 'data' => null);
        } else {
            $id = UsuarioAyuda::insertGetId(array(
                'usuario_id' => $usuario_id,
                'motivo' => $motivo,
                'detalle' => $detalle,
                'contacto' => $contacto,
            ));
            $modulo = Abconfig::where('id',$user->config_id)->select('etapa')->first();
            $mensaje = '*_Nueva incidencia:_* \n Empresa: Universidad Corporativa \n Módulo: '.$modulo->etapa.' \n DNI : '.$user->dni.' \n Ticket: #'.$id.' \n Motivo : '.$motivo.' \n Enlace: '.env('URL_GESTOR').'usuario_ayuda/index?id='.$id;
            UsuarioAyuda::send_message_to_slack($mensaje);
            $data = array('error' => false, 'data' =>['ticket'=>$id] );
        }
        return $data;
    }
    public function notification_user(){
        $user = Auth::user();
        $read = Usuario::find($user->id)->readNotifications()->select('id','data')->paginate(10);
        $unread = Usuario::find($user->id)->unreadNotifications()->select('id','data')->paginate(10);
        return response()->json(compact('read','unread'));
    }

    public function read_notification_user(Request $request){
        $notifications_id = $request->all();
        $user = Auth::user();
        Notification::where('notifiable_id',$user->id)->whereIn('id',$notifications_id)->update([
            'read_at' => true,
        ]);
        return response()->json('ok');
    }
    public function preguntas_seccion_ayuda(){
        $preguntas = AyudaApp::select('nombre', 'orden','check_text_area')->orderBy('orden','desc')->get();
        return response()->json(compact('preguntas'));
    }

    //Rediseño
}
