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

        $_courseStatuses = Taxonomy::query()
            ->where('group', 'course')
            ->where('type', 'user-status')
            ->get();
        $courseStatuses = new \stdClass();
        foreach ($_courseStatuses as $status) {
            $courseStatuses->{$status->code} = $status->name;
        }

        // Generate lsit of topics

        $_topicStatuses = Taxonomy::query()
            ->where('group', 'topic')
            ->where('type', 'user-status')
            ->get();
        $topicStatuses = new \stdClass();
        foreach ($_topicStatuses as $status) {
            $topicStatuses->{$status->code} = $status->name;
        }

        // Calculates totals

        $totals = SummaryCourse::calculateSupervisorCoursesTotals(
            $user->id, $workspaceId
        );

        if (isset($totals['courses'][0])) {
            $aprobados = (int)$totals['courses'][0]->aprobados;
            $desaprobados = (int)$totals['courses'][0]->desaprobados;
            $desarrollados = (int)$totals['courses'][0]->desarrollados;
            $encuestaPend = (int)$totals['courses'][0]->encuestaPend;
        }

        $response = [
            'supervisorId' => $supervisorWithSegment['user']->id,
            'workspaceId' => $workspaceId,
            'reportes' => [
                'supervisores_notas' => 'Reporte de notas por curso',
                'supervisores_notas_temas' => 'Reporte de notas por tema',
                'supervisores_avance_curricula' => 'Reporte de avance de currícula'
            ],

            'estados' => $courseStatuses,
            'estados_temas' => $topicStatuses,

            'usuarios_activos' => $totals['users'] ?? 0,
            'aprobados' => $aprobados ?? 0,
            'desaprobados' => $desaprobados ?? 0,
            'desarrollo' => $desarrollados ?? 0,
            'encuesta_pendiente' => $encuestaPend ?? 0
        ];

        return response()->json($response);
    }
}
