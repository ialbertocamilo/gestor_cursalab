<?php

namespace App\Http\Controllers\ApiRest;

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
// use Illuminate\Support\Str;
// use Illuminate\Support\Facades\Validator;
// use Illuminate\Validation\Rules\Password AS RulePassword;
// use LangleyFoxall\LaravelNISTPasswordRules\PasswordRules;
// use LangleyFoxall\LaravelNISTPasswordRules\Rules\ContextSpecificWords;
// use LangleyFoxall\LaravelNISTPasswordRules\Rules\DerivativesOfContextSpecificWords;
// use LangleyFoxall\LaravelNISTPasswordRules\Rules\RepetitiveCharacters;
// use LangleyFoxall\LaravelNISTPasswordRules\Rules\SequentialCharacters;

class RestUserApiController extends Controller
{

    /**
     * Reset the given user's password.
     *
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     */
    // public function reset(Request $request): JsonResponse|RedirectResponse
    public function resetPassword(Request $request)
    {
        // $request->validate($this->rules(), $this->validationErrorMessages());

        // // Here we will attempt to reset the user's password. If it is successful we
        // // will update the password on an actual user model and persist it to the
        // // database. Otherwise we will parse the error and return the response.

        // $response = $this->broker()->reset(
        //     $this->credentials($request), function ($user, $password) {


        //         $old_passwords = $user->old_passwords;

        //         $old_passwords[] = ['password' => bcrypt($password), 'added_at' => now()];


        //         // info('old_passwords');
        //         // info($old_passwords);
        //         // info(count($old_passwords));

        //         if (count($old_passwords) > 4) {
        //             array_shift($old_passwords);
        //         }

        //         $user->old_passwords = $old_passwords;
        //         $user->password = $password;
        //         $user->last_pass_updated_at = now();
        //         $user->setRememberToken(Str::random(60));
        //         $user->save();
        //     }
        // );

        // // info('response');
        // // info($response);
        // // info(Password::PASSWORD_RESET);

        // return response()->json([
        //     'success' => $response == Password::PASSWORD_RESET
        // ]);
    }
}
