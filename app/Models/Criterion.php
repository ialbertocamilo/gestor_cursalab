<?php

namespace App\Models;

class Criterion extends BaseModel
{
    protected $fillable = [
        'name', 'code', 'position',
        'parent_id', 'field_id', 'validation_id',
        'show_as_parent', 'show_in_reports', 'show_in_ranking', 'show_in_profile', 'show_in_segmentation',
        'show_in_form',
        'required', 'editable_configuration', 'editable_segmentation', 'multiple',
        'active', 'description',
    ];


    public function values()
    {
        return $this->hasMany(CriterionValue::class);
    }


}
