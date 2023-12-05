<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignContents;
use App\Models\CampaignPostulates;
use App\Models\CampaignSummoneds;
use App\Models\CampaignVotations;
use App\Models\Criterion;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CampaignVerifyContentUser;

class RestCampaignController extends Controller
{
    public function campaigns(User $user)
    {
        $subworkspace = $user->subworkspace;
        $criterio = Workspace::select('criterio_id_fecha_inicio_reconocimiento')->where('id', $subworkspace->parent_id)->first();

        $user_campaigns_ids = Campaign::getCampaignsByUser($user, $criterio);
        $campaings = Campaign::where('state', 1)->whereIn('id', $user_campaigns_ids)->get();

        $campaings_data = $campaings->map(function ($campaign) use($user) {

            $campaign->file_image = $campaign->file_image ? get_media_url($campaign->file_image) : $campaign->file_image;
            $campaign->file_banner = $campaign->file_banner ? get_media_url($campaign->file_banner) : $campaign->file_banner;
            $campaign->coming_soon = $campaign->processUserCommingSoon();

            $campaign->user_states = $campaign->processUserStateStage($user->id); // etapass usuario
            $campaign->user_badges = $campaign->processUserBadgeStage(); // insignias usuario

            return $campaign;
        });

        return $this->success($campaings_data);
    }

    public function campaingsContents(Campaign $campaign)
    {
        $campaings_contents = $campaign->contents()->select('id','title', 'description', 'file_media', 'linked', 'state')->get();

        $campaings_contents_data = $campaings_contents->map(function ($content) {

            $content->file_media = $content->file_media ? get_media_url($content->file_media) : $content->file_media;
            $content->type = ($content->linked) ? get_type_link2($content->linked) : get_type_media($content->file_media);

            return $content;
        });

        return $this->success($campaings_contents_data);
    }
    public function campaingsContentsV2(Campaign $campaign){
        $user_id = auth()->user()->id;
        $campaings_contents = $campaign->contents()->select('id','title', 'description', 'file_media', 'linked', 'state')->get();
        $contents_validated = CampaignVerifyContentUser::where('campaign_id',$campaign->id)
                                ->where('user_id',$user_id)->whereIn('content_id',$campaings_contents->pluck('id'))->get();

        $campaings_contents_data = $campaings_contents->map(function ($content) use($contents_validated){

            $content->file_media = $content->file_media ? get_media_url($content->file_media) : $content->file_media;
            $content->type = ($content->linked) ? get_type_link2($content->linked) : get_type_media($content->file_media);
            $content['reviewved'] = boolval($contents_validated->where('content_id',$content['id'])->first());
            return $content;
        });
        $allow_continue = false;
        if(count($campaings_contents_data)>0){
            $allow_continue = !boolval($campaings_contents_data->where('reviewved',false)->first());
        }
        return $this->success(['contents'=>$campaings_contents_data,'allow_continue'=>$allow_continue]);
    }
    public function checkContent(Request $request){
        $campaign_id = $request->campaign_id;
        $media_id = $request->media_id;
        $user_id = auth()->user()->id;
        CampaignVerifyContentUser::updateOrCreate(
            ['campaign_id'=>$campaign_id,'user_id'=>$user_id,'content_id'=>$media_id],
            ['campaign_id'=>$campaign_id,'user_id'=>$user_id,'content_id'=>$media_id,'completed'=>true]
        );
        return response()->json(['message'=>'contenido validado']);
    }
    public function campaignUserRequirements(Campaign $campaign, User $user)
    {
        $requirement_postulates = $campaign->requirement_null_criterio('POSTULATES')->first();
        $requirement_voters = $campaign->requirement_not_null_criterio('VOTERS')->first();

        $user_criterion_value = NULL;

        if($requirement_voters) {
            $user_criterion_value = $user->criterion_values()->select('id', 'value_text as criterio', 'criterion_id')
                                         ->where('criterion_id', $requirement_voters->criterio_id)
                                         ->first();

            if($user_criterion_value) {
                $user_criterion_value->makeHidden('pivot');
                $user_criterion_value->tipo_criterio = Criterion::find($requirement_voters->criterio_id)->name;
            }
        }

        $user_criterion_response = [
            'user_criterio' => $user_criterion_value,
            'state' => (bool) $user_criterion_value
        ];

        return $this->success($user_criterion_response);
    }

    public function campaignUserBadges(Campaign $campaign, User $user)
    {
        $user = auth()->user();

        $user_campaign = CampaignSummoneds::getSummonedAnswerCandidate($campaign->id, $user->id);

        if($user_campaign) {

            $user_campaign_response = [
                'user_id' => (int) $user->id,
                'campaign_id' => (int) $campaign->id,
                'first_badge_state' => !(is_null($user_campaign->answer)),
                'second_badge_state' => (bool) $user_campaign->candidate_state
            ];

            return $this->success($user_campaign_response);
        }

        return $this->error();
    }

    public function contentSaveAnswer(Request $request)
    {
        $summoned = CampaignSummoneds::select('id', 'campaign_id', 'user_id', 'in_date','answer')
                                     ->where('campaign_id', $request->campaign_id)
                                     ->where('user_id', $request->user_id)
                                     ->first();
        if($summoned) {
            return $this->success(true);
        } else {
            $summoned_data = $request->all();
            $summoned_data['in_date'] = date('Y-m-d H:i:s');
            $summoned_data['answer'] = $request->answer ?? NULL;

            CampaignSummoneds::create($summoned_data);
            return $this->success(true);
        }
    }

    // ===  APIS VALIDACION ===
    public function campaignUserCheckAnswer(Request $request)
    {
        $summoned = Campaign::checkAnswer($request);
        return $this->success((bool) $summoned);
    }

    public function campaignUserCheckPostulates(Request $request)
    {
        $summoned_postulate = Campaign::checkPostulates($request);
        return $this->success((bool) $summoned_postulate);
    }

    public function campaignUserCheckVotations(Request $request)
    {
        $summoned_votation = Campaign::checkVotations($request);
        return $this->success((bool) $summoned_votation);
    }
    // ===  APIS VALIDACION ===

    // ===  APIS POSTULACION ===
    public function postulates(Request $request)
    {
        $request->user_id = auth()->user()->id;
        $summoneds = CampaignSummoneds::search_api($request);
        $summoneds_rows = $summoneds->items();

        foreach ($summoneds_rows as $key => $item) {

            $summoneds_rows[$key]['id'] = $item->id;
            $summoneds_rows[$key]['user_id'] = $item->user->id;
            $summoneds_rows[$key]['campaign_id'] = $item->campaign_id;
            $summoneds_rows[$key]['state_send'] = $item->state_send;
            $summoneds_rows[$key]['name'] = $item->user->name;
            $summoneds_rows[$key]['lastname'] = $item->user->lastname;
            $summoneds_rows[$key]['surname'] = $item->user->surname;
            $summoneds_rows[$key]['document'] = $item->user->document;

            unset($summoneds_rows[$key]['user']);
        }

        return $this->success($summoneds);
    }

    public function postulatesUserSustent(Request $request) {

        $postulate = CampaignPostulates::where('summoned_id', $request->summoned_id)
                                       ->where('user_id', $request->user_id)
                                       ->first();
        if($postulate) {
            $postulate->update($request->all());
        } else {
            $postulate = CampaignPostulates::create($request->all());

            $summoned = CampaignSummoneds::find($request->summoned_id);
            $summoned->state_sustent = 0;
            $summoned->save();
        }

        return $this->success(true);
    }
    // ===  APIS POSTULACION ===

    // ===  APIS VOTACION ===
    public function votationCandidates(Request $request) {

        $candidates = CampaignSummoneds::search_candidates_api($request);
        $candidates_rows = $candidates->items();

        foreach ($candidates_rows as $key => $item) {

            $candidates_rows[$key]['id'] = $item->id;
            $candidates_rows[$key]['user_id'] = $item->user->id;
            $candidates_rows[$key]['campaign_id'] = $item->campaign_id;

            $candidates_rows[$key]['name'] = $item->user->name;
            $candidates_rows[$key]['lastname'] = $item->user->lastname;
            $candidates_rows[$key]['surname'] = $item->user->surname;
            $candidates_rows[$key]['document'] = $item->user->document;

            unset($candidates_rows[$key]['user']);
        }

        return $this->success($candidates);
    }

    public function votationUserVotes(Request $request) {
        $votations = CampaignSummoneds::search_votations_api($request);
        $votations_rows = $votations->items();

        foreach ($votations_rows as $key => $item) {

            $votations_rows[$key]['id'] = $item->id;
            $votations_rows[$key]['user_id'] = $item->user->id;
            $votations_rows[$key]['campaign_id'] = $item->campaign_id;

            $votations_rows[$key]['name'] = $item->user->name;
            $votations_rows[$key]['lastname'] = $item->user->lastname;
            $votations_rows[$key]['surname'] = $item->user->surname;
            $votations_rows[$key]['document'] = $item->user->document;

            unset($votations_rows[$key]['user']);
        }

        return $this->success($votations);
    }

    public function votationRanking(Request $request) {

        $ranking = CampaignSummoneds::search_votations_ranking_api($request);
        $ranking_rows = $ranking->items();

        foreach ($ranking_rows as $key => $item) {

            $ranking_rows[$key]['id'] = $item->id;
            $ranking_rows[$key]['user_id'] = $item->user->id;
            $ranking_rows[$key]['campaign_id'] = $item->campaign_id;

            $ranking_rows[$key]['name'] = $item->user->name;
            $ranking_rows[$key]['lastname'] = $item->user->lastname;
            $ranking_rows[$key]['surname'] = $item->user->surname;
            $ranking_rows[$key]['document'] = $item->user->document;
            $ranking_rows[$key]['votes'] = $item->votes;

            unset($ranking_rows[$key]['user']);
        }

        return $this->success($ranking);
    }

    public function votationUserSendVotes(Request $request) {

        $votations_data = [];

        foreach ($request->candidates_ids as $index => $candidateId) {

            $vote =  CampaignVotations::where('summoned_id', $candidateId)
                ->where('user_id', $request->user_id)
                ->first();

            if (!$vote) {
                $votations_data[$index]['summoned_id'] = $candidateId;
                $votations_data[$index]['user_id'] = $request->user_id;
            }
        }

        // info(['votations_data' => $votations_data]);
        CampaignVotations::insert($votations_data);

        return $this->success(true);
    }
    // ===  APIS VOTACION ===
}
