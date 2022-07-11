<?php

namespace App\Models;

class CriterionValue extends BaseModel
{
    protected $fillable = [
        'external_id', 'criterion_id', 'parent_id', 'exclusive_criterion_id',
        'value_text', 'value_date', 'value_datetime', 'value_boolean', 'value_decimal', 'value_integer',
        'position', 'active', 'description',
    ];

    public function criterion()
    {
        return $this->belongsTo(Criterion::class);
    }

    public function parents()
    {
        return $this->belongsToMany(CriterionValue::class, 'criterion_value_relationship', 'criterion_value_id', 'criterion_value_parent_id');
    }

    public function scopeParentsByCriterionID($q, $criterion_id)
    {
        return $q->where('criterion_id', $criterion_id);
    }
}
