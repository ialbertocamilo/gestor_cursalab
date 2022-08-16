<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginAppRequest;
use App\Models\Error;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function login(LoginAppRequest $request)
    {
        try {
            $data = $request->validated();

            $username = strip_tags($data['user']);
            $password = strip_tags($data['password']);
            $os = strip_tags($data['os'] ?? '');
            $version = strip_tags($data['version'] ?? '');
            $credentials = ['password' => $password];
            $key_search = str_contains($username, '@') ? 'email' : 'document';
            $credentials[$key_search] = $username;

            if (!Auth::attempt($credentials))
                return $this->error('No autorizado.', 401);

            return $this->respondWithDataAndToken();

        } catch (Exception $e) {
            info($e);
            Error::storeAndNotificateException($e, request());
            return $this->error('Server error.', 500);
        }
    }

    private function respondWithDataAndToken()
    {
        $user = Auth::user();
        $user->tokens()->delete();
        $token =  $user->createToken('accessToken')->accessToken;

        if (!$user->active)
            return $this->error("Usuario inactivo.", http_code: 401);

        $user->updateUserDeviceVersion();
        try {
            // Actualizar tabla usuario_versiones
            UsuarioVersiones::actualizar_version_y_visita($user->id, $os, $version);
        } catch (\Throwable $th) {
            info($th);
        }

//        $config_data = Abconfig::with('main_menu', 'side_menu')->select('id', 'color', 'duracion_dias', 'logo', 'isotipo', 'mod_agrupacion', 'mod_cronometro', 'mod_encuestas', 'mod_evaluaciones', 'mod_mainmenu', 'mod_sidemenu', 'mod_tipovalidacion', 'plantilla_diploma', 'mod_push', 'push_code')
//            ->where('id', $user->config_id)
//            ->first();
//        $matricula_actual = Matricula::select('carrera_id', 'ciclo_id')->where('usuario_id', $user->id)->where('estado', 1)->where('presente', 1)->orderBy('id', 'DESC')->first();
//        $carrera = ($matricula_actual) ? Carrera::select('id', 'nombre')->where('id', $matricula_actual->carrera_id)->first() : null;
//        $ciclo = ($matricula_actual) ? Ciclo::select('id', 'nombre')->where('id', $matricula_actual->ciclo_id)->first() : null;
        $supervisor = $user->isSupervisor();

        $user_data = [
            "id" => $user->id,
            "dni" => $user->document,
            "nombre" => $user->name,
            'criteria' => $user->load('criterion_values:id,value_text'),
//            "cargo" => $user->cargo,
//            "sexo" => $user->sexo,
//            "botica" => $user->botica,
//            "grupo" => $user->grupo,
//            'rol_entrenamiento' => $user->rol_entrenamiento,
            'supervisor' => !!$supervisor,
//            'carrera' => $carrera,
//            'ciclo' => $ciclo
        ];
        // Actualiza ultima_sesion
//        date_default_timezone_set('America/Lima');
//        $user->ultima_sesion = date('Y-m-d H:i:s');
//        $user->save();

//        $config_data->app_side_menu = $config_data->side_menu->pluck('code')->toArray();
//        $config_data->app_main_menu = $config_data->main_menu->pluck('code')->toArray();
//
//        $config_data->full_app_main_menu = Abconfig::getFullAppMenu('main_menu', $config_data->app_main_menu);
//        $config_data->full_app_side_menu = Abconfig::getFullAppMenu('side_menu', $config_data->app_side_menu);

//        return response()->json([
//            'access_token' => $token,
//            'token_type' => 'bearer',
//            'expires_in' => 560,
//            'error' => 0,
//            "token_firebase" => $user->token_firebase,
//            'config_data' => $config_data,
//            'usuario' => $user_data
//        ]);
        return response()->json([
            'access_token' => $token,
//            'expires_in' => auth('api')->factory()->getTTL() * 60,
//            'config_data' => $config_data,
            'usuario' => $user_data
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
