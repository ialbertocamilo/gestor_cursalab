<?php

namespace App\Http\Controllers;

use App\Http\Resources\CampaignPostulationSearchResource;
use App\Models\Campaign;
use App\Models\CampaignPostulates;
use App\Models\CampaignSummoneds;
use App\Models\Criterion;
use App\Models\User;
use App\Mail\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;


class CampaignPostulationController extends Controller
{
    public function search(Campaign $campaign, Request $request) 
    {
        $request->campaign_id = $campaign->id;

        $requirement_voter = $campaign->requirement_not_null_criterio('VOTERS')->first();
        $request->criterio_id = $requirement_voter ? $requirement_voter->criterio_id : NULL;

        $summoneds = CampaignSummoneds::search($request);
        CampaignPostulationSearchResource::collection($summoneds);

        return $this->success($summoneds);
    }

    public function search_sustents(Campaign $campaign, Request $request) 
    {
        $request->campaign_id = $campaign->id;

        $summoneds_sustents = CampaignPostulates::search($request);

        return $this->success($summoneds_sustents);
    }
    
    public function status(Request $request) 
    {
        $current = $request->all();
        return [__function__, __class__, $current];
    }
    
    public function requirements(Campaign $campaign, Request $request) 
    {
        $requirement = $campaign->requirement_not_null_criterio($request->stage)->first();

        $criterio = Criterion::select('id','name')->where('id', $requirement->criterio_id)->first();
        $criterio_values = $criterio->values()->select('id', 'value_text as name');

        if($requirement->value) {
            $criterio_values->whereIn('id', explode(',', $requirement->value));
        }
        $criterio_values = $criterio_values->get();

        return $this->success(compact('criterio', 'criterio_values'));
    }

    public function update_sustents(Campaign $campaign, Request $request) {
 
        $request->summoned_id = $request->id;
        $array_sustents = $request->currSustents;

        if(count($array_sustents) > 0) {
            CampaignPostulates::storeStateSustents($request);
        }

        CampaignSummoneds::saveStateCandidateSustent($request->summoned_id, $request->candidate_state, 1);

        return $this->success(true);
    }

    public function update_sub_sustents(Request $request) {  
        
        $request->summoned_id = $request->id;
        $request->candidate_state = $request->candidate_state ?? NULL;

        CampaignSummoneds::saveStateCandidateSustent($request->summoned_id, $request->candidate_state, 1);

        CampaignPostulates::where('summoned_id', $request->summoned_id)
                          ->update(['state' => 1]);

        return $this->success(true);
    }

    public function count_checks(Request $request) 
    {
        $summoneds_checks = CampaignPostulates::count_checks($request);

        return $this->success($summoneds_checks);
    }

    public function reset_sustents(Request $request) 
    {
        $request->summoned_id = $request->id;

        CampaignPostulates::reset_sustents($request->summoned_id);
        CampaignSummoneds::saveStateCandidateSustent($request->summoned_id, 0, 0);

        return $this->success(true);
    }

    public function send_email(Campaign $campaign, Request $request) {

        $summoned = CampaignSummoneds::find($request->summoned_id);
        $summoned_mail = [ 
            'title' => $campaign->title,
            'subject' => $campaign->subject,
            'body' => $campaign->body,
            'user' => $summoned->user->getFullnameAttribute() 
        ];

        // enviar email
        // $summoned_email = $summoned->user->email;
        $summoned_email = 'juan@cursalab.io';
        // $summoned_email = NULL;

        if($summoned_email) {
            Mail::to($summoned_email)
                ->send(new EmailTemplate('votaciones.enviar_notificacion', $summoned_mail));
            return $this->success(true);
        }else {
            return $this->success(false);
        }
    }
    
}