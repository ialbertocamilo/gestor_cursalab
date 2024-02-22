<?php

namespace App\Http\Controllers;

use App\Models\Criterion;
use App\Models\Prueba;
use App\Models\Visita;
use App\Models\Workspace;
use App\Services\DashboardService;
use Carbon\Carbon;
use DB;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Process request to render dashboard view
     *
     * @param Request $request
     * @return Application|Factory|View
     */
    public function index(Request $request)
    {

        if ($request->refresh == 'true')
            cache()->flush();

        $current_workspace = get_current_workspace();
        // $modulos = $current_workspace?->subworkspaces->toArray();
        $modulos = get_current_subworkspaces();
        if(!$modulos){
            return view('home', []);
        }
        $subworkspace_id = request('modulo_id', NULL);
        // $workspaceId = Workspace::getWorkspaceIdFromModule($subworkspace_id);
        $workspaceId = $current_workspace->id;

        $cache_name = "dashboard_cards-{$current_workspace->id}-";
        $cache_name .= $subworkspace_id ? "-modulo-{$subworkspace_id}" : '';

        $data = cache()->remember($cache_name, CACHE_SECONDS_DASHBOARD_DATA,
            function () use ($workspaceId, $subworkspace_id, $modulos,$current_workspace) {
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
                    'value' => DashboardService::countCourses($workspaceId),
                    'path' => '/cursos'
                ],

                'usuarios' => [
                    'title' => 'Usuarios totales',
                    'icon' => 'mdi-account-group',
                    'color' => '#5458ea',
                    'value' => DashboardService::countUsers($subworkspace_id),
                    'path' => '/usuarios'
                ],

                'usuarios_activos' => [
                    'title' => 'Usuarios activos',
                    'icon' => 'mdi-account-group',
                    'color' => '#22B573',
                    'value' => $count_active_users,
                    'path' => '/usuarios'
                ],

                'temas_evaluables' => [
                    'title' => 'Temas evaluables',
                    'icon' => 'mdi-text-box-check',
                    'color' => '#4E5D8C',
                    'value' => DashboardService::countAssessableTopics($workspaceId)
                ],
                ];

                $data['data'] = [
                    // 'modulos' => Criterion::getValuesForSelect('module'),
                    'modulos' => $modulos,
                    'categorias' => []//Categoria::select('id', 'nombre')->pluck('nombre', 'id'),
                ];

                return $data;
            });

        $data['last_update']['time'] = $data['time']->format('d/m/Y g:i:s a');
        $data['last_update']['text'] = $data['time']->diffForHumans();

        // $cate_populares = [];
        // $cate_avance = [];
        // $top_cursos = [];

        // $pruebas_exis = Prueba::count();
        // $pruebas_proyectado = $tot_usuarios * $cur_eval;

        // $cur_rea = 0;

        // if ($pruebas_proyectado > 0)
        //     $cur_rea = ($pruebas_exis / $pruebas_proyectado) * 100;

        // $cur_rea = number_format($cur_rea, 2, '.', ',');

        // $pruebas_ok = Prueba::where("resultado",'1')->count();
        // $pruebas_ok_proyectado = $tot_usuarios * $cur_eval;

        // $cur_apro = 0;

        // if ($pruebas_proyectado > 0)
        //     $cur_apro = ($pruebas_ok / $pruebas_ok_proyectado) * 100;

        // $cur_apro = number_format($cur_apro, 2, '.', ',');

        return view('home', compact('data'));
    }

    public function clearCache()
    {
        cache()->flush();

        return [];
    }

    public function getDataForGraphicTopBoticas()
    {
        $modulo_id = request('modulo_id', NULL);

        $response = Prueba::getTopBoticas($modulo_id);

        $result = $response['data']->toArray();

        $data['values'] = array_column($result, 'total_usuarios');
        $data['labels'] = array_column($result, 'botica');

        $data['last_update']['time'] = $response['time']->format('d/m/Y g:i:s a');
        $data['last_update']['text'] = $response['time']->diffForHumans();

        return $data;
    }

    public function getDataForGraphicVisitasPorfecha()
    {
        $modulo_id = request('modulo_id', NULL);
        // $workspaceId = Workspace::getWorkspaceIdFromModule($module_id);
        $current_workspace = get_current_workspace();

        $response = DashboardService::loadVisitsByUser($current_workspace->id,$module_id);
        foreach ($response['data'] as $row)
        {
            $data['labels'][] = Carbon::parse($row->fechita)->format('d/m/Y');
            $data['values'][] = $row->cant;
        }

        $data['last_update']['time'] = $response['time']->format('d/m/Y g:i:s a');
        $data['last_update']['text'] = $response['time']->diffForHumans();

        return $data;
    }

    public function getDataForGraphicEvaluacionesPorfecha()
    {
        $module_id = request('modulo_id', NULL);
        // $workspaceId = Workspace::getWorkspaceIdFromModule($module_id);
        $current_workspace = get_current_workspace();

        $response = DashboardService::loadEvaluacionesByDate($current_workspace->id,$module_id);
        foreach ($response['data'] as $row)
        {
            $data['labels'][] = Carbon::parse($row->fechita)->format('d/m/Y');
            $data['values'][] = $row->cant;
        }

        $data['last_update']['time'] = $response['time']->format('d/m/Y g:i:s a');
        $data['last_update']['text'] = $response['time']->diffForHumans();

        return $data;
    }
}
