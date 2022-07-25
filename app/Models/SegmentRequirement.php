<?php

namespace App\Models;

class SegmentRequirement extends BaseModel
{
    protected $fillable = [
        'name', 'description', 'criterion_value_count', 'active'
    ];

    public function segment()
    {
        return $this->belongsTo(Segment::class);
    }

}
