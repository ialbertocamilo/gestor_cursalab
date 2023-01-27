<?php

namespace App\Http\Controllers\Auth;

use App\Models\Workspace;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\ResetPasswordRequest;


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
            //resetear codigo y expiracion
            $user = auth()->user();
            $user->resetToNullCode2FA();

            session()->forget('init_2fa'); 
        }

        if(session()->has('init_reset')) {
            //resetear codigo y expiracion - reset pass
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

        if ($request->email != 'kevin@cursalab.io') {

            if (env('MAINTENANCE_MODE')) {

                throw ValidationException::withMessages([
                    $this->username() => [config('errors.maintenance_message')],
                ]);
            }
        }


        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {


            $user = $this->guard()->user();
            $user->resetToNullCode2FA(); // reset 2fa values

            if ( $user->isAn('super-user', 'admin', 'config', 'content-manager', 'trainer', 'reports','only-reports') )
            {

                if ($request->hasSession()) {
                    $request->session()->put('auth.password_confirmed_at', time());
                }

                if($user->enable_2fa) {
                    // verificacion de doble autenticacion
                    $response2FA = (bool) $user->generateCode2FA();
                    if($response2FA) { 
                        session()->put('init_2fa', $user->id);
                        return redirect('/2fa');
                    }
                    // verificacion de doble autenticacion

                } else {

                    // verifica si se requiere actualizar contraseña
                    if($this->checkIfCanResetPassword()) {
                        return $this->showResetPassword();
                    }
                    // verifica si se requiere actualizar contraseña

                    // === iniciar session ===
                    return $this->sendLoginResponse($request);
                }
               
            } else {
                $this->guard()->logout();

                $request->session()->invalidate();

                $request->session()->regenerateToken();
            }
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    // redireccionar a link de reseteo
    public function showResetPassword() {
        $user = $this->guard()->user();
        session()->put('init_reset', $user->id);

        // crear token 
        $currentEntropy = env('RESET_PASSWORD_TOKEN_ENTROPY');
        $token = bin2hex(random_bytes($currentEntropy));

        // insertar token 'table';
        $responseToken = (bool) $user->setUserPassUpdateToken($token);
        
        if($responseToken) {
            return redirect('reset/'.$token); // vista resetea token
        }
    }

    // verificar si debe actualizar su contraseña
    public function checkIfCanResetPassword()
    {
        $user = $this->guard()->user();
        $currentDays = env('RESET_PASSWORD_DAYS');
        settype($currentDays, "int");

        $diferenceDays = now()->diffInDays($user->last_pass_updated_at);
        return ($diferenceDays >= $currentDays);
        // return ($diferenceDays >= $currentDays) && $user->enable_resetpass;
    }
    
    // actualizar contraseña usuario
    public function reset_pass(ResetPasswordRequest $request)
    {
        $request->validated();

        $currentToken = $request->token;
        $currentPassword = $request->password;
        $currentRePassword = $request->repassword;

        if($currentPassword === $currentRePassword) {
            $user = auth()->user();
            
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
            'password' => 'El campo contraseña no coincide.',
            'repassword' => 'El campo repetir contraseña no coincide.'
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

            // verifica si se requiere actualizar contraseña
            if($this->checkIfCanResetPassword()) {
                return $this->showResetPassword();
            }
            // verifica si se requiere actualizar contraseña

            // === iniciar session ===
            return $this->sendLoginResponse($request);
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
            
        throw ValidationException::withMessages([
            'resend' => 'Re-enviamos'
        ]);
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

    protected function authenticated(Request $request, $user)
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

        if (count($workspaces) > 1) {

            // session()->forget('workspace');

            return redirect('/workspaces/list');

        } else {

            return redirect('/welcome');
        }
    }

    protected function attemptLogin(Request $request)
    {
        // return $this->guard()->attempt(
        //     $this->credentials($request), $request->boolean('remember')
        // );
        return (Auth::attempt(['email' => $request->email, 'password' => $request->password, 'active' => 1]));
    }

    protected function credentials(Request $request)
    {
        return $request->only($this->username(), 'password');
    }

    protected function getPollsNps($user = null)
    {
        $curl = curl_init();
        $email = isset($user->email) ? $user->email : null;
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
