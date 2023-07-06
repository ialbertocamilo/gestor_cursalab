<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Rules\CustomContextSpecificWords;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password AS RulePassword;
use LangleyFoxall\LaravelNISTPasswordRules\PasswordRules;
use LangleyFoxall\LaravelNISTPasswordRules\Rules\ContextSpecificWords;
use LangleyFoxall\LaravelNISTPasswordRules\Rules\DerivativesOfContextSpecificWords;
use LangleyFoxall\LaravelNISTPasswordRules\Rules\RepetitiveCharacters;
use LangleyFoxall\LaravelNISTPasswordRules\Rules\SequentialCharacters;

class ResetPasswordApiController extends Controller
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
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        $field = request()->email ? 'email' : 'document';
        $value = request()->email ? request()->email : request()->document;

        $user = User::where($field, $value)->first(); 
        $user_id = $user->id ?? NULL; 

        // info('ResetPasswordApiController');
        // info('request->all()');
        // info(request()->all());
        // info('user');
        // info($user);

        $passwordRules = [
            "required", 'confirmed', 'max:100',
            RulePassword::min(8)->letters()->numbers()->symbols(),
            "password_available:{$user_id}",

            // new ContextSpecificWords($user->email ?? NULL),
            // new ContextSpecificWords($user->document ?? NULL),
            // new ContextSpecificWords($user->name ?? NULL),
            // new ContextSpecificWords($user->lastname ?? NULL),
            // new ContextSpecificWords($user->surname ?? NULL),
            
            new CustomContextSpecificWords($user->email ?? NULL, 'email'),
            new CustomContextSpecificWords($user->document ?? NULL, 'document'),
            new CustomContextSpecificWords($user->name ?? NULL, 'name'),
            new CustomContextSpecificWords($user->lastname ?? NULL, 'lastname'),
            new CustomContextSpecificWords($user->surname ?? NULL, 'surname'),
            
            // new RepetitiveCharacters(),
            // new SequentialCharacters(),
        ];

        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => $passwordRules,
            // 'password' => ['required', 'confirmed'],
        ];
    }

    protected function validationErrorMessages() 
    {
        return [
                'password.password_available' => 'Has usado esa contraseña previamente, intenta con una nueva.'
            ];
    }

    /**
     * Reset the given user's password.
     *
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     */
    // public function reset(Request $request): JsonResponse|RedirectResponse
    public function reset(Request $request)
    {
        
        // $validator = Validator::make($request->all(), $this->rules());
        // if ($validator->fails()) {
        //     $errors = $validator->errors();
        //     info(['errors' => $errors]);
        // }

        $request->validate($this->rules(), $this->validationErrorMessages());

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.

        $response = $this->broker()->reset(
            $this->credentials($request), function ($user, $password) {


                $old_passwords = $user->old_passwords;

                $old_passwords[] = ['password' => bcrypt($password), 'added_at' => now()];


                // info('old_passwords');
                // info($old_passwords);
                // info(count($old_passwords));

                if (count($old_passwords) > 4) {
                    array_shift($old_passwords);
                }

                $user->old_passwords = $old_passwords;
                $user->password = $password;
                $user->last_pass_updated_at = now();
                $user->setRememberToken(Str::random(60));
                $user->save();
            }
        );

        // info('response');
        // info($response);
        // info(Password::PASSWORD_RESET);

        return response()->json([
            'success' => $response == Password::PASSWORD_RESET
        ]);
    }
}
