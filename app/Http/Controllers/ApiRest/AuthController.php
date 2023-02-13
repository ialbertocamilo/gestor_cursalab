<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\Controller;
use App\Http\Requests\{ LoginAppRequest, QuizzAppRequest, 
                        PasswordResetAppRequest };
use App\Models\Error;
use App\Models\Workspace;
use App\Models\{ Usuario, User };
use Exception;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

use Illuminate\Http\Request;

class SubworkspaceInMaintenance extends Exception {};

class AuthController extends Controller
{
    public function login(LoginAppRequest $request)
    {
        // Stop login process and show maintenance message

        if (env('MAINTENANCE_MODE')) {
            return $this->error(
                config('errors.maintenance_message'),
                503
            );
        }

        try {
            $data = $request->validated();

            // === validacion de recaptcha ===
            $availableRecaptcha = $this->checkVersionMobileRecaptcha($data);
            if($availableRecaptcha) {
                $responseRecaptcha = $this->checkRecaptchaData($data);
                if($responseRecaptcha !== true) return $responseRecaptcha;
            }  
            // === validacion de recaptcha ===

            $userinput = strip_tags($data['user']);
            $password = strip_tags($data['password']);
            $data['os'] = strip_tags($data['os'] ?? '');
            $data['version'] = strip_tags($data['version'] ?? '');
            $credentials1 = $credentials2 = ['password' => $password];
            // $key_search = str_contains($userinput, '@') ? 'email' : 'document';
            $credentials1['username'] = trim($userinput);
            $credentials2['document'] = trim($userinput);

            $userInstance = new User;

            // === validacion de intentos ===
            $user_attempts = $userInstance->checkAttemptManualApp([$credentials1, $credentials2]); 
            if($user_attempts) {
                $responseAttempts = $this->sendAttempsAppResponse($user_attempts);
                return $this->error('Intento fallido.', 401, $responseAttempts);
            }
            // === validacion de intentos ===

            if (Auth::attempt($credentials1) || Auth::attempt($credentials2)) {

                // When "Show message" variable is true and
                // company is Farmacias Peruanas

                if (env('SHOW_MESSAGE_M4')) {

                    // Fetch subworkspaces ids of Farmacias Peruanas workspace

                    $farmaciasIds = Workspace::where('parent_id', 25)
                        ->pluck('id')
                        ->toArray();

                    // If user's subworkspace is in Farmacias, stop authentication

                    Auth::check();
                    $isFarmacias = in_array(
                        Auth::user()->subworkspace_id,
                        $farmaciasIds
                    );

                    if ($isFarmacias) {
                        $message = 'ERROR_LOGIN_FARMACIAS_PERUANAS';
                        return $this->error($message, 401);
                    }
                }

                $user = Auth::user();
                $user->resetAttemptsUser(); // resetea intentos

                /*
                // === validar si debe reestablecer contraseña ===
                $canResetPassWord = $user->checkIfCanResetPassword();
                if($canResetPassWord) {
                    // === verificar el dni como password ===
                    $responseResetPass = $this->checkSameDataCredentials(trim($userinput), $password);
                    return response()->json($responseResetPass);
                }
                // === validar si debe reestablecer contraseña === 
                */
                
                // $responseUserData['recaptcha'] = $recaptcha_response; opcional
                $responseUserData = $this->respondWithDataAndToken($data);
                return response()->json($responseUserData); 

            } else {
                // === validacion de intentos ===
                $userInstance->checkTimeToReset($userinput, 'APP'); 
                $user_attempts = $userInstance->incrementAttempts($userinput, 'APP');
                if($user_attempts) {
                    $responseAttempts = $this->sendAttempsAppResponse($user_attempts);
                    return $this->error('Intento fallido.', 401, $responseAttempts);
                }
                // === validacion de intentos ===

                return $this->error('No autorizado.', 401);
            }

        }
        catch (SubworkspaceInMaintenance $e){
            return $this->error(
                    config('errors.maintenance_subworkspace_message'), 503
                );
        }
        catch (Exception $e) {
            info($e);
            Error::storeAndNotificateException($e, request());
            return $this->error('Server error.', 500);
        }
    }

    private function respondWithDataAndToken($data)
    {
        $user = Auth::user();
        $user->tokens()->delete();
        $token = $user->createToken('accessToken')->accessToken;

        // Stop login to users from specific workspaces
        $this->checkForMaintenanceModeSubworkspace($user->subworkspace_id);

        // if ($user->subworkspace_id == 29 AND $user->external_id) {

        //     // return $this->error("Usuario inactivo temporalmente. Migración en progreso.", http_code: 401);
        //     return $this->error(
        //         config('errors.maintenance_ucfp'), 503
        //     );
        // }

        if (!$user->active)
            return $this->error('Tu cuenta se encuentra inactiva.
            Comunícate con tu coordinador para enviar una solicitud de activación.', http_code: 503);

        $user->load('criterion_values:id,value_text');
        $user->updateUserDeviceVersion($data);
        $user->updateLastUserLogin($data);

        $config_data = Workspace::with('main_menu', 'side_menu')->select('id', 'logo', 'mod_evaluaciones', 'plantilla_diploma', 'contact_support')
            ->where('id', $user->subworkspace_id)
            ->first();

        $workspace = Workspace::find($user->subworkspace_id);
        // $matricula_actual = Matricula::select('carrera_id', 'ciclo_id')->where('usuario_id', $user->id)->where('estado', 1)->where('presente', 1)->orderBy('id', 'DESC')->first();
        // $carrera = ($matricula_actual) ? Carrera::select('id', 'nombre')->where('id', $matricula_actual->carrera_id)->first() : null;
        // $ciclo = ($matricula_actual) ? Ciclo::select('id', 'nombre')->where('id', $matricula_actual->ciclo_id)->first() : null;

        $supervisor = $user->isSupervisor();
        // $can_be_host = $user->belongsToSegmentation($workspace);

        $workSpaceIndex = $user->subworkspace->parent_id;
        $current_hosts = Usuario::getCurrentHosts(true, $workSpaceIndex);
        $can_be_host = in_array($user->id, $current_hosts);

        $workspace_data = ($workspace->parent_id) ? Workspace::select('logo', 'slug', 'name')->where('id', $workspace->parent_id)->first() : null;
        if ($workspace_data) {
            $workspace_data->logo = get_media_url($workspace_data->logo);
        }
        if ($user->subworkspace->logo) {
            $user->subworkspace->logo = get_media_url($user->subworkspace->logo);
        }

        $ciclo_actual = null;
        if ($user->subworkspace->parent_id == 25){
            $ciclo_actual = $user->getActiveCycle()?->value_text;
        }

        $user_data = [
            "id" => $user->id,
            "dni" => $user->document,
            "nombre" => $user->name ?? '',
            "apellido" => $user->lastname ?? '',
            "full_name" => $user->fullname,
            'criteria' => $user->criterion_values,
            'rol_entrenamiento' => $user->getTrainingRole(),
            'supervisor' => !!$supervisor,
            'module' => $user->subworkspace,
            'workspace' => $workspace_data,
            'can_be_host' => $can_be_host,
            'ciclo_actual' => $ciclo_actual,
            // 'can_be_host' => true,
            // 'carrera' => $carrera,
            // 'ciclo' => $ciclo
            // "grupo" => $user->grupo,
            // "botica" => $user->botica,
            // "sexo" => $user->sexo,
            // "cargo" => $user->cargo,
        ];

        $config_data->app_side_menu = $config_data->side_menu->pluck('code')->toArray();
        $config_data->app_main_menu = $config_data->main_menu->pluck('code')->toArray();

        $config_data->full_app_main_menu = Workspace::getFullAppMenu('main_menu', $config_data->app_main_menu);
        $config_data->full_app_side_menu = Workspace::getFullAppMenu('side_menu', $config_data->app_side_menu);
        $config_data->filters = config('data.filters');

        return [
            'access_token' => $token,
            'bucket_base_url' => get_media_url(),
            //            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'config_data' => $config_data,
            'usuario' => $user_data
        ];
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

    public function checkForMaintenanceModeSubworkspace($subworkspace_id){
        if (!empty(env('MAINTENANCE_SUBWORKSPACES'))) {
            $subworkspace_maintenance_array = explode (",", env('MAINTENANCE_SUBWORKSPACES'));
            if (in_array($subworkspace_id, $subworkspace_maintenance_array)) {
                throw new SubworkspaceInMaintenance();
            }
        }
    }

    public function sendAttempsAppResponse($user_attempts)
    {
        $errors = [ 'current_time'   => now()->diff($user_attempts->attempts_lock_time)->format("%I:%S"),
                    'attempts_count' => $user_attempts->attempts,
                    'attempts_max'   => env('ATTEMPTS_LOGIN_MAX_APP'),
                    'attempts_fulled'=> $user_attempts->fulled_attempts ];

        return $errors;
    }

    public function checkVersionMobileRecaptcha($data)
    {
        $currentOS = $data['os'] ?? '';
        $availableRecaptcha = true; // estado de recaptcha

        if($currentOS && $currentOS == 'android' ) {
            $currentVersion = $data['version'];
            settype($currentVersion, "float");

            $mobilesVersion = ['android' => 3.3];

            if($currentVersion >= $mobilesVersion[$currentOS]) $availableRecaptcha = true;
            else $availableRecaptcha = false;
        }

        return $availableRecaptcha; 
    }

    public function checkRecaptchaData($data)
    {
        $g_recaptcha_response = $data['g-recaptcha-response'] ?? '';
        $recaptcha_response = NULL;
                
        if ($g_recaptcha_response) {
            //validar token recaptcha
            $recaptcha_response = $this->validateRecaptcha($g_recaptcha_response);
            if(!$recaptcha_response['success']) {
                return $this->error('error-recaptcha', 500, $recaptcha_response);
            }
            //validar el score de recaptcha
            if(!$recaptcha_response['score'] >= 0.5) {
                return $this->error('error-recaptcha', 500, [ 
                        'score' => $recaptcha_response['score'],
                        'error-codes' => ['score-is-low']
                ]);
            }
     
            return true;

        } else { 
            return $this->error('error-recaptcha', 500); 
        }
    }

    public function validateRecaptcha($siteToken) {
        $secretKey = env('RECAPTCHA_TOKEN'); 
        $recaptchaUrl = env('RECAPTCHA_BASE_URL');

        // validamos token recaptcha
        $responseRecaptcha = Http::asForm()->post($recaptchaUrl, [
            'secret' => $secretKey,
            'response' => $siteToken
        ]);

        return $responseRecaptcha->json();
    }

    public function checkSameDataCredentials($userinput, $password)
    {
        $user = auth()->user();
        $checkCredentials['require_quizz'] = ($userinput === $password) && ($user->email === NULL);
        
        if(!$checkCredentials['require_quizz']) {
            return $this->sendEmailResetPassword($user, $checkCredentials);
        }
        return $checkCredentials;
    }

    public function sendEmailResetPassword($user, $checkCredentials)
    {
        $workspaceName = Workspace::find($user->subworkspace->parent_id)->name;
        $subWorkspaceName = $user->subworkspace->name;

        $mail_data = [ 'email' =>  $user->email,
                       'workspace' => $workspaceName,
                       'subworkspace' => $subWorkspaceName,
                       'fullname' => $user->name.' '.$user->lastname ];

        $status = Password::sendResetLink(['email' => $user->email]);

        $checkCredentials['recovery_email']['success'] = ($status === Password::RESET_LINK_SENT);
        $checkCredentials['recovery_email']['data'] = $mail_data;

        return $checkCredentials;
    }

    public function resendEmailResetPassword()
    {
        if(!session('auth_resend')) return $this->error('no-auth-resend', 503);
        if(!auth()->user()) return $this->error('no-auth-reset', 503);

        $user = auth()->user(); 
        return $user->email ? $this->success('Reenviamos email', $this->sendEmailResetPassword($user)):
                              $this->error('no-auth-reset', 503);
    }

    public function validateQuizz(QuizzAppRequest $request)
    {
        if(Auth::check()) {
            dd('paseed check autentication');
        }
        $user = auth()->user();
    }

    public function checkTokenResetPass_One(PasswordResetAppRequest $request)
    {
        if(!auth()->user()) return $this->error('no-auth-reset', 503);

        $request->add(['email' => auth()->user()->email]);
        $request->validated();

        $credentials = $request->only('email', 'password', 'password_confirmation', 'token');

        $status = Password::reset($credentials, function($user, $password) {
            $user->password = $password;
            $user->setRememberToken(Str::random(60));
            $user->save();
        });

        return $this->success('Contraseña actualizada', ['success' => ($status == Password::PASSWORD_RESET)]);
    }

    public function checkTokenResetPass_Two(PasswordResetAppRequest $request)
    {
        if(Auth::check()) {
            dd('paseed check autentication');
        }
        $user = auth()->user();
        $credentials = $request->only('password', 'password_confirmation');

        /*$status = Password::reset($request, function($user, $password) {
            $user->password = $password;
            $user->setRememberToken(Str::random(60));
            $user->save();
        });*/    
    }
}
