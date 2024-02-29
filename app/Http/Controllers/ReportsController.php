<?php

namespace App\Http\Controllers;

use App\Models\AssignedRole;
use App\Models\GeneratedReport;
use App\Models\Role;
use App\Models\Taxonomy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

use Altek\Accountant\Facades\Accountant;
use Illuminate\Support\Facades\Auth;

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
        $functionalities = get_current_workspace()->functionalities()->get();
        $show_report_dc3 =  boolval($functionalities->where('code','dc3-dc4')->first());
        $show_report_registro_capacitacion =  boolval($functionalities->where('code','registro-capacitacion')->first());
        $show_report_checklist =  boolval($functionalities->where('code','checklist')->first());
        $show_report_benefit =  boolval($functionalities->where('code','benefits')->first());
        $show_report_reconocimiento=  boolval($functionalities->where('code','benefits')->first());
        $show_report_sessions_live = boolval($functionalities->where('code','sesiones-live')->first());
        
        return $this->success([
            'types'=>Taxonomy::getDataByGroupAndType('reports', 'report'),
            'permissions'=> [
                'show_report_dc3'=>$show_report_dc3,
                'show_report_registro_capacitacion' => $show_report_registro_capacitacion,
                'show_report_checklist' => $show_report_checklist,
                'show_report_benefit' => $show_report_benefit,
                'show_report_reconocimiento' => $show_report_reconocimiento,
                'show_report_sessions_live' => $show_report_sessions_live
            ]
        ]);
    }

    public function saveAudits(Request $request, GeneratedReport $report)
    {
        $report = $report::search($request->id);

        Accountant::record($report, 'downloaded');

        return $this->success($report);
    }
}
