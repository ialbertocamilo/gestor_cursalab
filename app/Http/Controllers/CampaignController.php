<?php

namespace App\Http\Controllers;

use App\Exports\ExportVotacionesCandidatos;
use App\Exports\ExportVotacionesPostulantes;
use App\Http\Resources\{ CriterionResource, CriterionValueResource, CampaignSearchResource };
use App\Models\Campaign;
use App\Models\CampaignBadges;
use App\Models\CampaignContents;
use App\Models\CampaignRequirements;
use App\Models\CampaignSummoneds;
use App\Models\Criterion;
use App\Models\CriterionValue;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class CampaignController extends Controller
{
	public function search(Request $request)
	{
        $campaigns = Campaign::search($request);
        CampaignSearchResource::collection($campaigns);

        return $this->success($campaigns);
	}

    public function get_campaign(Campaign $campaign) {
        return $this->success($campaign);
    }

	public function store(Request $request)
	{
        $stage_values = $request->currStages['value'];
        $lastIndex = 0;

        $currBadgeOne = $request->currGeneralConfig['file_badge_one'] ?? NULL;
        $currBadgeTwo = $request->currGeneralConfig['file_badge_two'] ?? NULL;

        // === GENERAL CONFIG ===
        if($request->currGeneralConfig) {
            $lastIndex = Campaign::saveConfigGeneral(
                $request->currStages,$request->currGeneralConfig);
        }

        // === CONTENTS ===
        if(in_array(0, $stage_values) && $request->currContents) {
            ['contents' => $contents] = $request->currContents;
            CampaignContents::saveContents($contents, $lastIndex);

            // OPTIONAL: QUESTION - PREGUNTA DE ENCUESTA
            $question = $request->currContents['question'] ?? NULL;
            if($question) Campaign::saveQuestion($question, $lastIndex);

            // OPTIONAL: INSIGNIA 01 - RESPUESTA DE ENCUESTA
            if($currBadgeOne) CampaignBadges::saveBadge($currBadgeOne, 0, $lastIndex);
        }

        // === POSTULATES ===
        if(in_array(1, $stage_values) && $request->currPostulates) {

        	CampaignRequirements::saveRequirement('POSTULATES', $request->currPostulates, $lastIndex);

            // OPTIONAL: CHECK SUSTENTS
            $currSustentState = $request->currPostulates['state'] ?? NULL;
            if($currSustentState) Campaign::saveSupportSustent($currSustentState, $lastIndex);
        }

        // === VOTERS ===
        if(in_array(2, $stage_values) && $request->currVoters) {

        	CampaignRequirements::saveRequirement('VOTERS', $request->currVoters, $lastIndex);
        	CampaignRequirements::saveRequirementCriterio('VOTERS', $request->currVoters, $lastIndex);

        	// porcentaje de validez
	        ['porcent' => $porcent] = $request->currVoters;
    	    Campaign::savePorcent($porcent, $lastIndex);

            // OPTIONAL: INSIGNIA 02 - RESPUESTA DE ENCUESTA
            if($currBadgeTwo) CampaignBadges::saveBadge($currBadgeTwo, 1, $lastIndex);
        }

		return $this->success($lastIndex);
    }

    public function duplicate(Campaign $campaign)
    {
    	// para copiar tambien los requiemientos
    	$with_requirements = false;

    	$new_campaign = $campaign->replicate(); // replicamos
    	$new_campaign->title = $campaign->title.' - copia';
    	$new_campaign->state_postulate_support = false;

    	// replicamos las etapas - modalidad: como si recien se hubiesen creado
		switch ($campaign->stage_id) {
            case 0:
                $new_campaign->stage_content = 1;
                $new_campaign->stage_postulate = 0;
                $new_campaign->stage_votation = 0;
            break;

            case 1:
                $new_campaign->stage_content = 1;
                $new_campaign->stage_postulate = 0;
            break;

            case 2:
                $new_campaign->stage_postulate = 1;
                $new_campaign->stage_votation = 0;
            break;

            default:
                $new_campaign->stage_postulate = 1;
            break;
        }

        $new_campaign->porcent = NULL;
        $new_campaign->state = false;
        $new_campaign->created_at = Carbon::now();
        $new_campaign->updated_at = Carbon::now();
        $new_campaign->save();

        $new_campaign_id = $new_campaign->id;

        // replicate modules
        foreach ($campaign->modules as $module) {
            $new_module = $module->replicate();

            $new_module->campaign_id = $new_campaign_id;
            $new_module->created_at = Carbon::now();
            $new_module->updated_at = Carbon::now();
            $new_module->save();
        }

        // replicate badges
        foreach ($campaign->badges as $badge) {
            $new_badge = $badge->replicate();

            $new_badge->campaign_id = $new_campaign_id;
            $new_badge->created_at = Carbon::now();
            $new_badge->updated_at = Carbon::now();
            $new_badge->save();
        }

        // replicate contents
        foreach ($campaign->contents as $content) {
            $new_content = $content->replicate();

            $new_content->campaign_id = $new_campaign_id;
            $new_content->created_at = Carbon::now();
            $new_content->updated_at = Carbon::now();
            $new_content->save();
        }

	    // replicate requirements
        foreach ($campaign->requirements as $requirement) {
            $new_requirement = $requirement->replicate();
            $new_requirement->campaign_id = $new_campaign_id;
            if(!$with_requirements) {
                $new_requirement->criterio_id = NULL;
                $new_requirement->condition = NULL;
                $new_requirement->value = NULL;
            }

            $new_requirement->created_at = Carbon::now();
            $new_requirement->updated_at = Carbon::now();
            $new_requirement->save();
        }

    	return $this->success(['msg' => 'Campaña duplicada correctamente.']);
    }

	public function update(Campaign $campaign, Request $request)
	{
        $stage_values = $request->currStages['value'];
        $lastIndex = 0;

        $currBadgeOne = $request->currGeneralConfig['file_badge_one'] ?? NULL;
        $currBadgeTwo = $request->currGeneralConfig['file_badge_two'] ?? NULL;

         // === GENERAL CONFIG ===
        if($request->currGeneralConfig) {
            $lastIndex = Campaign::saveConfigGeneral(
                $request->currStages, $request->currGeneralConfig, $campaign->id, false
            );
        }

        // === CONTENTS ===
        if(in_array(0, $stage_values) && $request->currContents) {
            ['contents' => $contents] = $request->currContents;
            CampaignContents::saveContents($contents, $lastIndex);

            // OPTIONAL: QUESTION - PREGUNTA DE ENCUESTA
            $question = $request->currContents['question'] ?? NULL;
            // info(['$question'=> $question]);
            Campaign::saveQuestion($question, $lastIndex);

            // OPTIONAL: INSIGNIA 01 - RESPUESTA DE ENCUESTA
            if($currBadgeOne) CampaignBadges::updateBadge($currBadgeOne, 0, $lastIndex);
            else CampaignBadges::updateBadge($currBadgeOne, 0, $lastIndex, true); // deleted
        }

        // === POSTULATES ===
        if(in_array(1, $stage_values) && $request->currPostulates) {

            CampaignRequirements::saveRequirement('POSTULATES', $request->currPostulates, $lastIndex);

            // OPTIONAL: CHECK SUSTENTS
            $currSustentState = $request->currPostulates['state'] ?? NULL;
            Campaign::saveSupportSustent($currSustentState, $lastIndex);
        }

        // === VOTERS ===
        if(in_array(2, $stage_values) && $request->currVoters) {

            CampaignRequirements::saveRequirement('VOTERS', $request->currVoters, $lastIndex);
            CampaignRequirements::saveRequirementCriterio('VOTERS', $request->currVoters, $lastIndex);

            // porcentaje de validez
            ['porcent' => $porcent] = $request->currVoters;
            Campaign::savePorcent($porcent, $lastIndex);

            // OPTIONAL: INSIGNIA 02 - RESPUESTA DE ENCUESTA
            if($currBadgeTwo) CampaignBadges::updateBadge($currBadgeTwo, 1, $lastIndex);
            else CampaignBadges::updateBadge($currBadgeTwo, 1, $lastIndex, true);
        }

        return $this->success($lastIndex);
	}

	public function edit_campaign(Campaign $campaign, Request $request)
	{
        $CAMPAIGN_DATA['currGeneralConfig'] = $campaign;
        $CAMPAIGN_DATA['currGeneralConfig'] = $campaign->processBadgesCampaigId(['one','two'], $CAMPAIGN_DATA['currGeneralConfig']);
        $CAMPAIGN_DATA['currGeneralConfig']['modules'] = $campaign->modules()->select('module_id as id')->get();

        $stage_values = $campaign->campaignGetStages();
        $CAMPAIGN_DATA_STAGES = [];

        // === CONTENTS ===
        if(in_array(0, $stage_values)) {
            $CAMPAIGN_DATA_STAGES['currContents'] = $campaign->getCurrContents();
        }

        // === POSTULATES ===
        if(in_array(1, $stage_values)) {
            $CAMPAIGN_DATA_STAGES['currPostulates'] = $campaign->getCurrPostulates();
        }

        // === VOTERS ===
        if(in_array(2, $stage_values)) {
            $CAMPAIGN_DATA_STAGES['currVoters'] = $campaign->getCurrVoters();
        }

        $CAMPAIGN_DATA['currGeneralConfig'] = Arr::except($CAMPAIGN_DATA['currGeneralConfig'], ['badges','stage_postulate', 'stage_content', 'stage_votation', 'state', 'deleted_at','created_at','updated_at','workspace_id', 'question', 'porcent']);
        $CAMPAIGN_DATA_RESULT = array_merge($CAMPAIGN_DATA, $CAMPAIGN_DATA_STAGES);

        return $this->success($CAMPAIGN_DATA_RESULT);
	}

	public function getFilterCriterion(Request $request)
	{
		$workspace = get_current_workspace();

		$criteria = Criterion::getListForSelectWorskpace($workspace?->id);

		return $this->success($criteria);
	}

	public function getFilterCriterionValues(Criterion $criterion)
	{
		$workspace = get_current_workspace();

        $criteria = CriterionValue::getListForSelectValues($criterion->id, $workspace->id);

        CriterionValueResource::collection($criteria);

        return $this->success($criteria);
	}

	public function getFiltersSelects()
	{
		$workspace = get_current_workspace();

		$modules = Workspace::where('parent_id', $workspace->id)
			// ->select('criterion_value_id as id', 'name')
			->select('id', 'name', 'codigo_matricula')
			->get()
			->map(function ($module, $key) {
				$module->name = $module->name . " [{$module->codigo_matricula}]";
				return $module;
			});

		return $this->success(compact('modules'));
	}

	public function getVerifyRequirements(Request $request)
	{
     	$config = [ $request->condition, $request->months, $request->index, $request->section ];
        $requirements = CampaignRequirements::getReturnDataRequirements($config);

        return CampaignRequirements::checkVerifyCount($requirements, $request);
	}

    public function check_duplicate(Campaign $campaign)
    {
        if(in_array($campaign->stage_id, [0, 2])) {
            // modalidad : 1 - 3
            $state_duplicate = $campaign->checkFillPostulates() && $campaign->checkFillVoters();
        } else {
            // modalidad : 2 - 4
            $state_duplicate = $campaign->checkFillPostulates();
        }

        return $this->success($state_duplicate);
    }

    public function update_stages(Campaign $campaign, Request $request)
    {
        $campaign->update($request->all());
    	return $this->success(['msg' => 'Etapas actualizadas correctamente.']);
    }

	public function status(Campaign $campaign)
	{
		$campaign->update(['state' => !$campaign->state ]);
        return $this->success(['msg' => 'Estado actualizado correctamente.']);
	}

	public function destroy(Campaign $campaign)
	{
		$campaign->delete();
    	return $this->success(['msg' => 'Campaña eliminada correctamente.']);
	}

    // === reportes ===
    public function report_candidates(Campaign $campaign, Request $request)
    {
        $requirement_voter = $campaign->requirement_not_null_criterio('VOTERS')->first();

        $request->campaign_id = $campaign->id;
        $request->criterio_id = $requirement_voter->criterio_id;

        $candidates = CampaignSummoneds::candidates_votes($request);

        ob_end_clean();
        ob_start();

        return Excel::download(new ExportVotacionesCandidatos($candidates, $campaign), 'reporte_candidatos_'.date('Y-m-d H:i:s').'.xlsx');
    }

    public function report_postulates(Campaign $campaign, Request $request)
    {
        $summoneds = $campaign->summoneds()
                              ->select('id','user_id','in_date', 'answer', 'candidate_state')
                              ->with(['user:id,name,lastname,document,surname',
                                      'postulates' => [
                                        'user:id,name,lastname,document,surname'
                                       ]
                              ])->get();

        ob_end_clean();
        ob_start();

        return Excel::download(new ExportVotacionesPostulantes($summoneds, $campaign), 'reporte_postulantes_'.date('Y-m-d H:i:s').'.xlsx');
    }
    // === reportes ===

}
