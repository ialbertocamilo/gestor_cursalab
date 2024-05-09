<?php

namespace App\Http\Controllers\Auth;

use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\AuthenticatesUsers;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    //protected $redirectTo = '/home';

    protected $workspacesList = [];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    // public function login(Request $request)
    // {
    //     $this->validateLogin($request);

    //     // If the class is using the ThrottlesLogins trait, we can automatically throttle
    //     // the login attempts for this application. We'll key this by the username and
    //     // the IP address of the client making these requests into this application.
    //     if (method_exists($this, 'hasTooManyLoginAttempts') &&
    //         $this->hasTooManyLoginAttempts($request)) {
    //         $this->fireLockoutEvent($request);

    //         return $this->sendLockoutResponse($request);
    //     }

    //     if ($this->attemptLogin($request)) {
    //         if ($request->hasSession()) {
    //             $request->session()->put('auth.password_confirmed_at', time());
    //         }

    //         return $this->sendLoginResponse($request);
    //     }

    //     // If the login attempt was unsuccessful we will increment the number of attempts
    //     // to login and redirect the user back to the login form. Of course, when this
    //     // user surpasses their maximum number of attempts they will get locked out.
    //     $this->incrementLoginAttempts($request);

    //     return $this->sendFailedLoginResponse($request);
    // }
    public function showLoginFormInit()
    {
        if(session()->has('init_2fa')) {
            // resetear codigo y expiracion
            $user = auth()->user();
            $user->resetToNullCode2FA();

            session()->forget('init_2fa');
        }

        if(session()->has('init_reset')) {
            // resetear codigo y expiracion - reset pass
            $user = auth()->user();
            $user->resetToNullResetPass();

            session()->forget('init_reset');
        }

        // resetear sessions
        $this->guard()->logout();
        session()->invalidate();
        session()->regenerateToken();

        return $this->showLoginForm();
    }

    public function login(Request $request)
    {
        // In maintenance mode, stop login process

        if ($request->email_gestor != 'kevin@cursalab.io') {

            if (env('MAINTENANCE_MODE')) {

                throw ValidationException::withMessages([
                    $this->username() => [config('errors.maintenance_message')],
                ]);
            }
        }

        $customer = strtoupper(config('app.customer.slug'));

        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.

        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        $user = new User;

        // === intentos ===
        $userAttempt = $user->checkAttemptManualGestor($request);
        if($userAttempt) return $this->sendAttempsResponse($userAttempt);
        // === intentos ===

        if ($this->attemptLogin($request)) {

            $user = $this->guard()->user();
            $user->resetToNullCode2FA(); // reset 2fa values


            if (!$user->canAccessPlatform()) {

                $this->guard()->logout();

                $request->session()->invalidate();

                $request->session()->regenerateToken();

                return redirect('/plataforma-suspendida');

            } else {

                $roles = Role::getRolesAdminNames();

                // Al menos un workspace activo
                $workspaces_active = $user->isAn('super-user');

                if (!$workspaces_active) {
                    $workspaces_active = $user->getWorkspaces()->where('active', ACTIVE)->count() > 0;
                }

                if ( $user->isAn(...$roles) && $workspaces_active)
                {
                // if ( $user->isAn('super-user', 'admin', 'config', 'content-manager', 'trainer', 'reports','only-reports') )
                // {
                    if ($request->hasSession()) {
                        $request->session()->put('auth.password_confirmed_at', time());
                    }

                    $email = $user->email_gestor;
                    if(config('slack.routes.demo') && !str_contains($email, 'cursalab.io')){
                        $message = "[{$customer}] Cursalab 2.0";
                        $attachments = [
                            [
                                "color" => "#36a64f",
                                "text" => "El usuario con email: $email se ha logueado"
                            ]
                        ];
                        messageToSlackByChannel($message,$attachments,config('slack.routes.demo'));
                    }
                    $user->resetAttemptsUser(); // reset attempts

                    if($user->enable_2fa) {
                        // verificacion de doble autenticacion
                        $response2FA = (bool) $user->generateCode2FA();
                        if($response2FA) {
                            session()->put('init_2fa', $user->id);
                            return redirect('/2fa');
                        }
                        // verificacion de doble autenticacion

                    } else {

                        // === reset password ===
                        if($user->checkIfCanResetPassword()) {
                            return $this->showResetPassword();
                        }
                        // === reset password ===

                        return $this->sendLoginResponse($request); // login
                    }

                } else {
                    $this->guard()->logout();

                    $request->session()->invalidate();

                    $request->session()->regenerateToken();
                }
            }
        }

        // verificar intentos
        $user->checkTimeToReset($request->email);
        if(config('slack.routes.demo')){
            $message = "[{$customer}] Cursalab 2.0";
            $attachments = [
                [
                    "color" => "#FF0000",
                    "text" => 'El usuario con email: '.$request->email. ' intentó loguearse'
                ]
            ];
            messageToSlackByChannel($message,$attachments,config('slack.routes.demo'));
        }
        $user_attempts = $user->incrementAttempts($request->email);
        if($user_attempts) return $this->sendAttempsResponse($user_attempts);
        // verificar intentos

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.

        $this->incrementLoginAttempts($request);
        return $this->sendFailedLoginResponse($request);
    }

    // respuesta de error para intentos fallidos
    public function sendAttempsResponse($user_attempts)
    {
        $errors = [ 'current_time'   => now()->diff($user_attempts->attempts_lock_time)->format("%I:%S"),
                    'attempts_count' => $user_attempts->attempts,
                    'attempts_max'   => env('ATTEMPTS_LOGIN_MAX_GESTOR'),
                    'attempts_fulled'=> $user_attempts->fulled_attempts ];

        if(!$user_attempts->fulled_attempts) {
            $errors = array_merge( $errors, ['email' => [trans('auth.failed')]] );
        }

        throw ValidationException::withMessages($errors);
    }

    // redireccionar a link de reseteo
    public function showResetPassword() {
        $user = $this->guard()->user();
        session()->put('init_reset', $user->id);

        // crear token
        $currentEntropy = env('RESET_PASSWORD_TOKEN_ENTROPY_GESTOR');
        $token = bin2hex(random_bytes($currentEntropy));

        // insertar token 'table';
        $responseToken = (bool) $user->setUserPassUpdateToken($token);

        if($responseToken) {
            return redirect('reset/'.$token); // vista resetea token
        }
    }

    // actualizar contraseña usuario
    public function reset_pass(ResetPasswordRequest $request)
    {
        $request->validated();

        $currentToken = $request->token;
        $currentPassword = $request->password;
        $currentRePassword = $request->repassword;

        $user = auth()->user();

        // si es igual al email y/o contraseña existente
        if(Auth::attempt([ 'email_gestor' => $user->email_gestor,
                           'password' => $currentPassword]) ||  $user->email_gestor === $currentPassword) {
            throw ValidationException::withMessages([
                'password' => 'La contraseña ya ha sido utilizada anteriormente.'
            ]);
        }

        if($currentPassword === $currentRePassword) {

            // verificar token
            $checkToken = $user->checkPassUpdateToken($currentToken, $user->id);

            if($checkToken) {
                $user->updatePasswordUser($currentPassword);

                $user->resetToNullResetPass();
                session()->forget('init_reset');

                // === iniciar session ===
                return $this->sendLoginResponse($request);
            }
            return redirect('/login');
        }

        throw ValidationException::withMessages([
            'password' => 'El campo nueva contraseña no coincide.',
            'repassword' => 'El campo repetir nueva contraseña no coincide.'
        ]);
    }

    // verificar el codigo y expiracion 2FA
    public function auth2fa(Request $request)
    {
        $user = $this->guard()->user();
        $checkCode = ($request->code === $user->code);
        $checkExpire = ($user->expires_code > now());

        if($checkCode && $checkExpire) {
            session()->forget('init_2fa');
            $user->resetToNullCode2FA();

            // === reset password ===
            if($user->checkIfCanResetPassword()) {
                return $this->showResetPassword();
            }
            // === reset password ===

            return $this->sendLoginResponse($request); // login
        }

        throw ValidationException::withMessages([
            'code' => ['Código incorrecto y/o expirado.']
        ]);
    }

    // reenviar codigo 2FA
    public function auth2fa_resend()
    {
        $user = $this->guard()->user();
        $user->generateCode2FA();

        return back()->with('resend', 'Re-enviamos');
    }

    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        if ($response = $this->authenticated($request, $this->guard()->user())) {

            return $response;
        }

        return $request->wantsJson()
                    ? new JsonResponse([], 204)
                    : redirect()->intended($this->redirectPath());
    }

    public function authenticated(Request $request, $user,$redirect=true)
    {

        try {
            $nps = $this->getPollsNps($user);

            if (!is_null($nps) && !$nps->error)
                session(['nps' => $nps->data]);

        } catch (\Throwable $th) {
        }
        // Load user's workspaces

        $workspaces =  Workspace::loadUserWorkspaces($user->id);
        // Save first workspace in session, to be used
        // as the default workspace, since user has not
        // selected a workspace yet

        session(['workspace' => $workspaces[0]]);

        // When there is more than 1 workspace show
        // workspaces selector, or show welcome page
        // otherwise
        if(!$redirect){
            return count($workspaces) == 1 ? 'welcome' : 'workspaces.list';
        }
        if (count($workspaces) > 1) {

            // session()->forget('workspace');

            return redirect('/workspaces/list');

        } else {

            return redirect('/home');
        }
    }

    protected function attemptLogin(Request $request)
    {
        // return $this->guard()->attempt(
        //     $this->credentials($request), $request->boolean('remember')
        // );
        // if(Auth::attempt(['email_gestor' => $request->email, 'password' => $request->password, 'active' => 1])) {
        //     info(Auth::user());
        // }

        return (Auth::attempt(['email_gestor' => $request->email, 'password' => $request->password, 'active' => 1]));
    }

    protected function credentials(Request $request)
    {
        return $request->only($this->username(), 'password');
    }

    protected function getPollsNps($user = null)
    {
        $curl = curl_init();
        $email = isset($user->email_gestor) ? $user->email_gestor : null;
        $dni = isset($user->dni) ? $user->dni : null;

        curl_setopt_array($curl, array(
            CURLOPT_URL => env('URL_GET_NPS') . '?plataforma='.env('NPS_PLATFORM').'&version='.env('NPS_VERSION').'&dni='.$dni.'&email='.$email,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            echo 'Error:' . curl_error($curl);
        }

        curl_close($curl);

        return json_decode($response);
    }

}
