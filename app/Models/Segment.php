<?php

namespace App\Models;

class Segment extends BaseModel
{
    protected $fillable = [
        'name', 'description', 'criterion_value_count', 'active'
    ];

    // public function courses()
    // {
    //     return $this->belongsToMany(Course::class, 'segment_course');
    // }

    // public function requirements()
    // {
    //     return $this->hasMany(SegmentRequirement::class);
    // }

    // public function criterion_values()
    // {
    //     return $this->belongsToMany(CriterionValue::class);
    // }

    public function values()
    {
        return $this->hasMany(SegmentValue::class);
    }
}
