<?php

namespace App\Models;

class SegmentValue extends BaseModel
{
    protected $table = 'segments_values';

    protected $fillable = [
        'name', 'criterion_id', 'criterion_value_id', 'type_id', 'starts_at', 'finishes_at'
    ];

    // public function courses()
    // {
    //     return $this->belongsToMany(Course::class, 'segment_course');
    // }

    // public function requirements()
    // {
    //     return $this->hasMany(SegmentRequirement::class);
    // }
    public function type()
    {
        return $this->belongsTo(Taxonomy::class, 'type_id');
    }

    public function criterion()
    {
        return $this->belongsTo(Criterion::class);
    }

    public function criterion_value()
    {
        return $this->belongsTo(CriterionValue::class);
    }

    // public function values()
    // {
    //     return $this->hasMany(SegmentValue::class);
    // }
}
