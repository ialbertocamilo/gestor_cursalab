<?php

namespace App\Http\Controllers;

use App\Http\Resources\{ CampaignVotationSearchResource, CampaignVotationSearchStatusResource };
use App\Models\Campaign;
use App\Models\CampaignRequirements;
use App\Models\CampaignSummoneds;
use App\Models\Criterion;
use App\Models\Workspace;
use Illuminate\Http\Request;

class CampaignVotationController extends Controller
{
    public function search(Campaign $campaign, Request $request) 
    {
        $request->campaign_id = $campaign->id;

        $requirement_voter = $campaign->requirement_not_null_criterio('VOTERS')->first();
        $request->criterio_id = $requirement_voter ? $requirement_voter->criterio_id : NULL;

        $summoneds_votes = CampaignSummoneds::search_votations_votes($request);
        CampaignVotationSearchResource::collection($summoneds_votes);

        return $this->success($summoneds_votes);
    }
    
    public function search_status(Campaign $campaign, Request $request) 
    {
        $requirement_voter = $campaign->requirement_not_null_criterio('VOTERS')->first();

        $criterio = Criterion::select('id','name')->where('id', $requirement_voter->criterio_id)->first();
        $criterio_values = $criterio->values()->select('id', 'value_text as name');

        if($request->q) {
            $criterio_values->where('value_text', 'like', "%$request->q%");
        }

        if($requirement_voter->value) {
            $criterio_values->whereIn('id', explode(',', $requirement_voter->value));
        }

        $result_criterio_values = $criterio_values->paginate(10);

        // === criterio_fecha_reconocimiento ===
        $workspace = get_current_workspace();
        $criterio_id = Workspace::select('criterio_id_fecha_inicio_reconocimiento')
                                ->where('id', $workspace->id)->first();
        // === criterio_fecha_reconocimiento ===

        // === condicion y meses ===
        $requirement_voter_date = $campaign->requirement_not_null_condition_value('VOTERS')->first();

        $requirements = CampaignRequirements::getReturnDataRequirements([
                             $requirement_voter_date->condition,
                             $requirement_voter_date->value,
                             NULL,
                             'voters'
                             ]);
        // === condicion y meses ===

        $request->campaign_id = (int) $campaign->id;
        $request->criterio_id = (int) $criterio->id;
        $request->porcent = (int) $campaign->porcent;
        $request->criterio_id_fecha_inicio_reconocimiento = (int) $criterio_id->criterio_id_fecha_inicio_reconocimiento;
        $request->workspace_id = (int) $workspace->id;
        $request->sub_workspaces = $campaign->modules->pluck('module_id')->toArray();
        $request->data = $requirements['data'];

        CampaignVotationSearchStatusResource::collection($result_criterio_values);

        return $this->success($result_criterio_values);
    }
}
