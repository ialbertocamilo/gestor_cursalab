<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\ResetsPasswords;

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
    protected function reset(Request $request)
    {
        $user = User::where('email_gestor', $request->email)->where('active',1)->first();
        $this->validate($request, $this->rules($user), $this->validationErrorMessages());
        // Encuentra al usuario por el correo electrónico
        $token = $request->token;
        $password = $request->password;
        // Verificar si el token es válido y no ha expirado
        $resetRecord = DB::table('password_resets')
            ->where('token', $token)
            ->first();

        if (!$resetRecord || Carbon::parse($resetRecord->created_at)->addHour()->isPast()) {
            // El token no es válido o ha expirado
            return redirect()->back()
                ->withInput($request->only('email'))
                ->withErrors(['status' => 'El token ha expirado.']);
        }
        if (!$user) {
            return redirect()->back()
                ->withInput($request->only('email'))
                ->withErrors(['status' => 'No se pudo restablecer la contraseña.']);
            // El usuario no existe, realiza acciones adecuadas (redireccionar, mostrar mensaje, etc.)
        }
        // Actualiza la contraseña del usuario y elimina el token
        $user->updatePasswordUser($password);
        DB::table('password_resets')->where('token', $token)->delete();
        $loginController = new LoginController();
        Auth::loginUsingId($user->id);
        $loginController->authenticated($request,$user,false);
        return redirect()->route('login')->with('status', 'Tu contraseña ha sido restablecida.');
    }
    // Sobrescribe el método sendResetResponse() para realizar la redirección
    protected function sendResetResponse($view)
    {
        return view($view);
    }
    public function showResetForm(Request $request)
    {
        $token = $request->token;
        // Verificar si el token es válido y no ha expirado
        $resetRecord = DB::table('password_resets')
            ->where('token', $token)
            ->first();

        if (!$resetRecord || Carbon::parse($resetRecord->created_at)->addHour()->isPast()) {
            // El token no es válido o ha expirado
            return view('auth.passwords.reset')->with(
                ['token' => $token, 'email' => null,'showErrorModal' => true ]
            );
        }

        return view('auth.passwords.reset')->with(
            ['token' => $token, 'email' => $resetRecord->email,'showErrorModal'=>false]
        );
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

        $is_new_pass = $user->last_pass_updated_at ? 'Expiró la vigencia de '.env('RESET_PASSWORD_DAYS_GESTOR').' días para tu contraseña. Por seguridad debes actualizarla.' : 'Por seguridad debes actualizar tu contraseña a una nueva.';

        return view('auth.passwords.reset_pass', [ 'token' => $currentToken,
                                                   'message' => $is_new_pass ]);
    }
    protected function rules($user)
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed','max:100', "password_available:{$user->id}",
                        Password::min(8)->letters()->numbers()->symbols(),
                        function ($attribute, $value, $fail) use ($user) {
                            $attributesToCheck = ['name', 'lastname', 'surname'];
                            foreach ($attributesToCheck as $attributeToCheck) {
                                if (strpos(strtolower($value), strtolower($user->{$attributeToCheck})) !== false) {
                                    $fail("No puedes incluir tu nombre ni apellido '{$user->$attributeToCheck}' en la contraseña.");
                                }
                            }
                            if (strpos(strtolower($value), strtolower($user->email_gestor)) !== false) {
                                $fail("No puedes incluir tu correo electronico en la contraseña.");
                            }
                            if ($user->document && strpos(strtolower($value), strtolower($user->document)) !== false) {
                                $fail("No puedes incluir tu documento en la contraseña.");
                            }
                        }
                    ],
        ];
    }

    protected function validationErrorMessages()
    {
        return [
            'password.min' => 'La contraseña debe tener al menos :min caracteres.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }
}
