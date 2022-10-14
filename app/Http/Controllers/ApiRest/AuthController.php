<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginAppRequest;
use App\Models\Error;
use App\Models\Workspace;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(LoginAppRequest $request)
    {
        try {
            $data = $request->validated();

            $userinput = strip_tags($data['user']);
            $password = strip_tags($data['password']);
            $data['os'] = strip_tags($data['os'] ?? '');
            $data['version'] = strip_tags($data['version'] ?? '');
            $credentials1 = $credentials2 = ['password' => $password];
            // $key_search = str_contains($userinput, '@') ? 'email' : 'document';
            $credentials1['username'] = $userinput;
            $credentials2['document'] = $userinput;

            if (Auth::attempt($credentials1) || Auth::attempt($credentials2)){
                return $this->respondWithDataAndToken($data);
            }else{
                return $this->error('No autorizado.', 401);
            }

        } catch (Exception $e) {
//           info($e);
            Error::storeAndNotificateException($e, request());
            return $this->error('Server error.', 500);
        }
    }

    private function respondWithDataAndToken($data)
    {
        $user = Auth::user();
        $user->tokens()->delete();
        $token = $user->createToken('accessToken')->accessToken;

        if (!$user->active)
            return $this->error("Usuario inactivo.", http_code: 401);

        $user->load('criterion_values:id,value_text');
        $user->updateUserDeviceVersion($data);
        $user->updateLastUserLogin($data);

       $config_data = Workspace::with('main_menu', 'side_menu')->select('id', 'logo', 'mod_evaluaciones', 'plantilla_diploma')
           ->where('id', $user->subworkspace_id)
           ->first();

       // $matricula_actual = Matricula::select('carrera_id', 'ciclo_id')->where('usuario_id', $user->id)->where('estado', 1)->where('presente', 1)->orderBy('id', 'DESC')->first();
       // $carrera = ($matricula_actual) ? Carrera::select('id', 'nombre')->where('id', $matricula_actual->carrera_id)->first() : null;
       // $ciclo = ($matricula_actual) ? Ciclo::select('id', 'nombre')->where('id', $matricula_actual->ciclo_id)->first() : null;

        $supervisor = $user->isSupervisor();

        // $user_criteria = $user->criterion_values()->with('criterion.field_type')->get()->groupBy('criterion_id');

        // $course_segment_criteria = $segment->values->groupBy('criterion_id');

        // $valid_segment = Segment::validateSegmentByUserCriteria($user_criteria, $course_segment_criteria);

        

        $user_data = [
            "id" => $user->id,
            "dni" => $user->document,
            "nombre" => $user->name,
            "apellido" => $user->lastname,
            'criteria' => $user->criterion_values,
            'rol_entrenamiento' => $user->getTrainingRole(),
            'supervisor' => !!$supervisor,
            'module' => $user->subworkspace,
            'can_be_host' => true,
//            'carrera' => $carrera,
//            'ciclo' => $ciclo
//            "grupo" => $user->grupo,
//            "botica" => $user->botica,
//            "sexo" => $user->sexo,
//            "cargo" => $user->cargo,
        ];

       $config_data->app_side_menu = $config_data->side_menu->pluck('code')->toArray();
       $config_data->app_main_menu = $config_data->main_menu->pluck('code')->toArray();

       $config_data->full_app_main_menu = Workspace::getFullAppMenu('main_menu', $config_data->app_main_menu);
       $config_data->full_app_side_menu = Workspace::getFullAppMenu('side_menu', $config_data->app_side_menu);

        return response()->json([
            'access_token' => $token,
            'bucket_base_url' => get_media_url(),
//            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'config_data' => $config_data,
            'usuario' => $user_data
        ]);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // auth()->logout();
        // Auth::logout();

        $user = auth()->user();

        $user->tokens()->delete();

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
