<?php

namespace App\Models;

class Segment extends BaseModel
{
    protected $fillable = [
        'name', 'description', 'mandatory', 'criterion_value_count',
        'user_id'
    ];


    public function criterion_values()
    {
//        return $this->belongsToMany(CriterionValue::class, );
    }
}
