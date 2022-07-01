<?php

namespace App\Http\Controllers\ApiRest;

use Config;
use App\Models\Abconfig;
use App\Models\Taxonomia;
use App\Models\Carrera;
use App\Models\Ciclo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Matricula;
use App\Models\Usuario_rest;
use App\Models\UsuarioVersiones;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct()
    {
        // header('Access-Control-Allow-Origin: *');
        // header('Access-Control-Allow-Methods:  GET,PUT,POST,DELETE,PATCH,OPTIONS');
        // header('Access-Control-Allow-Credentials: true');
        // header("Access-Control-Allow-Headers: Origin, Accept, X-Requested-With, Content-Type, X-Token-Auth, Authorization, x-xsrf-token");

        // Config::set('jwt.user', Usuario_rest::class);
        // Config::set('auth.providers.users.model', Usuario_rest::class);

        $this->middleware('auth.jwt', ['except' => ['login']]);
        return auth()->shouldUse('api');
    }
    /**
     * Get a JWT via given credentials.
     *
     */
    public function login(Request $request)
    {
        // return $request;
        try {
            $validator = Validator::make($request->all(), [
                'user' => 'required',
                'password' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => 2, 'data' => null], 200); //500
            }
            $username = strip_tags($request->input('user'));
            $pass = strip_tags($request->input('password'));
            $os = strip_tags($request->input('os'));
            $version = strip_tags($request->input('version'));

            $credentials = array('dni' => $username, 'password' => $pass);
            if (!$token = auth()->attempt($credentials)) {
                // return response()->json(['error' => 3, 'data' => null], 401);
                return response()->json(['error' => 3, 'data' => null], 200); //401
            }
            return $this->respondWithDataAndToken($token,  $os, $version);
        } catch (Exception $e) {
            report($e);
            return response()->json([
                "status_code" => 500,
                "logged" => false,
                "message" => "Error in Login",
                "error" => $e,
            ]);
        }
    }

    private function respondWithDataAndToken($token, $os, $version)
    {
        $usuario = auth()->user();
        if ($usuario->estado == 0) {
            return response()->json(['error' => 1, 'data' => null], 500);
        }
        try {
            // Actualizar tabla usuario_versiones
            UsuarioVersiones::actualizar_version_y_visita($usuario->id,$os,$version);
        } catch (\Throwable $th) {
            info($th);
        }
       
        // Data
        $config_data = Abconfig::with('main_menu', 'side_menu')->select('id', 'color', 'duracion_dias', 'logo', 'isotipo', 'mod_agrupacion', 'mod_cronometro', 'mod_encuestas', 'mod_evaluaciones', 'mod_mainmenu', 'mod_sidemenu', 'mod_tipovalidacion', 'plantilla_diploma', 'mod_push', 'push_code')
            ->where('id', $usuario->config_id)
            ->first();
        $matricula_actual = Matricula::select('carrera_id', 'ciclo_id')->where('usuario_id', $usuario->id)->where('estado', 1)->where('presente', 1)->orderBy('id', 'DESC')->first();
        $carrera = ($matricula_actual) ? Carrera::select('id', 'nombre')->where('id', $matricula_actual->carrera_id)->first() : null;
        $ciclo = ($matricula_actual) ? Ciclo::select('id', 'nombre')->where('id', $matricula_actual->ciclo_id)->first() : null;
        $supervisor = DB::table('supervisores')->where('usuario_id', $usuario->id)->first();

        $usuario_data = [
            "id" => $usuario->id,
            "dni" => $usuario->dni,
            "nombre" => $usuario->nombre,
            "cargo" => $usuario->cargo,
            "sexo" => $usuario->sexo,
            "botica" => $usuario->botica,
            "grupo" => $usuario->grupo,
            'rol_entrenamiento'=> $usuario->rol_entrenamiento,
            'supervisor' => ($supervisor) ? true : false,
            'carrera' => $carrera,
            'ciclo' => $ciclo
        ];
        // Actualiza ultima_sesion
        date_default_timezone_set('America/Lima');
        $usuario->ultima_sesion = date('Y-m-d H:i:s');
        $usuario->save();

        $config_data->app_side_menu = $config_data->side_menu->pluck('code')->toArray();
        $config_data->app_main_menu = $config_data->main_menu->pluck('code')->toArray();

        $config_data->full_app_main_menu = Abconfig::getFullAppMenu('main_menu', $config_data->app_main_menu);
        $config_data->full_app_side_menu = Abconfig::getFullAppMenu('side_menu', $config_data->app_side_menu);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => 560,
            'error' => 0,
            "token_firebase" => $usuario->token_firebase,
            'config_data' => $config_data,
            'usuario' => $usuario_data
        ]);
    }

    // public function me()
    // {
    //     return response()->json(auth()->user());
    // }

    // public function payload()
    // {
    //     return response()->json(auth()->payload());
    // }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    // FUNCIONES

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        // return $this->respondWithToken(auth()->refresh());
        return $this->respondOnlyToken(auth()->refresh());
    }
    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    // protected function respondWithToken($token)
    // {
    //     return response()->json([
    //         'access_token' => $token,
    //         'token_type' => 'bearer',
    //         'expires_in' => auth('api')->factory()->getTTL() * 60,
    //         'user' => auth()->user(),
    //     ]);
    // }

    protected function respondOnlyToken($token)
    {
//        \Log::info('New token refreshed' . $token);
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => 560
        ]);
    }
}
