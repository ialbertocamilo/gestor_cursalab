<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\UserAppPasswordUpdateRequest;


class RestUserApiController extends Controller
{

    /**
     * Reset the given user's password.
     *
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     */
    // public function reset(Request $request): JsonResponse|RedirectResponse
    public function resetPassword(UserAppPasswordUpdateRequest $request)
    {
        $user = auth()->user();
        $old_passwords = $user->old_passwords;

        $old_passwords[] = ['password' => bcrypt($password), 'added_at' => now()];

        if (count($old_passwords) > 4) {
            array_shift($old_passwords);
        }

        $user->old_passwords = $old_passwords;
        $user->password = $password;
        $user->last_pass_updated_at = now();
        $user->setRememberToken(Str::random(60));
        $user->save();

        return $this->success(['message' => 'Contraseña actualizada correctamente.']);
        
        // return response()->json([
        //     'success' => $response == Password::PASSWORD_RESET
        // ]);
    }
}
