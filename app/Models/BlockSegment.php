<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockSegment extends Model
{
    protected $table = 'block_segment';

    protected $with = ['segment'];

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
