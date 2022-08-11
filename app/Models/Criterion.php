<?php

namespace App\Models;

class Criterion extends BaseModel
{

    protected $table = 'criteria';

    protected $fillable = [
        'name', 'code', 'position',
        'parent_id', 'field_id', 'validation_id',
        'show_as_parent', 'show_in_reports', 'show_in_ranking',
        'show_in_profile', 'show_in_segmentation',
        'show_in_form', 'required', 'editable_configuration',
        'editable_segmentation', 'multiple', 'active', 'description',
    ];


    public function values()
    {
        return $this->hasMany(CriterionValue::class);
    }

    public function workspaces()
    {
        return $this->belongsToMany(Workspace::class);
    }

    protected function getValuesForSelect($criterion_code)
    {
        return CriterionValue::whereRelation('criterion', 'code', $criterion_code)
                        ->select('id', 'value_text as nombre')
                        // ->where('criterion_id', $criterion->id)
                        // ->when($config_id, function($q) use ($config_id){
                        //     $q->where('config_id', $config_id);
                        // })
                        ->get();
    }


}
