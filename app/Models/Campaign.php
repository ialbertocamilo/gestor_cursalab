<?php

namespace App\Models;

use Illuminate\Http\Request;
use App\Models\CampaignPostulates;
use App\Models\CampaignRequirements;
use App\Models\CampaignSummoneds;
use App\Models\CampaignVotations;
use App\Models\{ CampaignBadges, CampaignModules, Media };
use Illuminate\Database\Eloquent\{ Model, SoftDeletes };
use Illuminate\Support\Arr;

class Campaign extends Model
{
    use SoftDeletes;

    // local atributes
    protected $fillable = [ 'id', 'workspace_id', 'title','description','file_image', 'start_date', 'end_date', 'subject', 'body', 'file_banner','color', 'question', 'porcent', 'stage_id', 'stage_content','stage_postulate','stage_votation', 'state_postulate_support','state'];

    protected $casts = [
        'start_date' => 'datetime:d/m/Y', //datetime:d/m/Y h:i:s
        'end_date' => 'datetime:d/m/Y',

        'stage_content' => 'boolean',
        'stage_postulate' => 'boolean',
        'stage_votation' => 'boolean',

        'state_postulate_support' => 'boolean',
        'state' => 'boolean',
    ];

    public static $index_campaign = NULL;

    public function modules() {
        return $this->hasMany(CampaignModules::class);
    }

    public function contents() {
        return $this->hasMany(CampaignContents::class);
    }

    public function badges() {
        return $this->hasMany(CampaignBadges::class);
    }

    public function summoneds() {
        return $this->hasMany(CampaignSummoneds::class);
    }

    public function requirements() {
        return $this->hasMany(CampaignRequirements::class);
    }

    public function requirement_null_criterio($type) {
        return $this->hasMany(CampaignRequirements::class)
                    ->where('type', $type)
                    ->whereNull('criterio_id');
    }

    public function requirement_not_null_criterio($type) {
        return $this->hasMany(CampaignRequirements::class)
                    ->where('type', $type)
                    ->whereNotNull('criterio_id');
    }

    public function requirement_not_null_condition_value($type) {
        return $this->hasMany(CampaignRequirements::class)
                    ->where('type', $type)
                    ->whereNotNull('condition')
                    ->whereNotNull('value');
    }


    public function requirement_postulates() {
        return $this->hasMany(CampaignRequirements::class)->where('type', 'POSTULATES');
    }

    public function requirement_voters() {
        return $this->hasMany(CampaignRequirements::class)->where('type', 'VOTERS');
    }

    public function getCampaingInicioFinFecha() {
        if($this->start_date) return $this->start_date->format('d/m/Y').' - '.$this->end_date->format('d/m/Y');
        return 'Sin fecha';
    }

    public function campaignGetStages() {
        $arrayModes = [
                        [0, 1, 2],
                        [0, 1],
                        [1, 2],
                        [1]
                    ];

        return $arrayModes[$this->stage_id];
    }

    public function processNullCriterio($requirement) {
        if(is_null($requirement->condition)) {
            $arrayRequirement['range'] = $requirement->condition;
        }else {
            $condition = $requirement->condition;
            settype($condition, "int");

            $arrayRequirement['range'] = ['id' => $condition];
        }

        $arrayRequirement['months'] = $requirement->value;
        return $arrayRequirement;
    }

    public function processBadgesCampaigId($badge_keys, $CAMPAIGN_DATA) {

        foreach ($badge_keys as $key => $value) {
            $current_badge = $this->badges[$key] ?? NULL;
            $current_file = NULL;

            if($current_badge) $current_file = $current_badge->file_badge;
            $CAMPAIGN_DATA['file_badge_'.$value] = $current_file;
        }

        return $CAMPAIGN_DATA;
    }

    public function processModulesCampaigId($CAMPAIGN_DATA) {

        $CAMPAIGN_DATA['modules'] = $this->modules->map(function ($module) {
            return ['id' => $module->id ];
        });

        return $CAMPAIGN_DATA;
    }

    public function getCurrContents() {
        $campaing_contents = $this->contents()->select('title', 'description', 'file_media', 'linked', 'state')->get();

        $content_data['contents'] = $campaing_contents;
        $content_data['question'] = $this->question; // from general

        return $content_data;
    }

    public function getCurrPostulates() {
        $requirement_postulate = $this->requirement_null_criterio('POSTULATES')->first();

        $postulate_data = $this->processNullCriterio($requirement_postulate);
        $postulate_data['state'] = (bool) $this->state_postulate_support;
        $postulate_data['matchs'] = 0;

        return $postulate_data;
    }

    public function getCurrVoters() {
        $requirement_voters = $this->requirement_null_criterio('VOTERS')->first();
        $requirement_voters_criterio = $this->requirement_not_null_criterio('VOTERS')->first();

        $voter_data = $this->processNullCriterio($requirement_voters);

        if(is_null($requirement_voters_criterio)) {
            $voter_data['requirement'] = NULL;
            $voter_data['requirement_values'] = [];
        } else {
            $requirement_values_exploded = explode(',', $requirement_voters_criterio->value);

            $voter_data['requirement'] = ['id' => (int) $requirement_voters_criterio->criterio_id ];
            $voter_data['requirement_values'] = Arr::map($requirement_values_exploded, function ($value_id) {
                return ['id' => (int) $value_id ];
            });
        }

        $voter_data['matchs'] = 0;
        $voter_data['porcent'] = $this->porcent;

        return $voter_data;
    }

    public function getCampaingStage() {
        $actual_stage = 'Sin etapa';

        $description_stages = [
            ['text' => 'Contenido', 'state' => $this->stage_content ],
            ['text' => 'Postulación', 'state' => $this->stage_postulate ],
            ['text' => 'Votación', 'state' => $this->stage_votation ],
        ];

        foreach ($description_stages as $key => [ 'state' => $state,
                                                  'text' => $text ]) {
            if($state) $actual_stage = $text;
        }
        return $actual_stage;
    }

    public function processUserBadgeStage() {
        $badges = $this->badges()->select('id', 'campaign_id', 'file_badge', 'position', 'state')->get();

        $badges_data = $badges->map(function ($badge) {
            $badge->file_badge = $badge->file_badge ? get_media_url($badge->file_badge) : $badge->file_badge;
            return $badge;
        });

        return $badges_data;
    }

    protected function checkAnswer($request)
    {
        return CampaignSummoneds::where('campaign_id', $request->campaign_id)
                                ->where('user_id', $request->user_id)->count();
    }

    protected function checkPostulates($request)
    {
        return CampaignPostulates::where('user_id', $request->user_id)
                                 ->whereRelation('summoned', 'campaign_id', $request->campaign_id)
                                 ->count();
    }

    protected function checkVotations($request)
    {
        return CampaignVotations::where('user_id', $request->user_id)
                                 ->whereRelation('summoned', 'campaign_id', $request->campaign_id)
                                 ->count();
    }

    public function processUserStateStage($user_id) {

        $stage_content = $this->stage_content;
        $stage_postulate = $this->stage_postulate;
        $stage_votation = $this->stage_votation;

        // request local
        $request = new Request();
        $request->replace(['campaign_id' => $this->id, 'user_id' => $user_id]);

        $checkAnswer =  $this->checkAnswer($request);
        $checkPostulate = $this->checkPostulates($request);
        $checkVoter = $this->checkVotations($request);

        // for question
        if($stage_content === NULL) $RespStateContent = $checkAnswer;
        else if($stage_content && !$checkAnswer) $RespStateContent = false;
        else if($stage_content && $checkAnswer) $RespStateContent = true;
        else $RespStateContent = $checkAnswer;
        // else $RespStateContent = false;

        // for postulates
        if($stage_postulate === NULL) $RespStatePostulate = false;
        else if($stage_postulate && !$checkPostulate) $RespStatePostulate = false;
        else if($stage_postulate && $checkPostulate) $RespStatePostulate = true;
        else $RespStatePostulate = $checkPostulate;
        // else $RespStatePostulate = false;

        // for votations
        if($stage_votation === NULL) $RespStateVotation = $checkVoter;
        else if($stage_votation && !$checkVoter) $RespStateVotation = false;
        else if($stage_votation && $checkVoter) $RespStateVotation = true;
        else $RespStateVotation = $checkVoter;
        // else $RespStateVotation = false;

        return [ 'stage_content' => (bool) $RespStateContent,
                 'stage_postulate' => (bool) $RespStatePostulate,
                 'stage_votation' => (bool) $RespStateVotation ];
    }

    public function processUserCommingSoon() {
        $stateAnnounDate = ($this->start_date && $this->end_date);
        $stateResponse = false;

        if($stateAnnounDate) {
            $current_date = date('Y-m-d');
            $start_date = date('Y-m-d', strtotime($this->start_date));
            $end_date = date('Y-m-d', strtotime($this->end_date));

            $stateResponse = ($start_date > $current_date && $current_date < $end_date);
        }
        return $stateResponse;
    }

    protected function search($request, $paginate = 10) {
        $workspace = get_current_workspace();

        $q = self::query();

        if($workspace)
            $q->where('workspace_id', $workspace->id);

        if ($request->q)
            $q->where('title', 'like', "%$request->q%");

        if (is_string($request->active))
            $q->where('state', $request->active);

        $sort = ($request->sortDesc == 'true') ? 'DESC' : 'ASC';

        if($request->sortBy)
            $q->orderBy($request->sortBy, $sort);
        else
            $q->orderBy('created_at', 'DESC');

        return $q->paginate($request->paginate);
    }


    protected function saveConfigGeneral($data, $currGeneralConfig, $campaign_id = NULL) {
        ['value' => $stage_value] = $data;

        $arrayData = ['stage_content', 'stage_postulate', 'stage_votation'];
        $arrayStages = [];
        $initFlag = false;

//        foreach($stage_value as $value) {
//            $currKey = $arrayData[$value];
//
//            if(!$initFlag) {
//                $initFlag = true;
//                $arrayStages[$currKey] = true;
//            } else $arrayStages[$currKey] = false;
//        }

        $stagesAttr = array_merge(['stage_id' => $data['id']], $arrayStages);
        $data_config = array_merge($currGeneralConfig, $stagesAttr);

        $data_config = Media::requestUploadFileOnly($data_config, 'image');
        $data_config = Media::requestUploadFileOnly($data_config, 'banner');

        // obviar las siguientes columnas
        $data_config = Arr::except($data_config, ['modules','file_badge_one', 'file_badge_two']);

        $workspace = get_current_workspace();
        $data_config['workspace_id'] = $workspace->id;

        if($campaign_id) {

            $data_config['description'] = $data_config['description'] ?? NULL;
            $data_config['start_date'] = $data_config['start_date'] ?? NULL;
            $data_config['end_date'] = $data_config['end_date'] ?? NULL;
            $data_config['subject'] = $data_config['subject'] ?? NULL;
            $data_config['body'] = $data_config['body'] ?? NULL;
            $data_config['color'] = $data_config['color'] ?? NULL;

            $data_config = Arr::except($data_config, ['id']);

            // info(['$data_config' => $data_config]);
            $campaign = self::find($campaign_id);
            $campaign->update($data_config);

            CampaignModules::saveModules($campaign, $currGeneralConfig['modules']);

            return $campaign;

        }else {
            $campaign = self::create($data_config);
            CampaignModules::saveModules($campaign, $currGeneralConfig['modules']);

            return $campaign;
        }
    }

    protected function saveQuestion($question, $campaign) {
        $campaign = self::find($campaign->id);

        $campaign->question = ($question === 'null') ? NULL : $question;
        $campaign->save();
    }

    protected function saveSupportSustent($state, $campaign) {
        $campaign = self::find($campaign->id);

        $campaign->state_postulate_support = (bool) $state;
        $campaign->save();
    }

    protected function savePorcent($porcent, $campaign) {
        $campaign = self::find($campaign->id);

        $campaign->porcent = $porcent;
        $campaign->save();
    }

    protected function in_array_values($campaign_criterio_values, $user_criterios_values) {
        $stateArray = false;
        $stackValues = $campaign_criterio_values;

        foreach ($stackValues as $value) {
            if (in_array($value, $user_criterios_values)) {
                $stateArray = true;
                break;
            }
        }
        return $stateArray;
    }

    protected function calc_date($condition, $value) {

        $arrayOperators = ['>=','<=','='];
        $operator = $arrayOperators[$condition];

        $isequal = ($operator === '=');
        $calcdate = date( $isequal ? 'Y-m' : 'Y-m-d', strtotime("-$value months"));

        return $calcdate;
    }

    protected function check_date($condition, $calcdate, $in_user_date) {
        $conditionState = 0;
        // info(['calcdate'=> $calcdate, 'in_user_date' => $in_user_date]);

        switch($condition) {
            case 0: $conditionState = ($in_user_date >= $calcdate); break;
            case 1: $conditionState = ($in_user_date <= $calcdate); break;
            default:
                $in_user_date = date('Y-m', strtotime($in_user_date));
                $conditionState = ($in_user_date == $calcdate);
            break;
        }

        return $conditionState;
    }

    protected function checkRequirementCampaign($requirement, $user_fecha, $user_criterios = [], $user_criterios_values = []) {
        $requirement_state = false;

        foreach($requirement as $key => [
                                            'criterio_id' => $criterio_id,
                                            'condition' => $condition,
                                            'value'  => $value
                                         ]) {

            // si la condicion es NULL y el value es NULL : viene con 'criterio_id'
            if (is_null($condition) && is_null($value)) {
                $requirement_state = in_array($criterio_id, $user_criterios);

            // si la condicion es NULL y el value es texto : viene con 'criterio_values'
            }else if(is_null($condition) && !is_null($value)) {
                $requirement_state = self::in_array_values( explode(',', $value), $user_criterios_values);

            // en todo caso viene la condicion y el value : referenciando fecha
            }else{
                $calcdate = self::calc_date($condition, $value);
                $requirement_state = self::check_date($condition, $calcdate, $user_fecha->value_date);
            }
            // Si al menos 1 requisito no se cumple no debería ver la camapaña
            if(!$requirement_state){
                return $requirement_state;
            }
        }

        return $requirement_state;
    }

    protected function getCampaignsByUser($user, $criterio){

        $user_fecha = $user->criterion_values()
                           ->where('criterion_id', $criterio->criterio_id_fecha_inicio_reconocimiento)
                           ->first();

        if(!$user_fecha) return [];

        $user_criterios_data = $user->criterion_values()->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'criterion_id' => $item->criterion_id,
            ];
        });

        $user_criterios = $user_criterios_data->pluck('criterion_id')->toArray();
        $user_criterios_values = $user_criterios_data->pluck('id')->toArray();

        // === campañas match con modulo ===
        $campaigns = self::whereHas('modules', function($query) use($user) {
            $query->where('module_id', $user->subworkspace_id);
        })->get();
        // === campañas match con modulo ===

        // === campañas match con requerimiento ===
        $process_campaigns = [];
        foreach ($campaigns as $campaign) {

            // === MODALIDAD => 1 Y 3 ===
            if($campaign->requirement_postulates && $campaign->requirement_voters) {

                // cumple con la fecha de ingreso
                $state_postulates = self::checkRequirementCampaign($campaign->requirement_postulates, $user_fecha);
                if($state_postulates) {

                    // copiamos y verificamos sus criterios con 'requirement_voters'
                    $temp_requirement_postulates = $campaign->requirement_voters;
                    $temp_requirement_postulates[0] = $campaign->requirement_postulates[0];

                    $state_postulates = self::checkRequirementCampaign($temp_requirement_postulates, $user_fecha, $user_criterios, $user_criterios_values);

                    // si cumple
                    if($state_postulates) {
                        $process_campaigns[] = $campaign->id;
                        continue;
                    }
                }

                // verificamos 'requirement_voters' originalmente
                $state_voters = self::checkRequirementCampaign($campaign->requirement_voters, $user_fecha, $user_criterios, $user_criterios_values);

                // si cumple
                if($state_voters) {
                    $process_campaigns[] = $campaign->id;
                }

            // MODALIDAD => 2 Y 4
            } else {

                // verificamos 'requirement_postulates' originalmente : MOdalidad => 2 y 4
                $requirement_postulates = self::checkRequirementCampaign($campaign->requirement_postulates, $user_fecha);

                if($requirement_postulates) {
                    $process_campaigns[] = $campaign->id;
                }
            }
        }
        // === campañas match con requerimiento ===

        return $process_campaigns;
    }

    public function checkFillPostulates() {
        $postulates_count = $this->requirement_not_null_condition_value('POSTULATES')->count();
        return ($postulates_count > 0);
    }

    public function checkFillVoters() {
        $voters_count = $this->requirement_not_null_condition_value('VOTERS')->count();
        $voters_count_criterio = $this->requirement_not_null_criterio('VOTERS')->count();

        return ($voters_count > 0 && $voters_count_criterio > 0);
    }

}
