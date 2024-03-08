<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Menu;
use App\Models\Role;
use App\Models\Taxonomy;
use App\Models\AssignedRole;
use Illuminate\Http\Request;
use App\Models\GeneratedReport;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Altek\Accountant\Facades\Accountant;

class ReportsController extends Controller
{
    /**
     * Load workspace's reports queue from the last month
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function loadReportsQueue(Request $request): JsonResponse
    {
        // Checks whether current user is a super user
        Auth::checK();
        $isSuperUser = AssignedRole::hasRole(Auth::user()->id, Role::SUPER_USER);

        // Gets admin workspace

        $workspaceId = session('workspace')->id;

        // Load workspace's reports queue

        $query = GeneratedReport::query()
            ->with('admin.subworkspace')
            ->whereHas('admin')
            ->where('workspace_id', $workspaceId)
            ->where('report_type','<>','api_information')
            ->where('created_at', '>=', Carbon::today()->subDays(30)->toDateTimeString())
            ->orderBy('created_at', 'desc');

        // Adds a condition to exclude superusers

        if (!$isSuperUser) {
            $superusersIds = AssignedRole::getSuperusersIds($workspaceId);
            $query->whereNotIn('admin_id', $superusersIds);
        }

        $reports = $query->get()->toArray();


        // Load report types taxonomies

        $reportsTypes = Taxonomy::getDataByGroupAndType(
            'reports', 'report'
        );

        // Format reports data

        foreach ($reports as &$report) {
            $report['download_url'] = $report['download_url'] ? reportsSignedUrl($report['download_url']) : '';
            $reportType = $reportsTypes->where('code', $report['report_type'])->first();
            $report['report_type'] = $reportType ? $reportType->name : '';
            $report['filters_descriptions'] = json_decode($report['filters_descriptions']) ?: new \stdClass();
            // $report['created_at'] = date('d/m/Y g:i a', strtotime($report['created_at']));
            $report['created_at'] =  Carbon::parse($report['created_at'])->subHours(10)->format('Y-m-d G:i a');
            $report['updated_at'] = $report['updated_at'] ? date('d/m/Y g:i a', strtotime($report['updated_at'])) : '';
        }

        return $this->success($reports);
    }

    /**
     * Load report types from taxonomy
     * @return JsonResponse
     */
    public function loadRepotsTypes(): JsonResponse
    {
        $platform = session('platform') ? session('platform') : 'capacitacion';
        $report_permissions = $this->getPermissionsReportsByPlatform($platform);        
        return $this->success([
            'types'=>Taxonomy::getDataByGroupAndType('reports', 'report'),
            'permissions'=> $report_permissions,
            'platform' => $platform
        ]);
    }

    public function saveAudits(Request $request, GeneratedReport $report)
    {
        $report = $report::search($request->id);

        Accountant::record($report, 'downloaded');

        return $this->success($report);
    }

    public function getPermissionsReportsByPlatform($platform){
        Auth::checK();
        $isSuperUser = AssignedRole::hasRole(Auth::user()->id, Role::SUPER_USER);
        $permissions = [];
        switch ($platform) {
            case 'capacitacion':
                 //Constraints by functionalities
                $functionalities = get_current_workspace()->functionalities()->get();
                $permissions['show_report_dc3'] =  $isSuperUser || boolval($functionalities->where('code','dc3-dc4')->first());
                $permissions['show_report_registro_capacitacion'] =  $isSuperUser || boolval($functionalities->where('code','registro-capacitacion')->first());
                //Constraints by menus
                $menus = Menu::getListSubMenusByUser(auth()->user());
                $permissions['show_report_sessions_live'] = $isSuperUser || boolval($menus->where('code','meetings')->first());
                $permissions['show_report_benefit'] =  $isSuperUser || boolval($menus->where('code','benefits')->first()) ||  boolval($menus->where('code','speaker')->first());
                $permissions['show_report_reconocimiento'] =  $isSuperUser || boolval($menus->where('code','list-campaign')->first()) ||  boolval($menus->where('code','create-campaign')->first());
                $permissions['show_report_checklist'] = $isSuperUser || boolval($menus->where('code','trainer')->first()) ||  boolval($menus->where('code','checklist')->first());
                $permissions['show_report_videoteca'] =  $isSuperUser || boolval($menus->where('code','videoteca')->first());
                $permissions['show_report_vademecun'] =  $isSuperUser || boolval($menus->where('code','vademecun')->first());
                //Constraints by default
                $permissions['show_report_notas_usuario'] = true;
                $permissions['show_report_usuarios'] = true;
                $permissions['show_report_avance_curricula'] = true;
                $permissions['show_report_diploma'] = true;
                $permissions['show_report_visitas'] = true;
                $permissions['show_report_nota_por_tema'] = true;
                $permissions['show_report_tema_no_evaluable'] = true;
                $permissions['show_report_nota_por_curso'] = true;
                $permissions['show_report_segmentacion'] = true;
                $permissions['show_report_evaluaciones_abiertas'] = true;
                $permissions['show_report_reinicios'] = true;
                $permissions['show_report_usuario_uploads'] = true;
                $permissions['show_report_ranking'] = true;
                $permissions['show_report_historial_usuario'] = true; 
                $permissions['show_report_criterios_vacios'] = true;
                $permissions['show_report_multiple_usuarios'] = $isSuperUser; //Only super user
                $permissions['show_report_process_progress'] = false;
                $permissions['show_report_process_detail'] = false;
                break;
            case 'induccion':
                # code...
                $permissions['show_report_dc3'] = false;
                $permissions['show_report_registro_capacitacion'] = false;
                $permissions['show_report_sessions_live'] = false;
                $permissions['show_report_benefit'] = false;
                $permissions['show_report_reconocimiento'] = false;
                $permissions['show_report_checklist'] = false;
                $permissions['show_report_videoteca'] = false;
                $permissions['show_report_vademecun'] = false;
                $permissions['show_report_notas_usuario'] = false;
                $permissions['show_report_avance_curricula'] = false;
                $permissions['show_report_diploma'] = false;
                $permissions['show_report_visitas'] = false;
                $permissions['show_report_nota_por_tema'] = false;
                $permissions['show_report_tema_no_evaluable'] = false;
                $permissions['show_report_nota_por_curso'] = false;
                $permissions['show_report_segmentacion'] = false;
                $permissions['show_report_evaluaciones_abiertas'] = false;
                $permissions['show_report_reinicios'] = false;
                $permissions['show_report_usuario_uploads'] = false;
                $permissions['show_report_ranking'] = false;
                $permissions['show_report_historial_usuario'] = false; 
                $permissions['show_report_criterios_vacios'] = false;
                //REPORTS TO OMBOARDING
                $permissions['show_report_usuarios'] = true;
                $permissions['show_report_process_progress'] = true;
                $permissions['show_report_process_detail'] = true;
                break;
        }
       return $permissions;
    }
}
