<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class ForgotPasswordApiController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

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
     * Override method from SendsPasswordResetEmails trait
     * Send a reset link to the given user.
     *
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     */
    public function sendResetLinkEmail(Request $request): JsonResponse|RedirectResponse
    {
        $this->validateEmail($request);

        // Check if the email exists and if the user is active
        $userExists = $this->existe_email($request, $request->email);
        if (!$userExists['existe_email']) {
            // Usuario no existe
            return response()->json([
                'success' => false,
                'message' => 'El usuario no existe.'
            ], 404);
        } elseif ($userExists['mensaje'] !== 'El usuario está activo.') {
            // Usuario existe pero no está activo
            return response()->json([
                'success' => false,
                'message' => $userExists['mensaje']
            ], 404);
        }

        // The user exists and is active, proceed with sending the reset link

        \DB::table('password_resets')->where('email', $request->email)->delete();

        $response = $this->broker()->sendResetLink(
            $this->credentials($request)
        );

        return response()->json([
            'success' => $response == Password::RESET_LINK_SENT,
            'message' => 'Se ha enviado un correo electrónico con el enlace para restablecer la contraseña.'
        ]);
    }
        public function existe_email(Request $request, $email = null)
    {
        $existe_email = $email ? User::where('email', $email)->exists() : false;
        $mensaje = '';

        if ($existe_email) {
            $user = User::where('email', $email)->first();
            if ($user->active) {
                $mensaje = 'El usuario está activo.';
                $codigo_http = 200; // OK
            } else {
                $mensaje = 'El usuario está inactivo.';
                $codigo_http = 403; // Prohibido
            }
        } else {
            $mensaje = 'El usuario no existe.';
            $codigo_http = 404; // No encontrado
        }

        return [
            'existe_email' => (bool) $existe_email,
            'mensaje' => $mensaje
        ];
    }


}
