<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReporteSupervisor\ReporteSupervisorSearchRequest;
use App\Models\Matricula;
use App\Models\Segment;
use App\Models\SummaryCourse;
use App\Models\Taxonomy;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;

class RestReportesSupervisores extends Controller
{

    /**
     * Load initial data for supersivor reports
     *
     * @param ReporteSupervisorSearchRequest $request
     * @return JsonResponse
     */
    public function init(ReporteSupervisorSearchRequest $request): JsonResponse
    {
        $user = auth('api')->user();
        $supervisorWithSegment = User::loadSupervisorWithSegment($user->id);

        // If supervisor segment does not exist,
        // stop method execution

        if (!$supervisorWithSegment['segment']) return response()->json([]);

        // Get user workspace id

        $subworkspace = Workspace::where('id', $user->subworkspace_id)->first();
        $workspaceId = $subworkspace->parent_id;

        // Generate list of courses statuses

        $_courseStatuses = Taxonomy::where('group', 'course')
                                  ->where('type', 'user-status')
                                  ->get();
        $courseStatuses = new \stdClass();
        foreach ($_courseStatuses as $status) {
            $courseStatuses->{$status->code} = $status->name;
        }

        // Calculates totals

        $totals = SummaryCourse::calculateSupervisorCoursesTotals(
            $user->id, $workspaceId
        );

        $response = [
            'supervisorId' => $supervisorWithSegment['user']->id,
            'workspaceId' => $workspaceId,
            'reportes' => [
                'supervisores_avance_curricula' => 'Reporte de avance de curricula',
                'supervisores_notas' => 'Reporte de notas'
            ],

            'estados' => $courseStatuses,

            'usuarios_activos' => $totals['users'],
            'aprobados' => $totals ? (int)$totals['courses'][0]->aprobados : 0,
            'desaprobados' => $totals ? (int)$totals['courses'][0]->desarrollados : 0,
            'desarrollo' => $totals ? (int)$totals['courses'][0]->desaprobados : 0,
            'encuesta_pendiente' => $totals ? (int)$totals['courses'][0]->encuestaPend : 0
        ];

        return response()->json($response);
    }
}
