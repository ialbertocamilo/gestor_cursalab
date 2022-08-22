<?php

namespace App\Http\Controllers;

use App\Models\Criterion;
use App\Models\Prueba;
use App\Models\Workspace;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $workspaceId = Workspace::getWorkspaceIdFromModule($subworkspace_id);

        $cache_name = "dashboard_cards-{$current_workspace->id}-";
        $cache_name .= $subworkspace_id ? "-modulo-{$subworkspace_id}" : '';


        // Generates totals array

        $data = cache()->remember($cache_name, CACHE_MINUTES_DASHBOARD_DATA,
            function () use ($workspaceId, $subworkspace_id) {

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
                    'value' => DashboardService::countActiveUsers($subworkspace_id)
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
        $workspaceId = Workspace::getWorkspaceIdFromModule($module_id);

        $response = DashboardService::loadEvaluacionesByDate($workspaceId);

        foreach ($response['data'] as $row) {
            $data['labels'][] = Carbon::parse($row->fechita)
                                      ->format('d/m/Y');
            $data['values'][] = $row->cant;
        }
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
        $workspaceId = Workspace::getWorkspaceIdFromModule($module_id);
        $response = DashboardService::loadVisitsByUser($workspaceId);

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
        $workspaceId = Workspace::getWorkspaceIdFromModule($module_id);

        $response = DashboardService::loadTopBoticas($workspaceId);

        $result = $response['data']->toArray();

        $data['values'] = array_column($result, 'total_usuarios');
        $data['labels'] = array_column($result, 'botica');

        $data['last_update']['time'] = $response['time']->format('d/m/Y g:i:s a');
        $data['last_update']['text'] = $response['time']->diffForHumans();

        return $this->success(compact('data'));
    }
}
