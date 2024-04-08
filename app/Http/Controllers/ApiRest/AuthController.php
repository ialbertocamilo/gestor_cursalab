<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\Controller;
use App\Http\Requests\{ LoginAppRequest, QuizzAppRequest,
                        PasswordResetAppRequest };
use App\Mail\EmailTemplate;
use App\Models\Error;
use App\Models\Workspace;
use App\Models\{Ticket, Usuario, User, WorkspaceFunctionality, Ambiente, Process, Taxonomy};
use Exception;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

use App\Models\Master\Customer;

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

            $customer = Customer::getCurrentSession();

            if ($customer && !$customer->hasServiceAvailable()) {

                return $this->error('Plataforma suspendida. Comunícate con tu coordinador para más información. [C]', http_code: 503);
            }

            $ambiente = Ambiente::first();
            $data = $request->validated();

            // === validacion de recaptcha ===
             if(ENV('RECAPTCHA_ENABLED') == true){

                $availableRecaptcha = $this->checkVersionMobileRecaptcha($data);
                if($availableRecaptcha) {
                    $responseRecaptcha = $this->checkRecaptchaData($data);
                    if($responseRecaptcha !== true) return $responseRecaptcha;
                }
            }

            // === validacion de recaptcha ===

            $userinput = strip_tags($data['user']);
            $password = $data['password'];
            $data['os'] = strip_tags($data['os'] ?? '');
            $data['version'] = strip_tags($data['version'] ?? '');
            $credentials1 = $credentials2 = $credentials3 = ['password' => $password];
            $credentials1['username'] = trim($userinput);
            $credentials2['document'] = trim($userinput);
            $credentials3['email'] = trim($userinput);

            $userInstance = new User;

            // === validacion de intentos ===
            $user_attempts = $userInstance->checkAttemptManualApp([$credentials1, $credentials2, $credentials3], true); //permanent
            if($user_attempts) {
                $responseAttempts = $this->sendAttempsAppResponse($user_attempts);

                // custom message
                if($responseAttempts['attempts_fulled'] && $responseAttempts['current_time'] == false){
                    return $this->error('Validación de identidad fallida. Por favor, contáctate con tu administrador.', 400, $responseAttempts);
                }
                return $this->error('Intento fallido A.', 400, $responseAttempts);
            }
            // === validacion de intentos ===

            if (Auth::attempt($credentials1) || Auth::attempt($credentials2) || Auth::attempt($credentials3)) {

                $subworkspace = Auth::user()->subworkspace;
                $workspace = $subworkspace->parent ?? NULL;

                if (!$subworkspace || ($subworkspace && !$subworkspace->active))
                    return $this->error('Espacio de trabajo inactivo. Comunícate con tu coordinador para más información. [SW]', http_code: 503);

                if (!$workspace || ($workspace && !$workspace->active))
                    return $this->error('Espacio de trabajo inactivo. Comunícate con tu coordinador para más información. [W]', http_code: 503);

                // Valida si usuario está inactivo
                if (!Auth::user()->active)
                    return $this->error('Tu cuenta se encuentra inactiva. Comunícate con tu coordinador para enviar una solicitud de activación.', http_code: 503);

                // === verificar el dni como password ===
                if ($ambiente->identity_validation_enabled) {

                    if (trim($userinput) === $password) {
                        $responseResetPass = [];
                        Auth::user()->resetAttemptsUser(); // resetea intentos

                        $responseResetPass['recovery'] = $this->checkSameDataCredentials(trim($userinput), $password);
                        return response()->json($responseResetPass);
                    }
                }
                // === verificar el dni como password ===

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

                // === validar si debe reestablecer contraseña ===
                if ($ambiente->password_expiration_enabled) {

                    $canResetPassWord = $user->checkIfCanResetPassword('APP');
                    if($canResetPassWord) {
                        return $this->resetPasswordBuildToken($user);
                    }

                }
                // === validar si debe reestablecer contraseña ===

                $responseUserData = $this->respondWithDataAndToken($data);
                // $responseUserData['recaptcha'] = $recaptcha_response; opcional

                // Update flag to update courses
                $user->required_update_at = now();
                $user->save();

                return response()->json($responseUserData);

            } else {
                // === validacion de intentos ===
                $userInstance->checkTimeToReset(trim($userinput), 'APP');
                $user_attempts = $userInstance->incrementAttempts(trim($userinput), 'APP');

                if($user_attempts) {
                    $responseAttempts = $this->sendAttempsAppResponse($user_attempts);
                    $responseAttempts['credentials1'] = $credentials1;
                    $responseAttempts['credentials2'] = $credentials2;
                    $responseAttempts['credentials3'] = $credentials3;
                    // custom message
                    if($responseAttempts['attempts_fulled'] && $responseAttempts['current_time'] == false){
                        return $this->error('Validación de identidad fallida. Por favor, contáctate con tu administrador.', 400, $responseAttempts);
                    }

                    return $this->error('Intento fallido [L2].', 400, $responseAttempts);
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
            // return $this->error('Server error.', 500);
            return $this->error('Validación de identidad fallida. Por favor, contáctate con tu administrador.', 400, []);

        }
    }

    private function respondWithDataAndToken($data)
    {
        $user = Auth::user();
        // $user->tokens()->delete();
        $token = $user->createToken('accessToken')->accessToken;

        // Stop login to users from specific workspaces
        // $this->checkForMaintenanceModeSubworkspace($user->subworkspace_id);

        // if ($user->subworkspace_id == 29 AND $user->external_id) {

        //     // return $this->error("Usuario inactivo temporalmente. Migración en progreso.", http_code: 401);
        //     return $this->error(
        //         config('errors.maintenance_ucfp'), 503
        //     );
        // }

        // if (!$user->active)
        //     return $this->error('Tu cuenta se encuentra inactiva.
        //     Comunícate con tu coordinador para enviar una solicitud de activación.', http_code: 503);

        $user->load('criterion_values.criterion');
        $user->updateUserDeviceVersion($data);
        $user->updateLastUserLogin($data);

        $config_data = Workspace::with('main_menu', 'side_menu')->select('id', 'logo', 'plantilla_diploma', 'contact_support')
            ->where('id', $user->subworkspace_id)
            ->first();

        // info('here data');

        $workspace = Workspace::find($user->subworkspace_id);
        // $matricula_actual = Matricula::select('carrera_id', 'ciclo_id')->where('usuario_id', $user->id)->where('estado', 1)->where('presente', 1)->orderBy('id', 'DESC')->first();
        // $carrera = ($matricula_actual) ? Carrera::select('id', 'nombre')->where('id', $matricula_actual->carrera_id)->first() : null;
        // $ciclo = ($matricula_actual) ? Ciclo::select('id', 'nombre')->where('id', $matricula_actual->ciclo_id)->first() : null;

        $type_employee_onboarding = Taxonomy::getFirstData('user','type', 'employee_onboarding');

        if($user->type_id == $type_employee_onboarding?->id) {
            $onboarding = true;
            $supervisor_induccion = count($user->processes) ? true : false;
            $supervisor = false;
            $processes = Process::getProcessesAssigned($user);
        }
        else {
            $onboarding = false;
            $supervisor_induccion = false;
            $supervisor = $user->isSupervisor();
            $processes = [];
        }
        // $can_be_host = $user->belongsToSegmentation($workspace);

        $workSpaceIndex = $user->subworkspace->parent_id;
        $current_hosts = Usuario::getCurrentHosts(true, $workSpaceIndex);
        $can_be_host = in_array($user->id, $current_hosts);

        $workspace_data = ($workspace->parent_id)
            ? Workspace::select('share_diplomas_social_media', 'show_logo_in_app', 'logo', 'slug', 'name', 'id')
                ->where('id', $workspace->parent_id)
                ->first()
            : null;
        
        //CUSTOM DESIGN BY WORKSPACE
        $custom_ambiente = Ambiente::getCustomAmbienteByWorkspace($workspace->parent_id);
        if ($workspace_data) {
            $show_logo_in_app = $workspace_data->show_logo_in_app ?? false;

            if ($show_logo_in_app) {
                $workspace_data->logo = get_media_url($workspace_data->logo);
            } else {
                $ambiente = Ambiente::first() ;
                $workspace_data->logo =  get_media_url($ambiente['logo']);
            }
        }

        if ($user->subworkspace->logo) {
            $user->subworkspace->logo = get_media_url($user->subworkspace->logo);

            if ($user->subworkspace->show_logo_in_app) {
                $workspace_data->logo = $user->subworkspace->logo;
            }
        }

        $ciclo_actual = null;

        if ($user->subworkspace->parent_id == 25){
            $ciclo_actual = $user->getActiveCycle()?->value_text;
        }

        $criterios = $user->getProfileCriteria();

        $user_data = [
            "id" => $user->id,
            "dni" => $user->document,
            "nombre" => $user->name ?? '',
            "apellido" => $user->lastname ?? '',
            "full_name" => $user->fullname,
            // 'criteria' => $user->criterion_values,
            'rol_entrenamiento' => $user->getTrainingRole(),
            'supervisor' => !!$supervisor,
            'module' => [
                'id'=> $user->subworkspace->id,
                'name' => $user->subworkspace->name,
                'contact_support' => $user->subworkspace->contact_support,
            ],
            'workspace' => $workspace_data,
            'can_be_host' => $can_be_host,
            'ciclo_actual' => $ciclo_actual,
            'android' => $user->android,
            'ios' => $user->ios,
            'huawei' => $user->huawei,
            // 'can_be_host' => true,
            // 'carrera' => $carrera,
            // 'ciclo' => $ciclo
            // "grupo" => $user->grupo,
            // "botica" => $user->botica,
            // "sexo" => $user->sexo,
            // "cargo" => $user->cargo,
            'criterios' => $criterios,
            'supervisor_induccion' => $supervisor_induccion,
            'processes' => $processes,
            'onboarding' => $onboarding
        ];

        if($user->type_id == $type_employee_onboarding?->id) {
            if($supervisor_induccion) {
                $config_data->app_side_menu = [
                    'ind_asistencia',
                    'ind_procesos',
                    'ind_faq'
                ];
                $config_data->app_main_menu = [
                    'ind_home_sup',
                    'ind_asistencia',
                    'ind_procesos'
                ];
                $config_data->full_app_main_menu = [
                    'ind_home_sup' => true,
                    'ind_asistencia' => true,
                    'ind_procesos' => true
                ];
                $config_data->full_app_side_menu = [
                    'ind_asistencia' => true,
                    'ind_procesos' => true,
                    'ind_faq' => true,
                ];
            }
            else {
                $config_data->app_side_menu = [
                    'ind_avance',
                    'ind_ruta',
                    'ind_certificado',
                    'ind_faq'
                ];
                $config_data->app_main_menu = [
                    'ind_home',
                    'ind_ruta',
                    'ind_faq'
                ];
                $config_data->full_app_main_menu = [
                    'ind_home' => true,
                    'ind_ruta' => true,
                    'ind_faq' => true
                ];
                $config_data->full_app_side_menu = [
                    'ind_avance' => true,
                    'ind_ruta' => true,
                    'ind_certificado' => true,
                    'ind_faq' => true,
                ];
            }
        }
        else{
            $config_data->app_side_menu = $config_data->side_menu->pluck('code')->toArray();
            $config_data->app_main_menu = $config_data->main_menu->pluck('code')->toArray();
            $config_data->full_app_main_menu = Workspace::getFullAppMenu('main_menu', $config_data->app_main_menu, $user);
            $config_data->full_app_side_menu = Workspace::getFullAppMenu('side_menu', $config_data->app_side_menu, $user);
        }
        $config_data->filters = config('data.filters');
        $config_data->meetings_upload_template = config('app.meetings.app_upload_template');
        //has offline
        $config_data->is_offline = boolval(WorkspaceFunctionality::join('taxonomies','taxonomies.id','workspace_functionalities.functionality_id')
                        ->where('workspace_functionalities.workspace_id',$workspace?->parent_id)
                        ->where('workspace_functionalities.active',1)
                        ->where('taxonomies.code','course-offline')
                        ->first());
        return [
            'access_token' => $token,
            'bucket_base_url' => get_media_root_url(),
            // 'expires_in' => auth('api')->factory()->getTTL() * 60,
            'config_data' => $config_data,
            'usuario' => $user_data,
            'custom_ambiente'=> $custom_ambiente
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

        // $user->tokens()->delete();
        $user->token()->revoke();
        // $request->user()->currentAccessToken()->delete();

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

    // === ATTEMPTS ===
    public function sendAttempsAppResponse($user_attempts, $user_time = true)
    {
        $errors = [ 'attempts_count' => $user_attempts->attempts,
                    'attempts_max'   => env('ATTEMPTS_LOGIN_MAX_APP'),
                    'attempts_fulled'=> $user_attempts->fulled_attempts ];

        if($user_time) {
            $current_time = is_null($user_attempts->attempts_lock_time) ? false
                          : now()->diff($user_attempts->attempts_lock_time)->format("%I:%S");

            return array_merge(['current_time' => $current_time], $errors);
        }
        return $errors;
    }

    public function incrementAttemptsOnly($user, $returned = false)
    {
        $user_attempts = $user->incrementAttempts($user->document, 'APP', true); // permanent
        if($user_attempts) {
            // $responseAttempts = $this->sendAttempsAppResponse($user_attempts, false);
            $responseAttempts = $this->sendAttempsAppResponse($user_attempts);
            if($returned) return $responseAttempts;
            return $this->error('Intento fallido [IAO].', 400, $responseAttempts);
        }
    }
    // === ATTEMPTS ===

    // === RECAPTCHA ===
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

    public function validateRecaptcha($siteToken)
    {
        $secretKey = env('RECAPTCHA_TOKEN');
        $recaptchaUrl = env('RECAPTCHA_BASE_URL');

        // validamos token recaptcha
        $responseRecaptcha = Http::asForm()->post($recaptchaUrl, [
            'secret' => $secretKey,
            'response' => $siteToken
        ]);

        return $responseRecaptcha->json();
    }
    // === RECAPTCHA ===

    public function checkSameDataCredentials($userinput, $password, $send_email = true)
    {
        $user = auth()->user();
        $checkCredentials['require_quizz'] = ($userinput === $password) && !((bool) $user->email);
        $checkCredentials['id_user'] = $user->id;

        if(!$checkCredentials['require_quizz']) {
            return $this->sendEmailResetPassword($user, $checkCredentials, $send_email);
        }
        return $checkCredentials;
        // return $this->sendQuizzQuestionsValidate($user, $checkCredentials);
    }

    public function sendEmailResetPassword($user, $checkCredentials, $send_email = true)
    {
        $workspaceName = Workspace::find($user->subworkspace->parent_id)->name;
        $subWorkspaceName = $user->subworkspace->name;

        $mail_data = [ 'email' =>  $user->email,
                       'workspace' => $workspaceName,
                       'subworkspace' => $subWorkspaceName,
                       'fullname' => $user->name.' '.$user->lastname ];

        $status = "passwords.sent";
        if($send_email)
        {
            $userCallback = function ($user_instance, $token) {
                // enviar email
                $user_instance->sendPasswordRecoveryNotification($user_instance, $token);
            };

            $status = Password::sendResetLink(['email' => $user->email], $userCallback);
        }

        $checkCredentials['recovery_email']['success'] = ($status === Password::RESET_LINK_SENT);
        $checkCredentials['recovery_email']['data'] = $mail_data;

        return $checkCredentials;
    }

    public function sendQuizzQuestionsValidate($user, $checkCredentials)
    {
        $criteriaCodeStack = ['birthday_date', 'gender'];
        $callBackCode = function ($code) use ($user) {
            return [ $code => $user->getCriterionValueCode($code)->value_text ];
        };
        $stackAnswers = array_map($callBackCode, $criteriaCodeStack);
        $date = $user->getCriterionValueCode('birthday_date')->value_text;

        return [ 'user' => $stackAnswers,
                 'birht_date' => date('d-m-Y', strtotime($date)),
                 'checkCredentials' => $checkCredentials ];
        // dd(['user' => $user, 'checkCredentials' => $checkCredentials]);
    }

    // === QUIZZ ===
    public function quizz(QuizzAppRequest $request)
    {
        $request->validated();
        $user = User::find($request->id_user);

        // === validacion de intentos ===
        // $user_attempts = $user->checkTimeToReset($user->document, 'APP'); // reset intentos
        if($user->attempts == env('ATTEMPTS_LOGIN_MAX_APP')) {
            $user['fulled_attempts'] = true;
            $responseAttempts = $this->sendAttempsAppResponse($user);
            return $this->error('Intento fallido [Q].', 400, $responseAttempts);
        }
        // === validacion de intentos ===

        if($request->birthday_date) {
            $user_birth_date = $user->getCriterionValueCode('birthday_date');

            if(is_null($user_birth_date)){
                return $this->incrementAttemptsOnly($user);
                // no tiene fecha de nacimiento
                // $response = $this->incrementAttemptsOnly($user, true);
                // $response['birthday_date'] = 'No existe la fecha de nacimiento en este usuario';
                // return $this->error('Intento fallido', 400, $response);
            }

            $user_state_date = (date('d-m-Y', strtotime($user_birth_date->value_text)) == $request->birthday_date);
            if($user_state_date && !$request->gender) return $this->success(true, 'Respuesta correcta');
            if(!$user_state_date) return $this->incrementAttemptsOnly($user);
        }

        if($request->gender) {
            $user_prev_gender = $user->getCriterionValueCode('gender')->value_text;
            $user_gender = strtoupper(str_split($user_prev_gender)[0]); // primera letra
            $user_state_gender = ($user_gender == $request->gender);

            if($user_state_gender && $user_state_date) {
                $user->resetAttemptsUser(); // resetear intentos
                return $this->resetPasswordBuildToken($user);
            }
            return $this->incrementAttemptsOnly($user);
        }

        return $this->incrementAttemptsOnly($user);
    }
    // === QUIZZ ===

    // === RESET ===
    public function resetPasswordBuildToken($user, $returned = false)
    {
        $user['email'] = empty($user->email) ? $user->document : $user->email;
        $token = Password::createToken($user);

        $response = [ 'user_data'   => [ 'fullname' => $user->getFullnameAttribute(),
                                         'identifier' => $user->email ],
                      'user_token'  => $token,
                      'reset_days'  => env('RESET_PASSWORD_DAYS_APP'),
                      'first_reset' => is_null($user->last_pass_updated_at) ];

        return $this->success($response, 'Resetear Contraseña');
    }

    public function reset_password(PasswordResetAppRequest $request)
    {
        $data = $request->validated();

        if(!$request->email && !$request->document) return $this->error('no-auth', 503);

        $data_input['os'] = strip_tags($data['os'] ?? '');
        $data_input['version'] = strip_tags($data['version'] ?? '');

        $credentials = ($request->email) ? $request->only('email', 'password', 'password_confirmation', 'token')
                                         : $request->only('document', 'password', 'password_confirmation', 'token');

        $credentials1 = $credentials2 = $credentials3 = ['password' => $request->password];
        $userinput = $request->email ? $credentials['email'] : $credentials['document'];

        $credentials1['username'] = $userinput;
        $credentials2['document'] = $userinput;
        $credentials3['email'] = $userinput;
        if (Auth::attempt($credentials1) || Auth::attempt($credentials2) || Auth::attempt($credentials3)) {
            return $this->error('Contraseña no válida, asegúrate de crear una nueva contraseña.', 422);
        }
        // === prov el email a documento ===
        if(!$request->email) {
            $instance = new User;
            $instance->setDocumentAsEmail($request->document);
        }
        // === prov el email a documento ===

        $status = Password::reset($credentials, function($user, $password) use ($request) {

            $old_passwords = $user->old_passwords;

            $old_passwords[] = ['password' => bcrypt($password), 'added_at' => now()];

            if (count($old_passwords) > 4) {
                array_shift($old_passwords);
            }

            $user->old_passwords = $old_passwords;
            $user->password = $password;
            $user->last_pass_updated_at = now(); // actualizacion de contraseña
            $user->setRememberToken(Str::random(60));
            $user->save();

            $user_workspace = $user->subworkspace->parent_id;
            $functionality = $user_workspace ? WorkspaceFunctionality::getFunctionality( $user_workspace, 'send-credentials-to-email') : null;
            $hide_password = $request->password ? '******' . substr($request->password, -3) : '******';

            if($functionality && $request->email)
            {
                $mail_data = [ 'subject' => "Contraseña actualizada 🔐",
                            'user' => $user->name.' '.$user->lastname,
                            'email' => $request->email,
                            'password' => $hide_password
                            ];
                Mail::to($request->email)->send(new EmailTemplate('emails.enviar_credenciales_gestor', $mail_data));
            }
        });

        if($status == Password::PASSWORD_RESET) {

            if (Auth::attempt($credentials1) || Auth::attempt($credentials2) || Auth::attempt($credentials3)) {
                $user = Auth::user();

                if(!$request->email) $user->setInitialEmail();
                $user->resetAttemptsUser();

                $resp = $this->respondWithDataAndToken($data_input);

                if(is_array($resp))
                {
                    $ticket_user = Ticket::where('user_id', $user?->id)->where('status', 'pendiente')->where('reason', 'Soporte Login')->first();
                    $resp['mostrar_modal'] = $ticket_user ? $user?->email != $ticket_user?->email : false;
                }
                return $resp;
            }
        } else {

            if(!$request->email) {
                $instance = new User;
                $instance->setDocumentAsEmail($request->document, true);
            }
        }

        return $this->error('invalid-token', 503, $status);
    }
    // === RESET ===

    public function getRespondWithDataAndToken( $data )
    {
        return $this->respondWithDataAndToken( $data );
    }


    // === AMBIENTE ===
    public function getMediaUrl($media) {
        if ($media) {
           return get_media_url($media);
        }
        return $media;
    }

    public function configuracion_ambiente()
    {
        $ambiente = Ambiente::first();
        $customer = Customer::getCurrentSession();

        if($ambiente) {
            $ambiente['show_blog_btn'] = (bool) $ambiente->show_blog_btn;

            // gestor
            $ambiente->fondo = $this->getMediaUrl($ambiente->fondo);
            $ambiente->logo  = $this->getMediaUrl($ambiente->logo);
            $ambiente->icono = $this->getMediaUrl($ambiente->icono);
            $ambiente->logo_empresa = $this->getMediaUrl($ambiente->logo_empresa);
            // app
            $ambiente->fondo_app = $this->getMediaUrl($ambiente->fondo_app);
            $ambiente->logo_app  = $this->getMediaUrl($ambiente->logo_app);
            $ambiente->logo_cursalab = $this->getMediaUrl($ambiente->logo_cursalab);
            $ambiente->app_main_isotipo = $this->getMediaUrl($ambiente->logo_empresa);
            $ambiente->completed_courses_logo = $this->getMediaUrl($ambiente->completed_courses_logo);
            $ambiente->enrolled_courses_logo  = $this->getMediaUrl($ambiente->enrolled_courses_logo);
            $ambiente->diplomas_logo = $this->getMediaUrl($ambiente->diplomas_logo);
            $ambiente->male_logo   = $this->getMediaUrl($ambiente->male_logo);
            $ambiente->female_logo = $this->getMediaUrl($ambiente->female_logo);
            $service_status = true;
            if($customer){
                $service_status = $customer->hasServiceAvailable();
            }

            $ambiente->service = [
                'active' => $service_status,
                'message' => $service_status ? NULL : 'Tu espacio no se encuentra disponible. Comunícate con tu administrador para verificar el estado de tu plataforma.'
            ];

            return response()->json($ambiente);
        }
        return response()->json([]);
    }
    // === AMBIENTE ===
}
