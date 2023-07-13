<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginAppImpersonationRequest;
use App\Models\Error;
use App\Models\Workspace;
use App\Models\Usuario;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Hash;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class AuthImpersonationController extends Controller
{
    public function getData()
    {
        $data = [
            'enabled' => config('app.impersonation.enabled'),
            'fields' => config('app.impersonation.fields'),
            'button' => config('app.impersonation.button'),
        ];

        return compact('data');
    }

    public function login(LoginAppImpersonationRequest $request)
    {
        try {

            $data = $request->validated();

            $enabled = config('app.impersonation.enabled');
            $code = config('app.impersonation.code') ?? $this->getDefaultCode();

            if (!$enabled) return ['status' => 'success', 'message' => Inspiring::quote(), 'step' => 1];

            if ($code != strtolower($data['code'])) return ['status' => 'success', 'message' => Inspiring::quote(), 'step' => 2];

            $admin = User::where('email_gestor', $data['username'])->where('active', ACTIVE)->first();

            if (!$admin || $admin->isNotAn('super-user', 'admin')) return ['status' => 'success', 'message' => Inspiring::quote(), 'step' => 3];

            $checkPassword = Hash::check($data['password'], $admin->password);

            if (!$checkPassword) return ['status' => 'success', 'message' => Inspiring::quote(), 'step' => 4];

            $user = User::where('document', $data['document'])->first();

            if (!$user) return ['status' => 'success', 'message' => Inspiring::quote(), 'step' => 5];

            // Auth::login($user);

            // $user->resetAttemptsUser();

            $responseUserData = $this->respondWithDataAndToken($user);

            return response()->json($responseUserData);

        } catch (\Exception $e) {

            dd($e);

            // Error::storeAndNotificateException($e, request());

            return ['status' => 'error', 'message' => Inspiring::quote()];
        }
    }

    private function respondWithDataAndToken($user)
    {
        // $user->tokens('accessTokenImpersonation')->delete();
        $token = $user->createToken('accessTokenImpersonation')->accessToken;

        if (!$user->active)
            return $this->error('Tu cuenta se encuentra inactiva.
            Comunícate con tu coordinador para enviar una solicitud de activación.', http_code: 503);

        $user->load('criterion_values.criterion');
        // $user->updateUserDeviceVersion($data);
        // $user->updateLastUserLogin($data);

        $config_data = Workspace::with('main_menu', 'side_menu')->select('id', 'logo', 'mod_evaluaciones', 'plantilla_diploma', 'contact_support')
            ->where('id', $user->subworkspace_id)
            ->first();

        $workspace = Workspace::find($user->subworkspace_id);

        $supervisor = $user->isSupervisor();
        // $can_be_host = $user->belongsToSegmentation($workspace);

        $workSpaceIndex = $user->subworkspace->parent_id;
        $current_hosts = Usuario::getCurrentHosts(true, $workSpaceIndex);
        $can_be_host = in_array($user->id, $current_hosts);

        $workspace_data = ($workspace->parent_id) ? Workspace::select('logo', 'slug', 'name', 'id')->where('id', $workspace->parent_id)->first() : null;
        if ($workspace_data) {
            $workspace_data->logo = get_media_url($workspace_data->logo);

            if ($workspace_data->slug == 'farmacias-peruanas') {
                $workspace_data->logo = get_media_url($user->subworkspace->logo);
            }
        }
        if ($user->subworkspace->logo) {
            $user->subworkspace->logo = get_media_url($user->subworkspace->logo);
        }

        $ciclo_actual = null;
        if ($user->subworkspace->parent_id == 25){
            $ciclo_actual = $user->getActiveCycle()?->value_text;
        }

        $criterios = [];

        foreach ($user->criterion_values as $value) {
            $criterios[] = [
                'valor' => $value->value_text,
                'tipo' => $value->criterion->name ?? null,
            ];
        }

        $user_data = [
            "id" => $user->id,
            "dni" => $user->document,
            "nombre" => $user->name ?? '',
            "apellido" => $user->lastname ?? '',
            "full_name" => $user->fullname,
            // 'criteria' => $user->criterion_values,
            'rol_entrenamiento' => $user->getTrainingRole(),
            'supervisor' => !!$supervisor,
            'module' => $user->subworkspace,
            'workspace' => $workspace_data,
            'can_be_host' => $can_be_host,
            'ciclo_actual' => $ciclo_actual,
            'android' => $user->android,
            'ios' => $user->ios,
            'huawei' => $user->huawei,
            'criterios' => $criterios,
        ];

        $config_data->app_side_menu = $config_data->side_menu->pluck('code')->toArray();
        $config_data->app_main_menu = $config_data->main_menu->pluck('code')->toArray();

        $config_data->full_app_main_menu = Workspace::getFullAppMenu('main_menu', $config_data->app_main_menu);
        $config_data->full_app_side_menu = Workspace::getFullAppMenu('side_menu', $config_data->app_side_menu);
        $config_data->filters = config('data.filters');
        $api_url = config('app.url');

        return [
            'url_workspace'=>[
                'api_url'=> $api_url .'/api',
                'gestor_url'=> $api_url,
                'app_url'=> ENV('url_app'),
                'reportes_url'=>env('REPORTES_URL')
            ],
            'access_token' => $token,
            'bucket_base_url' => get_media_url(),
            'config_data' => $config_data,
            'usuario' => $user_data
        ];
    }

    public function getDefaultCode()
    {
        $months = config('data.months');

        $current_day = date('j');
        $current_month = date('n');
        $current_year = date('Y');

        $month = strtolower($months[$current_month]);

        return "{$current_day}{$month}{$current_year}";
    }

    public function external($token, Request $request)
    {
        try {

            $enabled = config('app.impersonation.enabled');

            if (!$enabled) return $this->error('Service not available.', http_code: 503);

            $token = Crypt::decryptString($request->token);

            $ids = explode('-', $token);

            $user_id = $ids[0] ?? null;
            $gestor_id = $ids[1] ?? null;

            $user = User::find($user_id);
            $gestor = User::find($gestor_id);

            if ($user && $gestor) {

                $data = $this->respondWithDataAndToken($user);

                $data['config_data']['impersonation'] = [
                    'show_bar' => true,
                    'show_title' => 'Accediste como ' . $user->fullname,
                    'user' => $gestor->fullname,
                ];

                return response()->json($data);
            }

            return $this->error('Wrong token.', http_code: 503);

        } catch (\Exception $e) {

            return $this->error('Error found.', http_code: 503);
        }
    }

}
