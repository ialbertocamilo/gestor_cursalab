<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Mail\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class ForgotPasswordController extends Controller
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

    public function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);

        // Obtener el correo electrónico ingresado por el usuario
        $email = $request->input('email');
        $user = User::where('email_gestor',$email)->where('active',1)->first();
        // Verificar Sí el usuario existe
        if ($user) {
                        
            $token = $this->generateCustomToken($user);
            // Enviar correo
            $this->sendCustomEmail($user,$token);
            
            return back()->with('status', 'El correo se envió con exito.');
        } 
        
        // Error: No se pudo enviar el correo de reinicio de contraseña
        return back()->withErrors(
            ['email' => '*El correo que ingresaste no se encuentra registrado, intenta con uno distinto']
        );
            
    }

    private function sendCustomEmail($user,$token){
        $url_to_reset = url(route('password.reset', [
            'token' => $token,
            // 'email' => $user->email_gestor
            ], false));

        $mail_data = [ 'subject' => 'Reinicio de contraseña',
                    'user' => $user->name.' '.$user->lastname,
                    'url_to_reset' => $url_to_reset,
                    'minutes' => 60,
                    ];
        Mail::to($user->email_gestor)->send(new EmailTemplate('emails.reset_password_gestor', $mail_data));
    }

    private function generateCustomToken($user){
        // Obtener el registro existente, si lo hay
        $existingRecord = DB::table('password_resets')
            ->where('email', $user->email_gestor)
            ->first();
        if ($existingRecord) {
            // Verificar si el token existente aún es válido
            $expirationTime = Carbon::parse($existingRecord->created_at)->addHours(1);
            if (Carbon::now()->lt($expirationTime)) {
                // El token todavía es válido, no se necesita generar uno nuevo
                return $existingRecord->token;
            }
        }
        $expiration = Carbon::now()->addHour();
        // Generar y almacenar un nuevo token
        $token = Hash::make($this->broker()->createToken($user, $expiration));
        $token = str_replace('/', '-', $token);
        DB::table('password_resets')->updateOrInsert(
            ['email' => $user->email_gestor],
            ['token' => $token, 'created_at' => now()]
        );
    
        return $token;
    }
}
