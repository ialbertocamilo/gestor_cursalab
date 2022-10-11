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
        $supervisorWithSegment = $this->loadSupervisorWithSegment();

        // Get user workspace id

        $user = auth('api')->user();
        $subworkspace = Workspace::where('id', $user->subworkspace_id)->first();
        $workspaceId = $subworkspace->parent_id;

        // If supervisor segment does not exist,
        // stop method execution

        if (!$supervisorWithSegment['segment']) return response()->json([]);

        // Generate list of courses statuses

        $_courseStatuses = Taxonomy::where('group', 'course')
                                  ->where('type', 'user-status')
                                  ->get();
        $courseStatuses = new \stdClass();
        foreach ($_courseStatuses as $status) {
            $courseStatuses->{$status->code} = $status->name;
        }

        // Calculates totals

        $coursesTotals = SummaryCourse::calculateWorkspaceCoursesTotals($workspaceId);

        $response = [
            'supervisorId' => $supervisorWithSegment['user']->id,
            'workspaceId' => $workspaceId,
            'reportes' => [
                'supervisores_avance_curricula' => 'Reporte de avance de curricula',
                'supervisores_notas' => 'Reporte de notas'
            ],

            'estados' => $courseStatuses,

            'usuarios_activos' => User::countActiveUsersInWorkspace($workspaceId),
            'aprobados' => (int)$coursesTotals[0]->aprobados,
            'desaprobados' => (int)$coursesTotals[0]->desarrollados,
            'desarrollo' => (int)$coursesTotals[0]->desaprobados,
            'encuesta_pendiente' => (int)$coursesTotals[0]->encuestaPend
        ];

        return response()->json($response);
    }

    /**
     * Load supervisor user and its segment
     * @return array
     */
    private function loadSupervisorWithSegment(): array
    {
//        $userId = 16;
//        $user = User::find($userId);

        $user = auth('api')->user();

        $supervisorTaxonomy = Taxonomy::where('type', 'code')
            ->where('group', 'segment')
            ->where('code', 'user-supervise')
            ->where('active', 1)
            ->first();

        $supervisorSegment = Segment::where('model_type', 'App\Models\User')
            ->where('model_id', $user->id)
            ->where('code_id', $supervisorTaxonomy->id)
            ->where('active', 1)
            ->first();

        return [
            'user' => $user,
            'segment' => $supervisorSegment
        ];
    }
}
