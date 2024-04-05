<?php

namespace App\Http\Controllers\ApiRest;

use Exception;
use App\Models\User;
use App\Models\Error;
use App\Models\Usuario;
use App\Models\Ambiente;
use App\Models\Workspace;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;

use Illuminate\Support\Facades\Password;
use App\Http\Requests\LoginAppImpersonationRequest;
use App\Models\Process;
use App\Models\Taxonomy;
use Illuminate\Contracts\Encryption\DecryptException;

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
        
        $type_employee_onboarding = Taxonomy::getFirstData('user','type', 'employee_onboarding');

        if($user->type_id == $type_employee_onboarding?->id) {
            $onboarding = true;
            $supervisor_induccion = count($user->processes) ? true : false;
            $supervisor = false;
            $processes = Process::getProcessesAssigned($user);
        }
        else {
            $onboarding = false;
            $supervisor_induccion = false;
            $supervisor = $user->isSupervisor();
            $processes = [];
        }
        // $can_be_host = $user->belongsToSegmentation($workspace);

        $workSpaceIndex = $user->subworkspace->parent_id;
        $current_hosts = Usuario::getCurrentHosts(true, $workSpaceIndex);
        $can_be_host = in_array($user->id, $current_hosts);

        //CUSTOM DESIGN BY WORKSPACE
        $custom_ambiente = Ambiente::getCustomAmbienteByWorkspace($workspace->parent_id);
        $workspace_data = ($workspace->parent_id) ? Workspace::select('share_diplomas_social_media', 'show_logo_in_app', 'logo', 'slug', 'name', 'id')->where('id', $workspace->parent_id)->first() : null;
        
        if ($workspace_data) {
            $show_logo_in_app = $workspace_data->show_logo_in_app ?? false;

            if ($show_logo_in_app) {
                $workspace_data->logo = get_media_url($workspace_data->logo);
            } else {
                $ambiente = Ambiente::first();
                $workspace_data->logo = get_media_url($ambiente->logo);
            }
        }

        if ($user->subworkspace->logo) {
            $user->subworkspace->logo = get_media_url($user->subworkspace->logo);

            if ($user->subworkspace->show_logo_in_app) {
                $workspace_data->logo = $user->subworkspace->logo;
            }
        }

        $ciclo_actual = null;

        if ($user->subworkspace->parent_id == 25){
            $ciclo_actual = $user->getActiveCycle()?->value_text;
        }

        $criterios = $user->getProfileCriteria();

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
            'supervisor_induccion' => $supervisor_induccion,
            'processes' => $processes,
            'onboarding' => $onboarding
        ];

        if($user->type_id == $type_employee_onboarding?->id) {
            if($supervisor_induccion) {
                $config_data->app_side_menu = [
                    'ind_asistencia',
                    'ind_procesos',
                    'ind_faq'
                ];
                $config_data->app_main_menu = [
                    'ind_home_sup',
                    'ind_asistencia',
                    'ind_procesos'
                ];
                $config_data->full_app_main_menu = [
                    'ind_home_sup' => true,
                    'ind_asistencia' => true,
                    'ind_procesos' => true
                ];
                $config_data->full_app_side_menu = [
                    'ind_asistencia' => true,
                    'ind_procesos' => true,
                    'ind_faq' => true,
                ];
            }
            else {
                $config_data->app_side_menu = [
                    'ind_avance',
                    'ind_ruta',
                    'ind_certificado',
                    'ind_faq'
                ];
                $config_data->app_main_menu = [
                    'ind_home',
                    'ind_ruta',
                    'ind_faq'
                ];
                $config_data->full_app_main_menu = [
                    'ind_home' => true,
                    'ind_ruta' => true,
                    'ind_faq' => true
                ];
                $config_data->full_app_side_menu = [
                    'ind_avance' => true,
                    'ind_ruta' => true,
                    'ind_certificado' => true,
                    'ind_faq' => true,
                ];
            }
        }
        else{
            $config_data->app_side_menu = $config_data->side_menu->pluck('code')->toArray();
            $config_data->app_main_menu = $config_data->main_menu->pluck('code')->toArray();

            $config_data->full_app_main_menu = Workspace::getFullAppMenu('main_menu', $config_data->app_main_menu, $user);
            $config_data->full_app_side_menu = Workspace::getFullAppMenu('side_menu', $config_data->app_side_menu, $user);
        }
        $config_data->filters = config('data.filters');
        $config_data->meetings_upload_template = config('app.meetings.app_upload_template');
        $api_url = config('app.url');
        
        return [
            'url_workspace'=>[
                'api_url'=> $api_url .'/api',
                'gestor_url'=> $api_url,
                'app_url'=> ENV('url_app'),
                'reportes_url'=>env('REPORTES_URL')
            ],
            'access_token' => $token,
            'bucket_base_url' => get_media_root_url(),
            'config_data' => $config_data,
            'usuario' => $user_data,
            'custom_ambiente'=> $custom_ambiente
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

            info($e);

            return $this->error('Error found.', http_code: 503);
        }
    }

}
