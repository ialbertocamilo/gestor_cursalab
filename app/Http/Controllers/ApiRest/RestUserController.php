<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\UserAppPasswordUpdateRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RestUserController extends Controller
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

        $old_passwords[] = ['password' => bcrypt($request->newPassword), 'added_at' => now()];

        if (count($old_passwords) > 4) {
            array_shift($old_passwords);
        }

        $user->old_passwords = $old_passwords;
        $user->password = $request->newPassword;
        $user->last_pass_updated_at = now();
        $user->setRememberToken(Str::random(60));
        $user->save();

        return $this->success(['message' => 'Contraseña actualizada correctamente.']);

        // return response()->json([
        //     'success' => $response == Password::PASSWORD_RESET
        // ]);
    }

    /**
     * Load user's visible notifications
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function loadNotifications(Request $request) {

        $user = User::find(32353);//;auth()->user();
        $notications = UserNotification::loadUserNotifications($user->id);

        return response()->json($notications);
    }

    /**
     * Update user's notification visible and read statuses
     *
     * @param Request $request
     * @param UserNotification $userNotification
     * @return JsonResponse
     */
    public function updateNoficationStatus(Request $request, UserNotification $userNotification) {

        if (isset($request['markAsRead'])) {
            $userNotification->markAsRead();
        }

        if (isset($request['hide'])) {
            $userNotification->hide();
        }

        return response()->json(['success' => true]);
    }
}
