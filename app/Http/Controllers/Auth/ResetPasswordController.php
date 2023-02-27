<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/login';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Override setUserPassword method from ResetsPasswords Trait
     * @param $user
     * @param $password
     * @return void
     */
    protected function setUserPassword($user, $password)
    {
        $user->password = $password;
    }

    public function showResetFormInit(Request $request)
    {
        $currentToken = $request->token;

        if(!$currentToken) {
            return redirect('/login');
        }
        // verificar existencia del token
        $user = auth()->user();
        $checkToken = $user->checkPassUpdateToken($currentToken, $user->id);

        if(!$checkToken) {
            return redirect('/login');
        }

        $is_new_pass = $user->last_pass_updated_at ? 'Expiró la vigencia de '.env('RESET_PASSWORD_DAYS').' días para tu contraseña. Por seguridad debes actualizarla.' : 'Por seguridad debes actualizar tu contraseña a una nueva.';

        return view('auth.passwords.reset_pass', [ 'token' => $currentToken,
                                                   'message' => $is_new_pass ]);
    }
}
