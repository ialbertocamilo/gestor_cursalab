<?php

namespace App\Models;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class CampaignRequirements extends Model
{
    use SoftDeletes;

    protected $fillable = ['id', 'campaign_id', 'criterio_id', 'type', 'condition', 'value'];

    protected function existRequirement($campaign_id, $type)
    {
        return self::where('campaign_id', $campaign_id)
                    ->where('type', $type)
                    ->whereNull('criterio_id')
                    ->first();
    }

    protected function existRequirementCriterio($campaign_id, $type)
    {
        return self::where('campaign_id', $campaign_id)
                    ->where('type', $type)
                    ->whereNotNull('criterio_id')
                    ->first();
    }

    protected function getReturnDataRequirements($config) {
        [ $condition, $months, $index, $section ] = $config;

        $currIndex = 0;
        if($index) {
            $currIndex = $index;
            settype($currIndex, "int");
        }

        settype($months, "int");
        settype($condition, "int");
        // set operator
        $arrayOperators = ['>=','<=','='];
        $operator = $arrayOperators[$condition] ?? $arrayOperators[0];

        $module = ($section === 'postulates'); //para postulantes o votantes 
        $data = ['operator' => $operator, 'months' => $months];
        
        return [ 'data' => $data, 'module' => $module, 'index' => $currIndex ];
    }

    protected function usersCheckCriterioDate_And_CriterioValue(
        $data, 
        $workspace_id, 
        $sub_workspaces = [], 
        $criterio_id_fecha_inicio_reconocimiento, 
        $criterion_value_id
    ) {
        
        $query_users = User::select('id')->where('users.active', 1)->whereRelation('subworkspace', 'parent_id', $workspace_id);

        if(!empty($sub_workspaces)) {
            $query_users->whereIn('users.subworkspace_id', $sub_workspaces);
        }

        $query_users->whereHas('criterion_values', function($q) use($criterio_id_fecha_inicio_reconocimiento, $data) {
            /* == usuarios segun workspace_id y criterio fecha ingreso ==*/
            $q->where('criterion_id', $criterio_id_fecha_inicio_reconocimiento);

            /* == usuarios filtrados segun su fecha de ingreso ==*/
            ['operator' => $operator, 'months' => $months] = $data;

            $isequal = ($operator === '=');
            $calcdate = date( $isequal ? 'Y-m' : 'Y-m-d', strtotime("-$months months"));

            if($isequal) {
                [$year, $month] = explode('-', $calcdate);
                $q->whereYear('value_date', $year)->whereMonth('value_date', $month);

            }else{ 
                $q->where('value_date', $operator, $calcdate);
            }
            /* == usuarios filtrados segun su fecha de ingreso ==*/
        });

        $query_users->whereHas('criterion_values', function($q) use($criterion_value_id) {
            $q->where('id', $criterion_value_id);
        });

        return $query_users;
    }

    protected function usersCheckCriterioDate($data, $sub_workspaces = []) {

        $workspace = get_current_workspace();
        $criterio_id = Workspace::select('criterio_id_fecha_inicio_reconocimiento')
                                 ->where('id', $workspace->id)->first();

        $query_users = User::where('users.active', 1)->whereRelation('subworkspace', 'parent_id', $workspace->id);
        if(!empty($sub_workspaces)) {
            $query_users->whereIn('users.subworkspace_id', $sub_workspaces);
        }

        $query_users->whereHas('criterion_values', function($q) use($criterio_id, $data) {
            /* == usuarios segun workspace_id y criterio fecha ingreso ==*/
            $q->where('criterion_id', $criterio_id->criterio_id_fecha_inicio_reconocimiento);

            /* == usuarios filtrados segun su fecha de ingreso ==*/
            ['operator' => $operator, 'months' => $months] = $data;

            $isequal = ($operator === '=');
            $calcdate = date( $isequal ? 'Y-m' : 'Y-m-d', strtotime("-$months months"));

            if($isequal) {
                [$year, $month] = explode('-', $calcdate);
                $q->whereYear('value_date', $year)->whereMonth('value_date', $month);

            }else{ 
                $q->where('value_date', $operator, $calcdate);
            }
            /* == usuarios filtrados segun su fecha de ingreso ==*/
        });


        // $query_users->join('criterion_value_user', 'criterion_value_user.user_id', '=', 'users.id')
        //             ->join('criterion_values', 'criterion_values.id', '=', 'criterion_value_user.criterion_value_id');

        // /* == usuarios y criterio fecha ingreso == */
        // $query_users->where('criterion_values.criterion_id', $criterio_id->criterio_id_fecha_inicio_reconocimiento);
        // /* == usuarios y criterio fecha ingreso == */

        // /* == usuarios filtrados segun fecha == */
        // ['operator' => $operator, 'months' => $months] = $data;

        // $isequal = ($operator === '=');
        // $calcdate = date( $isequal ? 'Y-m' : 'Y-m-d', strtotime("-$months months"));

        // if($isequal) {
        //     [ $year, $month ] = explode('-', $calcdate);
        //     $query_users->whereYear('criterion_values.value_date', $year)
        //                 ->whereMonth('criterion_values.value_date', $month);
        // }else{ 
        //     $query_users->where('criterion_values.value_date', $operator, $calcdate);
        // }
        // /* == usuarios filtrados segun fecha == */

        return $query_users; 
    }

    protected function usersCheckCriterioValues($query_users, $criterion_id, $criterion_values_ids = []) {

        $workspace = get_current_workspace();

        if(empty($criterion_values_ids)) {
            $criterion_values_ids = CriterionValue::getListForSelectValues($criterion_id, $workspace->id)->pluck('id')->toArray();
        }

        // $query_users->join('criterion_values', function($join) use ($criterion_values_ids) {
        //     $join->on('criterion_values.id', '=', 'criterion_value_user.criterion_value_id')
        //          ->whereIn('criterion_values.id', $criterion_values_ids);
        // });

        // $query_users->whereIn('criterion_value_user.criterion_value_id', $criterion_values_ids);

        $query_users->whereHas('criterion_values', function($q) use($criterion_values_ids) {
            $q->whereIn('id', $criterion_values_ids);
        });

        return $query_users;
    }
    
    protected function checkVerifyCount($requirements, $request)
    {
        [ 'data' => $data, // la condición y los meses
          'module' => $module, 
          'index' => $index ] = $requirements;

        $sub_workspaces_ids = explode(',', $request->modules); // 26 27 28
        
        $criterion_id = $request->requirement;
        $criterion_values_ids = ($request->requirement_values) ? 
                                explode(',', $request->requirement_values) : [];

        $query_users = self::usersCheckCriterioDate($data, $sub_workspaces_ids);
        $counter_users = 0;
        
        if($module) {
            // postulates
            $counter_users = $query_users->count(); 
        }else{
            // voters
            $counter_users = self::usersCheckCriterioValues($query_users, $criterion_id, $criterion_values_ids)->count();
        }

        return $counter_users;
    }

    protected function saveRequirement($type, $requirement, $campaign) {
        $condition = $requirement['range']['id'];
        $value = $requirement['months'];

        $requirement_data['type'] = $type;
        $requirement_data['condition'] = $condition;
        $requirement_data['value'] = $value;

        $requirement = self::existRequirement($campaign->id, $type);
        
        if($requirement){
            $requirement->update($requirement_data);
        } else {
            $requirement_data['campaign_id'] = $campaign->id;
            self::create($requirement_data);
        }
    }

    protected function saveRequirementCriterio($type, $requirement, $campaign) {
        $criterio_id = $requirement['requirement']['id'];

        $requirement_data['criterio_id'] = $criterio_id;
        $requirement_data['type'] = $type;

        $requirement_values = $requirement['requirement_values'] ?? NULL; // criterio valores
        if($requirement_values && is_array($requirement_values)) {
            $criterio_values = collect($requirement_values)->pluck(['id'])->implode(',');
            $requirement_data['value'] = $criterio_values;
        } else {
            $requirement_data['value'] = NULL;
        }

        $requirement = self::existRequirementCriterio($campaign->id, $type);

        if($requirement){
            $requirement->update($requirement_data);
        } else {
            $requirement_data['campaign_id'] = $campaign->id;
            self::create($requirement_data);
        }
    }
}
