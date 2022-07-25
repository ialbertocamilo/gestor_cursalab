<?php

namespace App\Models;

class BlockSegment extends BaseModel
{
    public function block()
    {
        return $this->belongsTo(Block::class);
    }

    public function segment()
    {
        return $this->belongsTo(Segment::class);
    }

    public function criterion_values()
    {
        return $this->belongsToMany(CriterionValue::class);
    }
}
