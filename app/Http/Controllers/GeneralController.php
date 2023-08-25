<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Requests\GeneralStorageRequest;
use App\Http\Resources\ResourceGeneralSubWorkspaceStatus;
use App\Http\Resources\ResourceGeneralWorkspaceStatus;
use App\Http\Resources\ResourceListGeneralWorkspacesStatus;
use App\Mail\EmailTemplate;
use App\Models\Criterion;
use App\Models\Prueba;
use App\Models\Workspace;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class GeneralController extends Controller
{
    public function getModulos()
    {
        $current_workspace = get_current_workspace();
        $modulos = $current_workspace->subworkspaces->toArray();
        // $modulos = Criterion::getValuesForSelect('module');

        return $this->success(compact('modulos'));
    }

    /**
     * Process request to load stats for dashboard
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getCardsInfo(Request $request)
    {
        if ($request->refresh === 'true')
            cache()->flush();

        $current_workspace = get_current_workspace();
        $modulos = $current_workspace->subworkspaces->toArray();

        $subworkspace_id = request('modulo_id', NULL);
        // $workspaceId = Workspace::getWorkspaceIdFromModule($subworkspace_id);
        $workspaceId = $current_workspace->id;

        $cache_name = "dashboard_cards-{$current_workspace->id}-";
        $cache_name .= $subworkspace_id ? "-modulo-{$subworkspace_id}" : '';


        // Generates totals array

        $data = cache()->remember($cache_name, CACHE_MINUTES_DASHBOARD_DATA,
            function () use ($workspaceId, $subworkspace_id,$current_workspace) {
            $count_active_users = DashboardService::countActiveUsers($subworkspace_id);
            if(!$subworkspace_id){
                $limit_allowed_users = $current_workspace->getLimitAllowedUsers();
                ($limit_allowed_users) && $count_active_users .= '/'.$limit_allowed_users;
            }
            $data['time'] = now();

            $data['totales'] = [

                'temas' => [
                    'title' => 'Temas',
                    'icon' => 'mdi-book-open',
                    'color' => '#FFB300',
                    'value' => DashboardService::countTopics($workspaceId)
                ],

                'cursos' => [
                    'title' => 'Cursos',
                    'icon' => 'mdi-book',
                    'color' => '#E01717',
                    'value' => DashboardService::countCourses($workspaceId)
                ],

                'usuarios' => [
                    'title' => 'Usuarios totales',
                    'icon' => 'mdi-account-group',
                    'color' => '#5458ea',
                    'value' => DashboardService::countUsers($subworkspace_id)
                ],

                'usuarios_activos' => [
                    'title' => 'Usuarios activos',
                    'icon' => 'mdi-account-group',
                    'color' => '#22B573',
                    'value' => $count_active_users
                ],

                'temas_evaluables' => [
                    'title' => 'Temas evaluables',
                    'icon' => 'mdi-text-box-check',
                    'color' => '#4E5D8C',
                    'value' => DashboardService::countAssessableTopics($workspaceId)
                ],
            ];

            return $data;
        });

        return $this->success(compact('data'));
    }

    /**
     * Process request to load "evaluaciones por fecha"
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getEvaluacionesPorfecha(Request $request)
    {
        if ($request->refresh === 'true')
            cache()->flush();

        $module_id = request('modulo_id', NULL);
        // $workspaceId = Workspace::getWorkspaceIdFromModule($module_id);
        $workspace = get_current_workspace();
        $response = DashboardService::loadEvaluacionesByDate($workspace->id,$module_id);

        foreach ($response['data'] as $row) {
            $data['labels'][] = Carbon::parse($row->fechita)
                                      ->format('d/m/Y');
            $data['values'][] = $row->cant;
        }
        $data['response'] = $response['data'];
        $data['last_update']['time'] = $response['time']->format('d/m/Y g:i:s a');
        $data['last_update']['text'] = $response['time']->diffForHumans();

        return $this->success(compact('data'));
    }

    /**
     * Process request to load visits group by date
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function loadVisitsByDate(Request $request)
    {
        // Clear cache

        if ($request->refresh === 'true')
            cache()->flush();

        $module_id = request('modulo_id', NULL);
        // $workspaceId = Workspace::getWorkspaceIdFromModule($module_id);
        $workspace = get_current_workspace();

        $response = DashboardService::loadVisitsByUser($workspace->id,$module_id);

        foreach ($response['data'] as $row) {
            $data['labels'][] = Carbon::parse($row->fechita)->format('d/m/Y');
            $data['values'][] = $row->cant;
        }

        $data['last_update']['time'] = $response['time']->format('d/m/Y g:i:s a');
        $data['last_update']['text'] = $response['time']->diffForHumans();
        return $this->success(compact('data'));
    }

    /**
     * Process request to load total of passed users by "botica"
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function loadTopBoticas(Request $request)
    {
        if ($request->refresh === 'true')
            cache()->flush();

        $module_id = request('modulo_id', NULL);
        // $workspaceId = Workspace::getWorkspaceIdFromModule($module_id);
        $current_workspace = get_current_workspace();

        $response = DashboardService::loadTopBoticas($current_workspace->id);

        $result = $response['data']->toArray();

        $data['values'] = array_column($result, 'total_usuarios');
        $data['labels'] = array_column($result, 'botica');

        $data['last_update']['time'] = $response['time']->format('d/m/Y g:i:s a');
        $data['last_update']['text'] = $response['time']->diffForHumans();

        return $this->success(compact('data'));
    }

    public function getPowerBiView()
    {
        $workspace = get_current_workspace();
        $pbi_url = $workspace->url_powerbi ?? 'Not provided';

        return view('powerbi.index', compact('pbi_url'));
    }

    public function workspaces_status(Request $request) 
    {
        $workspaces_status = DashboardService::loadWorkspacesStatus();

        // === storage segun workspace_id ===
        $workspaces_ids = $workspaces_status->pluck('id');
        $workspaces_storage = DashboardService::loadSizeWorkspaces($workspaces_ids);
        $request->workspaces_storage = $workspaces_storage;

        $workspaces_status->each(function ($workspace, $key) use($workspaces_storage) {

            $workspace->size_medias = (int) $workspaces_storage[$key]->medias_sum_size; // en KB
            $workspace->users_count_actives = $workspace->subworkspaces->sum('users_count_actives');

            return $workspace;
        });

        // === storage y usuarios total ===
        $workspaces_total = [ 
            'workspaces_total_storage' => formatSize($workspaces_status->sum('size_medias')),
            'workspaces_total_users' => $workspaces_status->sum('users_count_actives') 
        ];

        $workspaces_status_total = ResourceListGeneralWorkspacesStatus::collection($workspaces_status);

        return $this->success(compact('workspaces_total', 'workspaces_status_total'));
    }

    public function workspace_current_status(Request $request) 
    {
        $workspace_status = DashboardService::loadCurrentWorkspaceStatus();
        
        return $this->success(new ResourceGeneralWorkspaceStatus($workspace_status));
    }

    public function subworkspace_status(Request $request, string $subworkspace_id = NULL) 
    {
        $subworkspace_status = $subworkspace_id;

        // === usuarios total por subworkspace ===
        if($subworkspace_id) {
            $subworkspace_status = DashboardService::loadSubworkspaceStatus($subworkspace_id);
            $subworkspace_status = new ResourceGeneralSubWorkspaceStatus($subworkspace_status);
        }

        return $this->success($subworkspace_status);
    }

    public function workspace_storage(Request $request) 
    {
        $workspace = get_current_workspace();
        $workspace_current_storage = DashboardService::loadSizeWorkspaces([$workspace->id])->first();
        $workspace_current_storage = (int) $workspace_current_storage->medias_sum_size;

        // === workspace storage actual ===
        $total_current_storage = $workspace_current_storage + round($request->size / 1024);
        $total_current_storage = formatSize($total_current_storage, parsed:false);
        // === workspace storage actual ===

        $total_storage_limit = $workspace->limit_allowed_storage ?? 0;
        $file_storage_check  = ($total_current_storage['size_unit'] == 'Gb' && 
                                $total_storage_limit <= $total_current_storage['size']); 

        $workspace_data = [
            'workspace_storage' => $total_storage_limit.' Gb', // gb
            'workspace_current_storage' => formatSize($workspace_current_storage), // kb
            'file_current_size' =>  formatSize(round($request->size / 1024)), // kb
            'file_storage_check' => $file_storage_check,
            // 'file_storage_check' => true,
        ];

        return $this->success($workspace_data);
    }

    public function workspace_users(Request $request) 
    {
        $workspace_storage = DashboardService::loadCountUsersWorkspaces();
        $users_count_inactives = $workspace_storage->subworkspaces->sum('users_count_actives');

        $total_current_storage = $users_count_inactives + 1;
        $user_storage_check = $workspace_storage->limit_allowed_users['quantity'] < $total_current_storage;

        $workspace_data = [
            'workspace_storage' => $workspace_storage->limit_allowed_users['quantity'], // gb
            'workspace_current_storage' => $total_current_storage, // kb
            'user_storage_check' => $user_storage_check,
        ];

        return $this->success($workspace_data);
    }
    
    public function workspace_plan(GeneralStorageRequest $request) 
    {
        /*paola@cursalab.io|juanjose@cursalab.io|juan@cursalab.io*/

        $workspace = get_current_workspace();
        $user = Auth::user();
        $functionalities_name  = implode(', ',collect($request->functionalities)->pluck('name')->toArray());
        $storage_mail = [ 
                    'subject' => 'Solicitud de Almacenamiento',
                    'user_admin' => $user->getFullnameAttribute(),
                    'user_admin_email' => $user->email_gestor,
                    'workspace_name' => $workspace->name,
                    'storage' => $request->limit_allowed_storage ? $request->limit_allowed_storage.' Gb' : '-',
                    'users' => $request->limit_allowed_users ?? '-',
                    'description' => $request->description ?? '-',
                    'functionalities_name' => $functionalities_name
                ];
        // info(['storage_mail' => $storage_mail]);
        // Mail::to('juan@cursalab.io')->send(new EmailTemplate('emails.enviar_almacenamiento_notificacion', $storage_mail));
        // info(['storage_mail' => $storage_mail]);
        Mail::to(['kevin@cursalab.io','aldo@cursalab.io'])->send(new EmailTemplate('emails.enviar_almacenamiento_notificacion', $storage_mail));
        Mail::to('aldo@cursalab.io')->send(new EmailTemplate('emails.enviar_almacenamiento_notificacion', $storage_mail));

        return $this->success(true);
    }
}
