<?php

namespace App\Providers;

use App\Models\Account;
use App\Models\Attendant;
use App\Models\Meeting;
use App\Models\Taxonomy;
use App\Models\User;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Validator;
use Illuminate\Support\Facades\Hash;

class ValidationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('letters', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/\pL/', $value);
        });

        Validator::replacer('letters', function($message, $attribute, $rule, $parameters) {
            return str_replace($message, "El campo debe tener al menos una letra.", $message);
        });

        Validator::extend('numbers', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/\pN/', $value);
        });

        Validator::replacer('numbers', function($message, $attribute, $rule, $parameters) {
            return str_replace($message, "El campo debe tener al menos un número.", $message);
        });

        Validator::extend('case_diff', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/(\p{Ll}+.*\p{Lu})|(\p{Lu}+.*\p{Ll})/u', $value);
        });

        Validator::replacer('case_diff', function($message, $attribute, $rule, $parameters) {
            return str_replace($message, "El campo debe tener minúsculas y mayúsculas.", $message);
        });

        Validator::extend('password_available', function ($attribute, $value, $parameters, $validator) {

            $user_id = $parameters[0] ?? NULL;

            $user = User::where('id', $user_id)->select('old_passwords')->first();

            if (!$user) return true;
            if (!$user->old_passwords) return true;

            foreach ($user->old_passwords as $key => $row) {
                if (Hash::check($value, $row['password'])) {
                    return false;                    
                }
            }

            return true;
        });

        Validator::replacer('password_available', function($message, $attribute, $rule, $parameters) {
            return str_replace($message, "La contraseña ya ha sido utilizada anteriormente.", $message);
        });


        Validator::extend('account_type_available', function ($attribute, $value, $parameters, $validator) {

            $data = $validator->getData();

            $meeting_id = $parameters[0] ?? NULL;

            $meeting = $meeting_id ? Meeting::find($meeting_id) : NULL;

            $type = Taxonomy::find($value);

            if ($meeting AND ! $meeting->datesHaveChanged($data) AND ! $meeting->typeHasChanged($type)) return true;


            $dates = Account::getDatesToSchedule($data['starts_at'], $data['finishes_at']);

            return Account::getAvailablesForMeeting($type, $dates, $meeting, 'count');
        });

        Validator::extend('available_for_meeting', function ($attribute, $value, $parameters, $validator) {
            // return true;
            $data = $validator->getData();

            $meeting_id = $parameters[0] ?? NULL;

            $usuario = Usuario::find($value);
            $dates = Account::getDatesToSchedule(
                $data['starts_at'], $data['finishes_at'], 0
            );

            $meeting = $meeting_id ? Meeting::find($meeting_id) : NULL;

            return Attendant::isAvailableToHostMeeting($usuario, $dates, $meeting);
        });

        Validator::extend('meeting_can_be_finished', function ($attribute, $value, $parameters, $validator) {
            $data = $validator->getData();

            $user = auth()->user();

            $meeting = Meeting::find($value);

            if (!$meeting->isLive())
                return false;

            if ($user instanceof User)
                return true;

            return $user->id === $meeting->host_id;
        });

        Validator::extend('meeting_date_after_or_equal_today', function ($attribute, $value, $parameters, $validator) {
            $data = $validator->getData();

            $meeting_id = $parameters[0] ?? NULL;

            $meeting = $meeting_id ? Meeting::find($meeting_id) : NULL;
            $dates = Account::getDatesToSchedule($data['starts_at'], $data['finishes_at'], 0);

            if ($meeting AND $meeting->isLive() AND !$meeting->datesHaveChanged($dates))
                return true;

            return $dates['starts_at'] > Carbon::now();
        });

        // Validator::extend('current_password', function($attribute, $value, $parameters, $validator) {
        //     return Hash::check($value, auth()->user()->password);
        // });

        // return preg_match('/\p{Z}|\p{S}|\p{P}/', $value);
    }
}
